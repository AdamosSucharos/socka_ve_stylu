<?php

$host = "localhost";
$username = "root";
$password = '';
$database = "kubica_soc";

$mysqli = mysqli_connect($host, $username, $password, $database);
                     
if ($mysqli->connect_errno) {
    die("Connection error: " . $mysqli->connect_error);
}

return $mysqli;
?>
