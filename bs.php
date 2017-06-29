<?php
include(__DIR__ . '/inc/default.inc.php');

use ttetris\DPRNG;
use ttetris\BrickSequenceGenerator;

$rng = new DPRNG((int) $_GET["salt"]);
$g = new BrickSequenceGenerator($rng);

echo "salt: " . $rng->getInitialSalt() . "<br>";

for ($i = 0;$i < 10;$i++)
{
    echo $g->nextType() . " ";
}

echo "<br>";

$rng = new DPRNG((int) $_GET["salt"]);
for ($i = 0;$i < 20;$i++)
{
    echo $rng->nextInt(0, 7) . "<br>";
}