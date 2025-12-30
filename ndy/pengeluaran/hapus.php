<?php
require_once __DIR__ . "/../sistem/koneksi.php";
$hub = open_connection();

$id = $_GET['id'];

mysqli_query($hub, "DELETE FROM pengeluaran WHERE id='$id'");

header("Location: png_1.php");
exit;
