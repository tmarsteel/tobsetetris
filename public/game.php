<?php
include(__DIR__ . '/../src/default.inc.php');

use ttetris\Game;
use ttetris\Brick;
use ttetris\DPRNG;
use ttetris\BrickSequenceGenerator;
use sql\Database;
use sql\Query;
use util\json\JSONArray;
use util\json\JSONObject;

session_start();

$mode = $_GET["mode"];

switch ($mode)
{
    case "start":
        $rng = new DPRNG();
        
        $gameData = array();
        $gameData["instance"] = new Game();
        $gameData["bsSalt"] = $rng->getInitialSalt();
        $gameData["bsg"] = new BrickSequenceGenerator($rng);
        $gameData["lastAction"] = millitime(); // is set to null in the "pause" block
        $gameData["playingSince"] = millitime(); // time since game start or since last resume; is null during pause
        $gameData["gameTimeAccumulator"] = 0; // counts the milliseconds that have been played, excluding seconds
                                              // is updated using playingSince every pause and with the final commit
        
        $_SESSION["games"][$gameData["instance"]->getID()] = serialize($gameData);
        
        header("Content-Type: application/json");
        echo json_encode(array(
            "id" => $gameData["instance"]->getID(),
            "brickSequenceGenerationSalt" => $rng->getInitialSalt()
        ));
        break;
    
    case "turn":
        $gameID = $_GET["gid"];
        
        if (!isset($_SESSION["games"][$gameID]))
        {
            header("HTTP/1.1 404 Not Found");
            header("Content-Type: application/json");
            echo json_encode(array(
                "error" => "Game not found."
            ));
            exit;
        }
        
        $gameData = unserialize($_SESSION["games"][$gameID]);

        $now = millitime();

        // pause => null => ignore
        if ($gameData["lastAction"] != null)
        {
            // the client sends the current turns at least every 10 seconds,
            // as long as there are turns to submit
            // the game field is 19 blocks high; each tick takes at most 750ms
            // therefore, it can take at most 14.25 seconds for a brick to drop
            // add to that the 10 second sync delay -> 24.5 seconds
            // we should also account for network delays of about 4 seconds
            // => 30 seconds between two submits are accepted

            if ($now - $gameData["lastAction"] > 30000)
            {
                header("HTTP/1.1 400 Bad Request");
                header("Content-Type: application/json");
                echo json_encode(array(
                    "error" => "Turn timeout of 16 seconds exceeded. Start a new game."
                ));
                exit;
            }
        }
        else {
            // resume was not called? assume this is the resume
            $gameData["lastAction"] = millitime();
        }
        
        /** @var Game $game */ 
        $game = $gameData["instance"];
        
        
        // apply up to 20 turns submitted by the client
        $nTurn = 0;
        
        try
        {
            for (;$nTurn < 20;$nTurn++)
            {
                if (!isset($_POST["brickType" . $nTurn]))
                {
                    break;
                }

                $expectedType = $gameData["bsg"]->nextType();
                $submittedType = $_POST["brickType" . $nTurn];

                if ($submittedType != $expectedType)
                {
                    // log the error
                    file_put_contents(
                        "desync.log",
                        "[" . date("Y-m-d H:i:s") . "] submitted brick #" . $nTurn .
                            "; submitted type = " . $submittedType . "; expected type = " . $expectedType .
                            "; salt = " . $gameData["bsSalt"] . "\n" .
                        FILE_APPEND
                    );

                    // cancel the turn
                    throw new \Exception("Brick sequence out of sync in turn " . 
                        $nTurn . ": client placed " . $_POST["brickType" . $nTurn] .
                        " but should have placed " . $expectedType);
                }
                
                $brick = Brick::getInstance($expectedType);

                $brick->setPositionX((int) $_POST["brickX" . $nTurn]);
                $brick->setPositionY((int) $_POST["brickY" . $nTurn]);
                $brick->setRotation((int) $_POST["brickRotation" . $nTurn]);   
                
                $game->onBrickPlaced($brick);
            }
        }
        catch (\ttetris\CollisionException $ex)
        {
            header("HTTP/1.1 400 Bad Request");
            header("Content-Type: application/json");
            echo json_encode(array(
                "error" => "Turn " . $nTurn . " is invalid: " . $ex->getMessage()                
            ));
            break;
        }
        catch (\Exception $ex)
        {
            header("HTTP/1.1 400 Bad Request");
            header("Content-Type: application/json");
            echo json_encode(array(
                "error" => $ex->getMessage()
            ));
            break;
        }
        
        $gameData["lastAction"] = $now;
            
        $_SESSION["games"][$gameID] = serialize($gameData);

        header("Content-Type: application/json");
        echo json_encode(array(
            "score" => $game->getCurrentScore()
        ));
        
        break;
        
    case "pause":
        $gameID = $_GET["gid"];
        if (!isset($_SESSION["games"][$gameID]))
        {
            header("HTTP/1.1 404 Not Found");
            header("Content-Type: application/json");
            echo json_encode(array(
                "error" => "Game not found."
            ));
            exit;
        }
        
        $gameData = unserialize($_SESSION["games"][$gameID]);

        $now = millitime();

        if ($gameData["lastAction"] == null)
        {
            $gameData["lastAction"] = $now;
            $gameData["playingSince"] = $now;
        }
        else
        {
            $gameData["lastAction"] = null;
            $gameData["gameTimeAccumulator"] += $now - $gameData["playingSince"];
            $gameData["playingSince"] = null;
        }
        
        header("Content-Type: application/json");
        echo json_encode(array(
            "state" => $gameData["lastAction"] == null? "paused" : "running"
        ));
        break;
        
    case "commit":
        $gameID = $_GET["gid"];
        if (!isset($_SESSION["games"][$gameID]))
        {
            header("HTTP/1.1 404 Not Found");
            header("Content-Type: application/json");
            echo json_encode(array(
                "error" => "Game not found."
            ));
            exit;
        }

        $now = millitime();

        $gameData = unserialize($_SESSION["games"][$gameID]);

        $gameData["gameTimeAccumulator"] += $now - $gameData["playingSince"];

        unset($_SESSION["games"][$gameID]);
        
        $points = $gameData["instance"]->getCurrentScore();
       
        try
        {
            Database::establishConnection();

            $name = trim(isset($_POST['name'])? $_POST['name'] : 'Unknown');
            $name = empty($name)? 'Unknown' : $name;
            
            // sort out the correct highscore page
            $perPage = 13;

            // this will later hold the highscore page to output
            $highscorePage = null;

            // this will later hold the number of that page
            $highscorePageNumber = -1;

            // hodls the indices clients are to highlight
            $highlighted = array();

            // to find the appropriate page on which the new entry is located,
            // the entire highscore list has to be queried
            $res = Query::get('selectAllHighscore')->execute();

            // holds the number of the current page
            $nPage = 1;
            // holds the current page
            $currentPage = array();
            // loop var: the index within $currentPage at which the entry was
            // added
            $myIndex = -1;

            while ($row = $res->asAssocArray())
            {
                if (count($currentPage) >= $perPage)
                {
                    if ($myIndex != -1)
                    {
                        // the score has been added and the current page is full
                        // done!
                        break;
                    }

                    $nPage++;
                    $currentPage = array();
                }

                if ($myIndex == -1 && $points > $row['score'])
                {
                    $myIndex = count($currentPage);
                    $currentPage []= array('name' => $name, 'score' => $points);
                }

                // this check is necessary. otherwise scoring extactly between
                // the last and pre-last entry on the list makes $currentPage
                // one entry too long.
                if (count($currentPage) < $perPage)
                {
                    $currentPage []= $row;
                }
            }

            if ($myIndex == -1)
            {
                // end of highscore insert
                if (count($currentPage) >= $perPage)
                {
                    $myIndex = 0;
                    $currentPage = array(array('name' => $name, 'score' => $points));
                    $nPage++;
                }
                else
                {
                    $myIndex = count($currentPage);
                    $currentPage []= array('name' => $name, 'score' => $points);
                }
            }
            
            $res->close();
            
            // make sure to insert score
            $query = Query::get('insertHighscore');
            $query->setParam(0, $name);
            $query->setParam(1, $points);
            $query->setParam(2, $gameData["bsSalt"]);
            $query->execute();

            // now, $currentPage holds the page this entry was entered to
            // and $nPage is the number of that page.
            $highscorePage = $currentPage;
            $highscorePageNumber = $nPage;
            $highlighted []= $myIndex;

            $scoreList = new JSONArray();

            $out = new JSONObject();
            $out->add('pageN', $highscorePageNumber);
            $out->add('scores', $scoreList);
            $out->add('highlight', $highlighted);

            foreach ($highscorePage as $entry)
            {
                $jEntry = new JSONObject();
                $jEntry->add('name', trim($entry['name']));
                $jEntry->add('score', (int) $entry['score']);
                $scoreList->add($jEntry);
            }

            header("Content-Type: application/json");
            echo $out->render();
        }
        catch (Exception $ex)
        {
            exit("db-error");
        }
        break;
}

function millitime()
{
    return round(microtime(true) / 1000);
}