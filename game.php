<?php
include(__DIR__ . '/inc/default.inc.php');

use ttetris\Game;
use ttetris\Brick;
use ttetris\DPRNG;
use ttetris\BrickSequenceGenerator;
use cryptmail\sql\Database;
use cryptmail\sql\Query;
use cryptmail\util\json\JSONArray;
use cryptmail\util\json\JSONObject;

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
        
        // pause => null => ignore
        if ($gameData["lastAction"] != null)
        {
            $now = millitime();

            if ($now - $gameData["lastAction"] > 16000)
            {
                // the game field is 19 blocks high; each tick takes at most 750ms
                // therefore, there are at most 14.25 seconds between to actions
                // 16 seconds are accepted due to network issues
                header("HTTP/1.1 400 Bad Request");
                header("Content-Type: application/json");
                echo json_encode(array(
                    "error" => "Turn timeout of 16 seconds exceeded. Start a new game."
                ));
                exit;
            }
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

                $type = $gameData["bsg"]->nextType();
                
                if ($type != $_POST["brickType" . $nTurn])
                {
                    // log the error
                    file_put_contents("desync.log", "Desync with salt " . $gameData["bsSalt"] . "\n", FILE_APPEND);
                    
                    // cancel the turn
                    throw new \Exception("Brick sequence out of sync in turn " . 
                        $nTurn . ": client placed " . $_POST["brickType" . $nTurn] .
                        " but should have placed " . $type);
                }
                
                $brick = Brick::getInstance($type);

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
        
        if ($gameData["lastAction"] == null)
        {
            $gameData["lastAction"] = millitime();
        }
        else
        {
            $gameData["lastAction"] = null;
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
        
        $gameData = unserialize($_SESSION["games"][$gameID]);
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