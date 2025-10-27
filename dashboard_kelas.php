<?php
include "config.php";
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'kelas') {
    header("Location: login.php");
    exit();
}

$kelas = $_SESSION['username'];
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

$where = "WHERE siswa.kelas='$kelas'";
if ($tanggal != '') $where .= " AND absensi.tanggal='$tanggal'";

$query = "
SELECT siswa.nama, siswa.kelas, absensi.tanggal, absensi.keterangan
FROM absensi 
JOIN siswa ON absensi.siswa_id = siswa.id
$where
ORDER BY absensi.tanggal DESC, siswa.nama ASC
";

$data = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kelas - Sistem Absensi</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="layout-container">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <div class="sidebar-logo-icon">📚</div>
                <div class="sidebar-logo-text">Absensi</div>
            </div>

            <ul class="sidebar-menu">
                <li class="sidebar-menu-item">
                    <a href="#" class="sidebar-menu-link active">
                        <span class="sidebar-menu-icon">📋</span>
                        <span>Data Absensi</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="logout.php" class="sidebar-menu-link">
                        <span class="sidebar-menu-icon">🚪</span>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- HEADER -->
            <header class="header">
                <div class="header-title">
                    <h1>Dashboard Kelas: <?= htmlspecialchars($kelas) ?></h1>
                    <div class="header-date"><?= date('l, d F Y') ?></div>
                </div>
                <div class="header-user">
                    <div class="user-avatar"><?= strtoupper(substr($kelas, 0, 1)) ?></div>
                    <div>
                        <div style="font-weight: 600;"><?= htmlspecialchars($kelas) ?></div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary);">Kelas</div>
                    </div>
                </div>
            </header>

            <!-- CONTENT -->
            <div class="content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Data Absensi</h3>
                    </div>
                    <div class="card-body">
                        <form method="get" class="filter-section">
                            <div class="filter-group">
                                <label for="tanggal">Filter Tanggal</label>
                                <input type="date" name="tanggal" id="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
                            </div>
                            <div class="filter-actions">
                                <button type="submit" class="btn btn-primary">Tampilkan</button>
                                <?php if ($tanggal): ?>
                                    <a href="dashboard_kelas.php" class="btn btn-secondary">Reset</a>
                                <?php endif; ?>
                            </div>
                        </form>

                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Siswa</th>
                                        <th>Tanggal</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    if (mysqli_num_rows($data) > 0):
                                        while ($row = mysqli_fetch_assoc($data)): 
                                            $status_class = 'status-' . strtolower($row['keterangan']);
                                    ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= date('d F Y', strtotime($row['tanggal'])) ?></td>
                                        <td>
                                            <span class="status-badge <?= $status_class ?>">
                                                <?= htmlspecialchars($row['keterangan']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 2rem;">
                                            <p style="color: var(--text-secondary);">Belum ada data absensi</p>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
