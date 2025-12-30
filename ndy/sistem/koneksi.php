<?php
function open_connection() {

    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "pengluaran"; // SESUAI DATABASE KAMU

    $conn = mysqli_connect($host, $user, $pass, $db);

    if (!$conn) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    }

    return $conn;
}
