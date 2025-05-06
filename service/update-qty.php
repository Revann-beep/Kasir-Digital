<?php
session_start();

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];

    if (isset($_SESSION['keranjang'][$id])) {
        if ($action === 'tambah') {
            if ($_SESSION['keranjang'][$id]['qty'] < $_SESSION['keranjang'][$id]['stok']) {
                $_SESSION['keranjang'][$id]['qty'] += 1;
            } else {
                $_SESSION['error'] = 'Jumlah melebihi stok!';
            }
        } elseif ($action === 'kurang') {
            $_SESSION['keranjang'][$id]['qty'] -= 1;
            if ($_SESSION['keranjang'][$id]['qty'] <= 0) {
                unset($_SESSION['keranjang'][$id]);
            }
        }
    }
}

header('Location: ../user/keranjang.php');
exit;
