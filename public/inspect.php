<?php
session_start();
include dirname(__FILE__) . '/../src/default.inc.php';

use ttetris\DPRNG;
use ttetris\BrickSequenceGenerator;

try
{
    if ($_GET["mode"] == "init")
    {
        $salt = (int) $_GET["salt"];
        $rng = new DPRNG($salt);
        $bsg = new BrickSequenceGenerator(new DPRNG($salt));
        $_SESSION["inspect"] = serialize(array(
            "rng" => $rng,
            "bsg" => $bsg
        ));
        
        exit("ok");
    }
    else if ($_GET["mode"] == "step")
    {
        if (!isset($_SESSION["inspect"]))
        {
            header("HTTP/1.1 400 Bad Request");
            exit("not initialized");
        }
        
        $data = unserialize($_SESSION["inspect"]);
        if ($data == FALSE)
        {
            header("HTTP/1.1 500 Internal Server Error");
            exit("internal error");
        }
        $rng = $data["rng"];
        $bsg = $data["bsg"];
        
        // calculate the next 100 brick types and the next 1000 random ints [0,6]
        $ints = array();
        $brickTypes = array();
        
        for ($i = 0;$i < 1000;$i++)
        {
            if ($i < 100)
            {
                $brickTypes []= $bsg->nextType();
            }
            
            $ints []= $rng->nextInt(0, 6);
        }
        
        // write the states back to the session
        $_SESSION["inspect"] = serialize(array(
            "rng" => $rng,
            "bsg" => $bsg
        ));
        
        header("Content-Type: application/json");
        echo json_encode(array(
            "rng" => $ints,
            "bsg" => $brickTypes
        ));
        exit;
    }
}
catch (Exception $ex)
{
    header("HTTP/1.1 500 Internal Server Error");
    exit($ex->getMessage());
}
?>
<html>
    <head>
        <title>TETIRS - Test DPRNG Issues</title>
        <script type="text/javascript" src="js/libs/jquery.js"></script>
        <script type="text/javascript" src="js/utils.js?1498909317 "></script>
        <script type="text/javascript" src="js/bricks.js?1498909317 "></script>
        <script type="text/javascript" src="js/verify.js?1498909317 "></script>
        <link href="css/verify.css" rel="stylesheet" />
    </head>
    <body>
        <div id="initScreen" class="container">
            <span>Enter salt to verify: </span>
            <input id="saltInput" type="number">
            <button id="verifyBT" onclick="verify(parseInt($('#saltInput').val()))">verify</button>
            
            <br><br>
            <span>desync.log</span>
            <pre><?php echo file_get_contents("desync.log"); ?></pre>
        </div>
        <div id="verifyScreen" class="container">
            <table id="verifyTable">
                <thead>
                    <tr>
                        <td colspan="2">Client</td>
                        <td colspan="2">Server</td>
                    </tr>
                    <tr>
                        <td>DPRNG</td>
                        <td>Birck Sequence</td>
                        <td>DPRNG</td>
                        <td>Brick Sequence</td>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <div id="result"></div>
        </div>
    </body>
</html>