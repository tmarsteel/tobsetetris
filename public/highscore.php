<?php
include dirname(__FILE__) . '/../src/default.inc.php';

use sql\Database;
use sql\Query;
use util\json\JSONArray;

try
{
    Database::establishConnection();
    
    $perPage = 13;
    
    // this will later hold the highscore page to output
    $highscorePage = null;
    
    // this will later hold the number of that page
    $highscorePageNumber = -1;

    // simply query the highscores
    $requestedPage = isset($_GET['page'])? (int) $_GET['page'] : 1;
    if ($requestedPage == 0)
    {
        $requestedPage = 1;
    }

    $query = Query::get('selectHighscorePage');
    $query->setParam(0, ($requestedPage - 1) * $perPage);
    $query->setParam(1, $perPage);
    $res = $query->execute();

    $highscorePage = array();
    $highscorePageNumber = $requestedPage;

    while ($row = $res->asAssocArray())
    {
        $highscorePage []= $row;
    }

    $scoreList = new JSONArray();
    
    $out = array(
        'pageN' => $highscorePageNumber,
        'highlight' => array(),
        'scores' => array()
    );
    
    foreach ($highscorePage as $entry)
    {
        $out["scores"] []= array(
            'name' => trim($entry['name']),
            'score' => (int) $entry['score']
        );
    }

    header("Content-Type: application/json");
    
    echo json_encode($out);
}
catch (Exception $ex)
{
    exit("db-error");
}
?>