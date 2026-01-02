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

$sql = "SELECT * FROM admin WHERE email LIKE '%$searchQuery%' OR username LIKE '%$searchQuery%' ORDER BY id DESC";
$admins = $conn->query($sql);

// Hitung total admin
$countQuery = $conn->query("SELECT COUNT(*) as total FROM admin");
$totalAdmin = $countQuery->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Management - TimelessWatch.co</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a237e;
            --primary-light: #534bae;
            --primary-dark: #000051;
            --secondary: #ff9800;
            --accent: #00bcd4;
            --success: #4caf50;
            --warning: #ff9800;
            --danger: #f44336;
            --light: #f5f5f5;
            --dark: #212121;
            --gray: #757575;
            --gray-light: #e0e0e0;
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', 'Roboto', sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            color: var(--dark);
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 30px 20px;
            color: white;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100vh;
            z-index: 100;
        }

        .logo {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }

        .logo h2 {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 1.5px;
            color: white;
            margin-bottom: 5px;
        }

        .logo p {
            font-size: 12px;
            opacity: 0.8;
        }

        .sidebar-nav ul {
            list-style: none;
            flex-grow: 1;
        }

        .sidebar-nav ul li {
            margin-bottom: 10px;
        }

        .sidebar-nav ul li a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .sidebar-nav ul li a:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(5px);
        }

        .sidebar-nav ul li a.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .sidebar-nav ul li a i {
            width: 24px;
            text-align: center;
            font-size: 18px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 25px 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }

        .page-title h1 {
            font-size: 28px;
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 8px;
        }

        .page-title p {
            color: var(--gray);
            font-size: 14px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .search-box {
            position: relative;
            width: 300px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border: 2px solid var(--gray-light);
            border-radius: var(--border-radius);
            font-size: 14px;
            transition: var(--transition);
            background: #f9f9f9;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 16px;
        }

        /* Add Admin Button */
        .btn-add {
            background: linear-gradient(135deg, var(--success) 0%, #2e7d32 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }

        .btn-add:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
            background: linear-gradient(135deg, #43a047 0%, #1b5e20 100%);
        }

        /* Stats Card */
        .stats-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stats-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
        }

        .stats-info h3 {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .stats-info p {
            color: var(--gray);
            font-size: 14px;
            font-weight: 500;
        }

        /* Admin Table */
        .table-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .table-header {
            padding: 25px 30px;
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h2 {
            font-size: 20px;
            color: var(--primary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
        }

        th {
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }

        th:first-child {
            padding-left: 30px;
        }

        th:last-child {
            padding-right: 30px;
            text-align: center;
        }

        tbody tr {
            border-bottom: 1px solid var(--gray-light);
            transition: var(--transition);
        }

        tbody tr:hover {
            background-color: rgba(26, 35, 126, 0.05);
        }

        td {
            padding: 20px 15px;
            font-size: 14px;
            color: var(--dark);
        }

        td:first-child {
            padding-left: 30px;
            font-weight: 600;
            color: var(--primary);
        }

        td:last-child {
            padding-right: 30px;
            text-align: center;
        }

        /* Admin Avatar */
        .admin-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--gray-light);
            transition: var(--transition);
        }

        .admin-avatar:hover {
            transform: scale(1.1);
            border-color: var(--primary);
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-active {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .status-inactive {
            background: rgba(244, 67, 54, 0.1);
            color: var(--danger);
            border: 1px solid rgba(244, 67, 54, 0.3);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .btn-action {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            color: white;
            font-size: 16px;
        }

        .btn-edit {
            background: linear-gradient(135deg, var(--accent) 0%, #00838f 100%);
        }

        .btn-edit:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 188, 212, 0.3);
        }

        .btn-delete {
            background: linear-gradient(135deg, var(--danger) 0%, #c62828 100%);
        }

        .btn-delete:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
        }

        /* Footer */
        .footer {
            margin-top: auto;
            text-align: center;
            padding: 20px;
            color: var(--gray);
            font-size: 14px;
            border-top: 1px solid var(--gray-light);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: var(--gray-light);
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 20px;
            color: var(--gray);
            margin-bottom: 10px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 250px;
            }
            .main-content {
                margin-left: 250px;
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 20px;
            }
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            .header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            .search-box {
                width: 100%;
            }
            .table-container {
                overflow-x: auto;
            }
            table {
                min-width: 800px;
            }
        }

        @media (max-width: 480px) {
            .stats-card {
                flex-direction: column;
                text-align: center;
            }
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h2>TimelessWatch.co</h2>
            <p>Admin Management</p>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="kategori.php"><i class="fas fa-tags"></i> Kategori</a></li>
                <li><a href="produk.php"><i class="fas fa-box-open"></i> Produk</a></li>
                <li><a href="member.php"><i class="fas fa-users"></i> Member</a></li>
                <li><a href="admin.php" class="active"><i class="fas fa-user-shield"></i> Admin</a></li>
                <li><a href="laporan.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
                <li><a href="../service/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="page-title">
                <h1><i class="fas fa-user-shield"></i> Admin Management</h1>
                <p>Kelola akun administrator sistem</p>
            </div>
            
            <div class="header-actions">
                <form method="POST" action="" class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Cari admin berdasarkan email atau username..." 
                           value="<?= htmlspecialchars($searchQuery) ?>">
                </form>
                
                <button class="btn-add" onclick="window.location.href='../service/add-acc.php'">
                    <i class="fas fa-user-plus"></i> Tambah Admin
                </button>
            </div>
        </div>

        <!-- Stats Card -->
        <div class="stats-card">
            <div class="stats-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="stats-info">
                <h3><?= $totalAdmin ?></h3>
                <p>Total Administrator Terdaftar</p>
            </div>
        </div>

        <!-- Admin Table -->
        <div class="table-container">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Daftar Admin</h2>
                <span style="color: var(--gray); font-size: 14px;">
                    <?= $admins->num_rows ?> admin ditemukan
                </span>
            </div>

            <?php if ($admins->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Avatar</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $admins->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($row['username']) ?></strong>
                            <?php if ($row['id'] == $_SESSION['admin_id']): ?>
                                <span style="color: var(--primary); font-size: 12px; margin-left: 5px;">
                                    <i class="fas fa-star"></i> Anda
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <img src="../assets/<?= htmlspecialchars($row['gambar']) ?>" 
                                 alt="<?= htmlspecialchars($row['username']) ?>" 
                                 class="admin-avatar"
                                 onerror="this.src='../assets/default-avatar.png'">
                        </td>
                        <td>
                            <?php if ($row['id'] == $_SESSION['admin_id']): ?>
                                <span class="status-badge status-active">
                                    <i class="fas fa-circle"></i> Aktif
                                </span>
                            <?php else: ?>
                                <span class="status-badge status-inactive">
                                    <i class="fas fa-circle"></i> Nonaktif
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-edit" 
                                        onclick="window.location.href='../service/edit-acc.php?id=<?= $row['id'] ?>'"
                                        title="Edit Admin">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <?php if ($row['id'] != $_SESSION['admin_id']): ?>
                                <form method="POST" action="../service/delete-acc.php" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus admin <?= htmlspecialchars($row['username']) ?>?')"
                                      style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                    <button type="submit" class="btn-action btn-delete" title="Hapus Admin">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php else: ?>
                                <button class="btn-action" style="background: var(--gray); cursor: not-allowed;" 
                                        title="Tidak dapat menghapus akun sendiri">
                                    <i class="fas fa-lock"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-user-slash"></i>
                <h3>Tidak ada admin ditemukan</h3>
                <p><?= $searchQuery ? "Tidak ada hasil untuk '" . htmlspecialchars($searchQuery) . "'" : "Belum ada admin terdaftar" ?></p>
                <button class="btn-add" style="margin-top: 20px;" onclick="window.location.href='../service/add-acc.php'">
                    <i class="fas fa-user-plus"></i> Tambah Admin Pertama
                </button>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; <?= date('Y') ?> TimelessWatch.co - Admin Management System</p>
            <p style="font-size: 12px; margin-top: 5px;">
                <i class="fas fa-shield-alt"></i> Hak akses terbatas untuk administrator saja
            </p>
        </div>
    </div>

    <script>
        // Auto submit search on input
        document.querySelector('.search-box input').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });

        // Add loading animation for buttons
        document.querySelectorAll('.btn-action, .btn-add').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!this.disabled) {
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    this.disabled = true;
                }
            });
        });

        // Show notification if session message exists
        <?php if (isset($_SESSION["pesan"])): ?>
        setTimeout(() => {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--success);
                color: white;
                padding: 15px 25px;
                border-radius: var(--border-radius);
                box-shadow: var(--box-shadow);
                z-index: 1000;
                display: flex;
                align-items: center;
                gap: 10px;
                animation: slideIn 0.3s ease;
            `;
            notification.innerHTML = `
                <i class="fas fa-check-circle"></i>
                <span><?= addslashes($_SESSION["pesan"]) ?></span>
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }, 500);
        <?php unset($_SESSION["pesan"]); endif; ?>

        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
<?php $conn->close(); ?>