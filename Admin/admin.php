<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kasir";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        $email = $_POST['email'];
        $username = $_POST['username'];
        $gambar = "user-icon.png";
        
        $sql = "INSERT INTO admin (email, username, gambar) VALUES ('$email', '$username', '$gambar')";
        $conn->query($sql);
        header("Location: add-acc.php");
        exit();
    }
    
    if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        
        $sql = "UPDATE admin SET email='$email', username='$username' WHERE id=$id";
        $conn->query($sql);
        header("Location: edit-acc.php");
        exit();
    }
    
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        
        $sql = "DELETE FROM admin WHERE id=$id";
        $conn->query($sql);
        header("Location: admin.php");
        exit();
    }
}

$admins = $conn->query("SELECT * FROM admin");
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            min-height: 100vh;
        }
        .container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }
        .sidebar {
            width: 200px;
            background: #b8860b;
            padding: 20px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            flex-grow: 1;
        }
        .sidebar ul li {
            padding: 10px;
            cursor: pointer;
        }
        .sidebar ul .active {
            color: #c74e1b;
        }
        .content {
            flex-grow: 1;
            padding: 20px;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            padding: 10px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-header button {
            background: #ccc;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid black;
            padding: 10px;
            text-align: center;
        }
        th {
            background: yellow;
        }
        .action-icons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .icon {
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>TimelessWatch.co

</h2>
    <ul>
        <li><a href="dashboard.php">⚙️ Dashboard</a></li>
        <li><a href="kategori.php">📂 Kategori</a></li>
        <li><a href="produk.php">📦 Produk</a></li>
        <li><a href="../service/index.php">↩️ Log out</a></li>
    </ul>
</div>
<main class="content">
    <header class="top-bar">
        <input type="text" placeholder="Search">
    </header>
    <section class="admin-dashboard">
        <div class="admin-header">
            <h3>Halaman Admin</h3>
            <button onclick="window.location.href='../service/add-acc.php'">+ Add Admin</button>
        </div>
        <table>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Username</th>
                <th>Gambar</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $admins->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['email'] ?></td>
                <td><?= $row['username'] ?></td>
                <td><img src="<?= $row['gambar'] ?>" alt="User" width="50"></td>
                <td class="action-icons">
                    <span class="icon" onclick="window.location.href='../service/edit-acc.php?id=<?= $row['id'] ?>'">✏️</span>
                    <form action="../service/delete-acc.php" method="POST" style="display:inline;">
    <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
    <button type="submit" name="delete" onclick="return confirm('Hapus admin ini?')">🗑️</button>
</form>

                </td>
            </tr>
            <?php } ?>
        </table>
    </section>
</main>
</body>
</html> 