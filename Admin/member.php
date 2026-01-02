<?php 
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../service/index.php");
    exit;
}

include '../service/conection.php';

// Masa aktif member 1 menit (60 detik)
$durasi_aktif_detik = 360;
$tgl_sekarang = date('Y-m-d H:i:s');

// Cek member aktif yang sudah kadaluarsa
$expired_members = [];
$cek_query = mysqli_query($conn, "
    SELECT id_member, nama_member, tanggal_aktif 
    FROM member 
    WHERE status = 'aktif' 
    AND TIMESTAMPDIFF(SECOND, tanggal_aktif, '$tgl_sekarang') >= $durasi_aktif_detik
");

while ($row = mysqli_fetch_assoc($cek_query)) {
    $expired_members[] = $row;
}

if (count($expired_members) > 0) {
    $ids = array_column($expired_members, 'id_member');
    $ids_string = implode(',', $ids);
    mysqli_query($conn, "UPDATE member SET status = 'tidak aktif' WHERE id_member IN ($ids_string)");
}

// Hitung statistik
$total_member = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM member"))['total'] ?? 0;
$member_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM member WHERE status = 'aktif'"))['total'] ?? 0;
$total_poin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(poin) as total FROM member"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Manajemen Member - TimelessWatch.co</title>
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
        --info: #2196f3;
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
    .stat-card:nth-child(4) .stat-icon { background: var(--accent); }

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

    .form-row {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .form-group {
        flex: 1;
        min-width: 250px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--dark);
        font-size: 14px;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid var(--gray-light);
        border-radius: var(--border-radius);
        font-size: 14px;
        transition: var(--transition);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
    }

    /* Buttons */
    .btn {
        padding: 12px 24px;
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
    }

    td:last-child {
        padding-right: 30px;
        text-align: center;
    }

    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 6px 12px;
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

    /* Poin Badge */
    .poin-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: rgba(255, 152, 0, 0.1);
        color: var(--warning);
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid rgba(255, 152, 0, 0.3);
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
        .form-row {
            flex-direction: column;
        }
        .form-group {
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
            <p>Member Management</p>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="kategori.php"><i class="fas fa-tags"></i> Kategori</a></li>
                <li><a href="produk.php"><i class="fas fa-box-open"></i> Produk</a></li>
                <li><a href="member.php" class="active"><i class="fas fa-users"></i> Member</a></li>
                <li><a href="admin.php"><i class="fas fa-user-shield"></i> Admin</a></li>
                <li><a href="laporan.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
                <li><a href="transaksi.php"><i class="fas fa-shopping-cart"></i> Transaksi</a></li>
                <li><a href="../service/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="page-title">
                <h1><i class="fas fa-users"></i> Manajemen Member</h1>
                <p>Kelola data member dan sistem loyalty poin</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?= $total_member ?></div>
                <div class="stat-label">Total Member</div>
            </div>
            
            <div class="stat-card orange">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-value"><?= $member_aktif ?></div>
                <div class="stat-label">Member Aktif</div>
            </div>
            
            <div class="stat-card green">
                <div class="stat-icon">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="stat-value"><?= $total_poin ?></div>
                <div class="stat-label">Total Poin</div>
            </div>
            
            <div class="stat-card cyan">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?= $durasi_aktif_detik / 60 ?> min</div>
                <div class="stat-label">Durasi Aktif</div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="form-section">
            <?php
            $nama = "";
            $telp = "";
            $status = "tidak aktif";
            $edit_id = 0;

            if (isset($_GET['edit'])) {
                $edit_id = intval($_GET['edit']);
                $edit_query = mysqli_query($conn, "SELECT * FROM member WHERE id_member = $edit_id");
                if ($edit_query && mysqli_num_rows($edit_query) > 0) {
                    $row = mysqli_fetch_assoc($edit_query);
                    $nama = $row['nama_member'];
                    $telp = $row['no_telp'];
                    $status = $row['status'];
                }
            }
            ?>
            
            <h2><i class="fas <?= $edit_id ? 'fa-user-edit' : 'fa-user-plus' ?>"></i> 
                <?= $edit_id ? 'Edit Member' : 'Tambah Member Baru' ?>
            </h2>
            
            <form method="POST" action="">
                <input type="hidden" name="id_member" value="<?= $edit_id; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nama_member">Nama Member</label>
                        <input type="text" 
                               id="nama_member" 
                               name="nama_member" 
                               class="form-control" 
                               placeholder="Masukkan nama member..." 
                               value="<?= htmlspecialchars($nama); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="no_telp">Nomor Telepon</label>
                        <input type="text" 
                               id="no_telp" 
                               name="no_telp" 
                               class="form-control" 
                               placeholder="Contoh: 081234567890" 
                               value="<?= htmlspecialchars($telp); ?>" 
                               required>
                    </div>
                </div>
                
                <?php if ($edit_id > 0): ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="aktif" <?= $status == 'aktif' ? "selected" : ""; ?>>Aktif</option>
                            <option value="tidak aktif" <?= $status == 'tidak aktif' ? "selected" : ""; ?>>Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="form-row">
                    <button type="submit" name="simpan" class="btn <?= $edit_id ? 'btn-warning' : 'btn-primary' ?>">
                        <i class="fas fa-save"></i> <?= $edit_id ? 'Update Member' : 'Simpan Member' ?>
                    </button>
                    
                    <?php if ($edit_id): ?>
                    <a href="member.php" class="btn" style="background: var(--gray); color: white;">
                        <i class="fas fa-times"></i> Batal Edit
                    </a>
                    <?php endif; ?>
                </div>
            </form>
            
            <?php
            if (isset($_POST['simpan'])) {
                $id_member = intval($_POST['id_member']);
                $nama_member = mysqli_real_escape_string($conn, $_POST['nama_member']);
                $no_telp = mysqli_real_escape_string($conn, $_POST['no_telp']);
                $status = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : 'tidak aktif';

                // Cek duplikat no telp
                $cek_duplikat = mysqli_query($conn, "
                    SELECT id_member FROM member 
                    WHERE no_telp = '$no_telp' 
                    AND id_member != $id_member
                ");

                if (mysqli_num_rows($cek_duplikat) > 0) {
                    echo "<script>
                        alert('Nomor telepon sudah digunakan oleh member lain!');
                        window.location.href = 'member.php" . ($edit_id ? "?edit=$edit_id" : "") . "';
                    </script>";
                    exit;
                }

                if ($id_member > 0) {
                    // Update member
                    $sql_update = "UPDATE member SET 
                        nama_member = '$nama_member', 
                        no_telp = '$no_telp', 
                        status = '$status'" .
                        ($status === 'aktif' ? ", tanggal_aktif = NOW()" : "") . 
                        " WHERE id_member = $id_member";
                    
                    if (mysqli_query($conn, $sql_update)) {
                        echo "<script>
                            alert('Data member berhasil diperbarui!');
                            window.location.href = 'member.php';
                        </script>";
                    }
                } else {
                    // Insert member baru
                    $sql_insert = "INSERT INTO member (nama_member, no_telp, poin, status, tanggal_aktif) 
                                   VALUES ('$nama_member', '$no_telp', 0, 'tidak aktif', NULL)";
                    
                    if (mysqli_query($conn, $sql_insert)) {
                        echo "<script>
                            alert('Member baru berhasil ditambahkan!');
                            window.location.href = 'member.php';
                        </script>";
                    }
                }
                exit;
            }
            ?>
        </div>

        <!-- Table Section -->
        <div class="table-section">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Daftar Member</h2>
                <span style="color: var(--gray); font-size: 14px;">
                    <?= $total_member ?> member terdaftar
                </span>
            </div>

            <?php
            $data = mysqli_query($conn, "SELECT * FROM member ORDER BY id_member DESC");
            if (mysqli_num_rows($data) > 0):
            ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Member</th>
                        <th>No Telepon</th>
                        <th>Poin</th>
                        <th>Status</th>
                        <th>Terakhir Aktif</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($data)): 
                        $status_class = $row['status'] == 'aktif' ? 'status-active' : 'status-inactive';
                        $status_icon = $row['status'] == 'aktif' ? 'fa-check-circle' : 'fa-times-circle';
                    ?>
                    <tr>
                        <td>#<?= $row['id_member'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($row['nama_member']) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($row['no_telp']) ?></td>
                        <td>
                            <span class="poin-badge">
                                <i class="fas fa-coins"></i> <?= $row['poin'] ?> poin
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?= $status_class ?>">
                                <i class="fas <?= $status_icon ?>"></i> <?= $row['status'] ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['tanggal_aktif']): ?>
                                <?= date('d/m/Y', strtotime($row['tanggal_aktif'])) ?>
                                <br>
                                <small style="color: var(--gray);">
                                    <?= date('H:i', strtotime($row['tanggal_aktif'])) ?>
                                </small>
                            <?php else: ?>
                                <span style="color: var(--gray); font-style: italic;">Belum aktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-edit" 
                                        onclick="window.location.href='member.php?edit=<?= $row['id_member'] ?>'"
                                        title="Edit Member">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <button class="btn-action btn-delete" 
                                        onclick="confirmDelete(<?= $row['id_member'] ?>, '<?= htmlspecialchars(addslashes($row['nama_member'])) ?>', '<?= $row['status'] ?>')"
                                        title="Hapus Member">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>Belum ada member terdaftar</h3>
                <p>Mulai dengan menambahkan member pertama Anda</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; <?= date('Y') ?> TimelessWatch.co - Member Management System</p>
            <p style="font-size: 12px; margin-top: 5px;">
                <i class="fas fa-info-circle"></i> Member tidak aktif otomatis setelah 6 menit tidak transaksi
            </p>
        </div>
    </div>

    <script>
        // Konfirmasi hapus member
        function confirmDelete(id, name, status) {
            if (status === 'aktif') {
                alert(`Member "${name}" sedang aktif!\n\nTidak dapat menghapus member yang sedang aktif.`);
                return false;
            }
            
            if (confirm(`Apakah Anda yakin ingin menghapus member "${name}"?\n\nTindakan ini tidak dapat dibatalkan.`)) {
                window.location.href = `member.php?hapus=${id}`;
            }
        }

        // Show notification for expired members
        <?php if (count($expired_members) > 0): ?>
        setTimeout(() => {
            const expiredNames = <?= json_encode(array_map(fn($m) => $m['nama_member'], $expired_members)); ?>;
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--warning);
                color: white;
                padding: 15px 25px;
                border-radius: var(--border-radius);
                box-shadow: var(--box-shadow);
                z-index: 1000;
                animation: slideIn 0.3s ease;
                max-width: 400px;
            `;
            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <i class="fas fa-clock"></i>
                    <strong>Peringatan: Member Tidak Aktif</strong>
                </div>
                <div style="font-size: 13px;">
                    ${expiredNames.length} member telah dinonaktifkan karena tidak transaksi selama 6 menit:
                    <ul style="margin: 10px 0 0 20px;">
                        ${expiredNames.map(name => `<li>${name}</li>`).join('')}
                    </ul>
                </div>
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 8000);
        }, 1000);
        <?php endif; ?>

        // Format phone number input
        const phoneInput = document.getElementById('no_telp');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 12) value = value.substring(0, 12);
                e.target.value = value;
            });
        }

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + F untuk focus search (bisa ditambahkan search nanti)
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                const searchField = document.querySelector('input[type="text"]');
                if (searchField) searchField.focus();
            }
        });

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

    <?php
    // Handle delete
    if (isset($_GET['hapus'])) {
        $hapus_id = intval($_GET['hapus']);
        if ($hapus_id > 0) {
            $cek = mysqli_query($conn, "SELECT status FROM member WHERE id_member = $hapus_id");
            $row = mysqli_fetch_assoc($cek);
            if ($row && $row['status'] === 'aktif') {
                echo "<script>
                    alert('Tidak bisa menghapus member yang sedang aktif!');
                    window.location.href = 'member.php';
                </script>";
                exit;
            }
            mysqli_query($conn, "DELETE FROM member WHERE id_member = $hapus_id");
            echo "<script>
                alert('Member berhasil dihapus!');
                window.location.href = 'member.php';
            </script>";
            exit;
        }
    }
    ?>
</body>
</html>
<?php mysqli_close($conn); ?>