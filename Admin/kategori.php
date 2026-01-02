<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../service/index.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "kasir");

// Tambah / Edit Kategori
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama_kategori']);
    $nama = $conn->real_escape_string($nama);

    if (isset($_POST['id_kategori'])) {
        // Edit
        $id = intval($_POST['id_kategori']);
        $cek = $conn->query("SELECT COUNT(*) AS total FROM kategori WHERE nama_kategori = '$nama' AND id_kategori != $id");
        $data = $cek->fetch_assoc();
        if ($data['total'] > 0) {
            $_SESSION['pesan'] = "Nama kategori '$nama' sudah ada!";
            header("Location: kategori.php");
            exit();
        }

        $conn->query("UPDATE kategori SET nama_kategori='$nama' WHERE id_kategori=$id");
        $_SESSION['pesan'] = "Kategori berhasil diperbarui!";
    } else {
        // Tambah
        $cek = $conn->query("SELECT COUNT(*) AS total FROM kategori WHERE nama_kategori = '$nama'");
        $data = $cek->fetch_assoc();
        if ($data['total'] > 0) {
            $_SESSION['pesan'] = "Nama kategori '$nama' sudah ada!";
            header("Location: kategori.php");
            exit();
        }

        $conn->query("INSERT INTO kategori (nama_kategori, tgl_input) VALUES ('$nama', NOW())");
        $_SESSION['pesan'] = "Kategori baru berhasil ditambahkan!";
    }

    header("Location: kategori.php");
    exit();
}

// Hapus (dengan pengecekan relasi ke produk)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Cek apakah kategori masih dipakai di tabel produk
    $cek_produk = $conn->query("SELECT COUNT(*) as jumlah FROM produk WHERE fid_kategori = $id");
    $data_produk = $cek_produk->fetch_assoc();

    if ($data_produk['jumlah'] > 0) {
        $_SESSION['pesan'] = "Kategori tidak dapat dihapus karena masih digunakan oleh " . $data_produk['jumlah'] . " produk!";
        header("Location: kategori.php");
        exit();
    }

    // Jika tidak dipakai, lanjut hapus
    $conn->query("DELETE FROM kategori WHERE id_kategori=$id");
    $_SESSION['pesan'] = "Kategori berhasil dihapus!";
    header("Location: kategori.php");
    exit();
}

// Ambil data untuk form edit
$edit = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM kategori WHERE id_kategori=$id");
    $edit = $res->fetch_assoc();
}

