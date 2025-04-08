<?php
$server_name = 'localhost';
$username = 'root';
$password = '';
$database = "kasir";//sesuaikan nama database

$conn = mysqli_connect($server_name, $username, $password, $database);

if ($conn->connect_error) {
    die("gagal". $conn->connect_error);
}