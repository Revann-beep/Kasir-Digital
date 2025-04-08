<?php
session_start();

// Hapus semua data di keranjang
unset($_SESSION['keranjang']);

header("Location: ../user/keranjang.php");
exit;
