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

if (isset($_SESSION["pesan"])){
    echo "<script>alert('".$_SESSION["pesan"]."');</script>";
    unset($_SESSION["pesan"]);
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            min-height: 100vh;
            background-color: #f4f4f4;
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
            margin-top: 20px;
        }
        .sidebar ul li {
            padding: 10px 5px;
        }
        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            display: block;
            transition: background-color 0.3s ease;
            border-radius: 4px;
        }
        .sidebar ul li a:hover {
            background-color: #a57700;
        }
        .content {
            flex-grow: 1;
            padding: 20px;
            background-color: #fff;
            box-shadow: inset 0 0 15px #ddd;
            border-radius: 8px;
            margin: 15px;
        }
        .top-bar {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .top-bar form {
            display: flex;
            width: 300px;
        }
        .top-bar input[type="text"] {
            flex-grow: 1;
            padding: 8px 12px;
            border: 1.5px solid #ccc;
            border-radius: 6px 0 0 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        .top-bar input[type="text"]:focus {
            outline: none;
            border-color: #b8860b;
            box-shadow: 0 0 6px #b8860b;
        }
        .top-bar button {
            background-color: #b8860b;
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 0 6px 6px 0;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            user-select: none;
        }
        .top-bar button:hover {
            background-color: #9a6f02;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .admin-header h3 {
            margin: 0;
            font-weight: 700;
            color: #444;
        }
        .admin-header button {
            background-color: #b8860b;
            border: none;
            padding: 8px 16px;
            font-weight: 700;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            user-select: none;
        }
        .admin-header button:hover {
            background-color: #9a6f02;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            box-shadow: 0 2px 10px rgb(0 0 0 / 0.05);
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px 15px;
            text-align: center;
            font-size: 14px;
        }
        th {
            background-color: #ffcc00;
            font-weight: 700;
            color: #333;
        }
        img {
            border-radius: 6px;
            object-fit: cover;
        }
        .action-icons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .icon {
            cursor: pointer;
            font-size: 18px;
            transition: color 0.3s ease;
            user-select: none;
        }
        .icon:hover {
            color: #b8860b;
        }
        .action-icons form button {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #c0392b;
            transition: color 0.3s ease;
            padding: 0;
            user-select: none;
        }
        .action-icons form button:hover {
            color: #e74c3c;
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
            <button type="submit" title="Search">🔍</button>
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
                <td><img src="../assets/<?= htmlspecialchars($row['gambar']) ?>" alt="Admin" width="50" height="50"></td>
                <td>
                    <?php if ($row['id'] == $_SESSION['admin_id']): ?>
                        <span class="status-aktif">aktif</span>
                    <?php else: ?>
                        <span class="status-nonaktif">nonaktif</span>
                    <?php endif; ?>
                </td>
                <td class="action-icons">
                    <span class="icon" title="Edit" onclick="window.location.href='../service/edit-acc.php?id=<?= $row['id'] ?>'">✏️</span>
                    
                    <form method="POST" action="../service/delete-acc.php" onsubmit="return confirm('Yakin ingin hapus admin ini?')">
                        <input type="hidden" name="id" value="<?= $row['id']; ?>">
                        <button type="submit" class="btn btn-danger">🗑️</button>
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