// Ambil semua kategori dengan jumlah produk
$query = $conn->query("
    SELECT k.*, COUNT(p.id_produk) as jumlah_produk 
    FROM kategori k 
    LEFT JOIN produk p ON k.id_kategori = p.fid_kategori 
    GROUP BY k.id_kategori 
    ORDER BY k.id_kategori DESC
");
$kategori = $query->fetch_all(MYSQLI_ASSOC);

// Hitung statistik
$total_kategori = $conn->query("SELECT COUNT(*) as total FROM kategori")->fetch_assoc()['total'];
$kategori_terbaru = $conn->query("SELECT nama_kategori FROM kategori ORDER BY tgl_input DESC LIMIT 1")->fetch_assoc()['nama_kategori'] ?? '-';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kategori - TimelessWatch.co</title>
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

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            border-left: 5px solid var(--primary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-card.orange { border-left-color: var(--secondary); }
        .stat-card.green { border-left-color: var(--success); }
        .stat-card.cyan { border-left-color: var(--accent); }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: white;
        }

        .stat-card:nth-child(1) .stat-icon { background: var(--primary); }
        .stat-card:nth-child(2) .stat-icon { background: var(--secondary); }
        .stat-card:nth-child(3) .stat-icon { background: var(--success); }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--gray);
            font-weight: 500;
        }

        /* Form Section */
        .form-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }

        .form-section h2 {
            font-size: 20px;
            color: var(--primary);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .input-group {
            flex: 1;
            min-width: 300px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }

        .input-group input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid var(--gray-light);
            border-radius: var(--border-radius);
            font-size: 15px;
            transition: var(--transition);
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
        }

        /* Buttons */
        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(26, 35, 126, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(26, 35, 126, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #2e7d32 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning) 0%, #ef6c00 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
        }

        .btn-warning:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 152, 0, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #c62828 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(244, 67, 54, 0.4);
        }

        /* Table Section */
        .table-section {
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

        /* Product Count Badge */
        .product-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
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
            .form-group {
                flex-direction: column;
                align-items: stretch;
            }
            .input-group {
                min-width: 100%;
            }
            .table-section {
                overflow-x: auto;
            }
            table {
                min-width: 800px;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
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
            <p>Kategori Management</p>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="kategori.php" class="active"><i class="fas fa-tags"></i> Kategori</a></li>
                <li><a href="produk.php"><i class="fas fa-box-open"></i> Produk</a></li>
                <li><a href="member.php"><i class="fas fa-users"></i> Member</a></li>
                <li><a href="admin.php"><i class="fas fa-user-shield"></i> Admin</a></li>
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
                <h1><i class="fas fa-tags"></i> Manajemen Kategori</h1>
                <p>Kelola kategori produk di toko Anda</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="stat-value"><?= $total_kategori ?></div>
                <div class="stat-label">Total Kategori</div>
            </div>
            
            <div class="stat-card orange">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?= count($kategori) > 0 ? 'New' : '-' ?></div>
                <div class="stat-label">Kategori Terbaru</div>
                <div style="font-size: 13px; color: var(--gray); margin-top: 5px;"><?= $kategori_terbaru ?></div>
            </div>
            
            <div class="stat-card green">
                <div class="stat-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-value">
                    <?php 
                    $total_produk = array_sum(array_column($kategori, 'jumlah_produk'));
                    echo $total_produk;
                    ?>
                </div>
                <div class="stat-label">Total Produk di Semua Kategori</div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="form-section">
            <h2><i class="fas <?= $edit ? 'fa-edit' : 'fa-plus-circle' ?>"></i> 
                <?= $edit ? 'Edit Kategori' : 'Tambah Kategori Baru' ?>
            </h2>
            
            <form method="post">
                <div class="form-group">
                    <div class="input-group">
                        <label for="nama_kategori">Nama Kategori</label>
                        <input type="text" 
                               id="nama_kategori" 
                               name="nama_kategori" 
                               placeholder="Masukkan nama kategori..." 
                               value="<?= htmlspecialchars($edit['nama_kategori'] ?? '') ?>" 
                               required
                               autofocus>
                    </div>
                    
                    <?php if ($edit): ?>
                        <input type="hidden" name="id_kategori" value="<?= $edit['id_kategori'] ?>">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Update Kategori
                        </button>
                        <a href="kategori.php" class="btn" style="background: var(--gray); color: white;">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Kategori
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Table Section -->
        <div class="table-section">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Daftar Kategori</h2>
                <span style="color: var(--gray); font-size: 14px;">
                    <?= count($kategori) ?> kategori ditemukan
                </span>
            </div>

            <?php if (count($kategori) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Kategori</th>
                        <th>Jumlah Produk</th>
                        <th>Tanggal Input</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kategori as $row): ?>
                    <tr>
                        <td>#<?= $row['id_kategori'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($row['nama_kategori']) ?></strong>
                        </td>
                        <td>
                            <span class="product-badge">
                                <i class="fas fa-box"></i> <?= $row['jumlah_produk'] ?> produk
                            </span>
                        </td>
                        <td>
                            <?= date('d M Y', strtotime($row['tgl_input'])) ?>
                            <br>
                            <small style="color: var(--gray);">
                                <?= date('H:i', strtotime($row['tgl_input'])) ?>
                            </small>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-edit" 
                                        onclick="window.location.href='kategori.php?edit=<?= $row['id_kategori'] ?>'"
                                        title="Edit Kategori">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <button class="btn-action btn-delete" 
                                        onclick="confirmDelete(<?= $row['id_kategori'] ?>, '<?= htmlspecialchars(addslashes($row['nama_kategori'])) ?>', <?= $row['jumlah_produk'] ?>)"
                                        title="Hapus Kategori">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-tags"></i>
                <h3>Belum ada kategori</h3>
                <p>Mulai dengan menambahkan kategori pertama Anda</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; <?= date('Y') ?> TimelessWatch.co - Kategori Management System</p>
            <p style="font-size: 12px; margin-top: 5px;">
                <i class="fas fa-info-circle"></i> Kategori tidak dapat dihapus jika masih digunakan oleh produk
            </p>
        </div>
    </div>

    <script>
        // Konfirmasi hapus dengan pesan khusus
        function confirmDelete(id, nama, jumlahProduk) {
            if (jumlahProduk > 0) {
                alert(`Kategori "${nama}" tidak dapat dihapus karena masih digunakan oleh ${jumlahProduk} produk!\n\nHarap hapus atau pindahkan produk terlebih dahulu.`);
                return false;
            }
            
            if (confirm(`Apakah Anda yakin ingin menghapus kategori "${nama}"?\n\nTindakan ini tidak dapat dibatalkan.`)) {
                window.location.href = `kategori.php?delete=${id}`;
            }
        }

        // Auto focus pada input
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('nama_kategori');
            if (input) {
                input.focus();
                input.select();
            }
        });

        // Show notification if session message exists
        <?php if (isset($_SESSION['pesan'])): ?>
        setTimeout(() => {
            const notification = document.createElement('div');
            const message = "<?= addslashes($_SESSION['pesan']) ?>";
            const isError = message.includes('tidak dapat') || message.includes('sudah ada');
            
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${isError ? 'var(--danger)' : 'var(--success)'};
                color: white;
                padding: 15px 25px;
                border-radius: var(--border-radius);
                box-shadow: var(--box-shadow);
                z-index: 1000;
                display: flex;
                align-items: center;
                gap: 10px;
                animation: slideIn 0.3s ease;
                max-width: 400px;
            `;
            notification.innerHTML = `
                <i class="fas ${isError ? 'fa-exclamation-triangle' : 'fa-check-circle'}"></i>
                <span>${message}</span>
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 4000);
        }, 500);
        <?php unset($_SESSION['pesan']); endif; ?>

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