<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kasir";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Tangani pencarian
$searchQuery = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $searchQuery = $_POST['search'];
}

$sql = "SELECT * FROM admin WHERE email LIKE '%$searchQuery%' OR username LIKE '%$searchQuery%'";
$admins = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 200px;
            background: #b8860b;
            padding: 20px;
            color: white;
            display: flex;
            flex-direction: column;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            padding: 10px;
        }
        .sidebar ul li a {
            color: white;
            text-decoration: none;
        }
        .content {
            flex-grow: 1;
            padding: 20px;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
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
        .status-aktif {
            color: green;
            font-weight: bold;
        }
        .status-nonaktif {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>TimelessWatch.co</h2>
    <ul>
        <li><a href="dashboard.php">⚙️ Dashboard</a></li>
        <li><a href="kategori.php">📂 Kategori</a></li>
        <li><a href="produk.php">📦 Produk</a></li>
        <li><a href="../service/logout.php">↩️ Log out</a></li>
    </ul>
</div>

<main class="content">
    <header class="top-bar">
        <form method="POST" action="">
            <input type="text" name="search" placeholder="Search by email or username" value="<?= htmlspecialchars($searchQuery) ?>">
            <button type="submit">🔍</button>
        </form>
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
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $admins->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['email'] ?></td>
                <td><?= $row['username'] ?></td>
                <td><img src="../assets/<?= htmlspecialchars($row['gambar']) ?>" alt="Admin" width="50"></td>
                <td>
                    <span class="<?= $row['status'] === 'Aktif' ? 'status-aktif' : 'status-nonaktif' ?>">
                        <?= $row['status'] ?>
                    </span>
                </td>
                <td class="action-icons">
                    <span class="icon" onclick="window.location.href='../service/edit-acc.php?id=<?= $row['id'] ?>'">✏️</span>
                    <form action="../service/delete-acc.php" method="POST" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
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
<?php $conn->close(); ?>
