<?php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "perpustakaan_db_audrise";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die(json_encode(array(
        "status" => "error",
        "message" => "Koneksi database gagal"
    )));
}
