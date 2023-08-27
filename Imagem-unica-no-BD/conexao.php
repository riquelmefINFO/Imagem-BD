<?php

$host = "localhost";
$user = "root";
$pass = "";
$bd = "upload2";

$mysqli = new mysqli($host, $user, $pass, $bd);

/* check connectin */
if ($mysqli->connect_errno) {
    echo "Connect Failed: " . $mysqli->connect_errno;
    exit();
}

?>