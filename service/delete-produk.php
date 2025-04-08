<?php
include '../service/conection.php';

$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM produk WHERE id=$id");

header("Location: produk.php");
?>
