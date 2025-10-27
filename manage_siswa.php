<?php
session_start();
require "config.php";

// 🧩 Cek autentikasi admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 🧱 Inisialisasi pesan feedback
$feedback = "";

// ✳️ Tambah siswa baru
if (isset($_POST['tambah'])) {
    $nis   = trim($_POST['nis']);
    $nama  = trim($_POST['nama']);
    $kelas = trim($_POST['kelas']);

    if ($nis === '' || $nama === '' || $kelas === '') {
        $feedback = "⚠️ Semua field wajib diisi.";
    } else {
        $cek_stmt = $conn->prepare("SELECT id FROM siswa WHERE nis = ?");
        $cek_stmt->bind_param("s", $nis);
        $cek_stmt->execute();
        $cek_stmt->store_result();

        if ($cek_stmt->num_rows > 0) {
            $feedback = "❌ NIS sudah terdaftar!";
        } else {
            $stmt = $conn->prepare("INSERT INTO siswa (nis, nama, kelas) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nis, $nama, $kelas);
            $stmt->execute();
            $feedback = "✅ Siswa baru berhasil ditambahkan.";
        }

        $cek_stmt->close();
    }
}

// 🗑️ Hapus siswa
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id > 0) {
        $del_stmt = $conn->prepare("DELETE FROM siswa WHERE id = ?");
        $del_stmt->bind_param("i", $id);
        $del_stmt->execute();
        $feedback = "🗑️ Data siswa telah dihapus.";
        $del_stmt->close();
    }
}

// 📋 Ambil data siswa
$siswa = $conn->query("SELECT * FROM siswa ORDER BY kelas ASC, nama ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Siswa - Sistem Absensi</title>
    <link rel="stylesheet" href="./assets/css/style.css">
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
                    <a href="dashboard_admin.php" class="sidebar-menu-link">
                        <span class="sidebar-menu-icon">📅</span>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="#" class="sidebar-menu-link active">
                        <span class="sidebar-menu-icon">👨‍🎓</span>
                        <span>Kelola Siswa</span>
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
                    <h1>Kelola Data Siswa</h1>
                    <div class="header-date"><?= date('l, d F Y') ?></div>
                </div>
                <div class="header-user">
                    <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                    <div>
                        <div style="font-weight: 600;"><?= htmlspecialchars($_SESSION['username']) ?></div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary);">Administrator</div>
                    </div>
                </div>
            </header>

            <!-- CONTENT -->
            <div class="content">
                <?php if ($feedback): ?>
                    <div class="alert <?= strpos($feedback, '✅') !== false ? 'alert-success' : 'alert-error' ?>">
                        <span><?= strpos($feedback, '✅') !== false ? '✅' : '❌' ?></span>
                        <span><?= htmlspecialchars($feedback) ?></span>
                    </div>
                <?php endif; ?>

                <!-- FORM TAMBAH SISWA -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Tambah Siswa Baru</h3>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                                <div class="form-group">
                                    <label for="nis">NIS (Nomor Induk Siswa)</label>
                                    <input type="text" id="nis" name="nis" required placeholder="Contoh: 2024001">
                                </div>

                                <div class="form-group">
                                    <label for="nama">Nama Lengkap</label>
                                    <input type="text" id="nama" name="nama" required placeholder="Masukkan nama siswa">
                                </div>

                                <div class="form-group">
                                    <label for="kelas">Kelas</label>
                                    <input type="text" id="kelas" name="kelas" required placeholder="Contoh: X RPL 1">
                                </div>
                            </div>

                            <button type="submit" name="tambah" class="btn btn-primary">
                                <span>➕</span>
                                <span>Tambah Siswa</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- TABEL DATA SISWA -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Siswa</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIS</th>
                                        <th>Nama</th>
                                        <th>Kelas</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    if ($siswa->num_rows > 0):
                                        while ($row = $siswa->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nis']) ?></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= htmlspecialchars($row['kelas']) ?></td>
                                        <td>
                                            <a href="?hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus siswa ini?')">
                                                <span>🗑️</span>
                                                <span>Hapus</span>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 2rem;">
                                            <p style="color: var(--text-secondary);">Belum ada data siswa</p>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 2rem;">
                    <a href="dashboard_admin.php" class="btn btn-secondary">
                        <span>⬅️</span>
                        <span>Kembali ke Dashboard</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
