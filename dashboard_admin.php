<?php
session_start();
require "config.php";

// 🔒 Akses hanya untuk admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// 🗂 Ambil daftar kelas
$stmt = $conn->prepare("SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC");
$stmt->execute();
$result_kelas = $stmt->get_result();
$kelas_list = $result_kelas->fetch_all(MYSQLI_ASSOC);

// 📅 Ambil parameter GET
$kelas   = $_GET['kelas']   ?? ($kelas_list[0]['kelas'] ?? '');
$tanggal = $_GET['tanggal'] ?? date("Y-m-d");

// 🧾 Ambil daftar siswa untuk kelas tertentu
$stmt = $conn->prepare("SELECT * FROM siswa WHERE kelas = ? ORDER BY nama ASC");
$stmt->bind_param("s", $kelas);
$stmt->execute();
$siswa = $stmt->get_result();

// 💾 Simpan absensi
if (isset($_POST['simpan'])) {
    foreach ($_POST['keterangan'] as $id_siswa => $ket) {
        $allowed = ['Hadir', 'Izin', 'Sakit', 'Alfa'];
        if (!in_array($ket, $allowed)) $ket = 'Alfa';

        $stmt = $conn->prepare("SELECT keterangan FROM absensi WHERE siswa_id=? AND tanggal=?");
        $stmt->bind_param("is", $id_siswa, $tanggal);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if ($row['keterangan'] != $ket) {
                $stmt = $conn->prepare("UPDATE absensi SET keterangan=? WHERE siswa_id=? AND tanggal=?");
                $stmt->bind_param("sis", $ket, $id_siswa, $tanggal);
                $stmt->execute();
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO absensi (siswa_id, tanggal, keterangan) VALUES (?,?,?)");
            $stmt->bind_param("iss", $id_siswa, $tanggal, $ket);
            $stmt->execute();
        }
    }

    $success = "✅ Absensi berhasil disimpan / diperbarui untuk tanggal $tanggal";
}

// 📊 Ambil data rekap jika ada parameter bulan
$rekap = null;
if (isset($_GET['bulan']) && isset($_GET['tahun'])) {
    $bulan = $_GET['bulan'];
    $tahun = $_GET['tahun'];
    $dari = "$tahun-$bulan-01";
    $sampai = date("Y-m-t", strtotime($dari));

    $stmt = $conn->prepare("
        SELECT s.nama,
            SUM(a.keterangan='Hadir') AS Hadir,
            SUM(a.keterangan='Izin')  AS Izin,
            SUM(a.keterangan='Sakit') AS Sakit,
            SUM(a.keterangan='Alfa')  AS Alfa
        FROM siswa s
        LEFT JOIN absensi a 
            ON s.id = a.siswa_id 
            AND a.tanggal BETWEEN ? AND ?
        WHERE s.kelas = ?
        GROUP BY s.id
        ORDER BY s.nama ASC
    ");
    $stmt->bind_param("sss", $dari, $sampai, $kelas);
    $stmt->execute();
    $rekap = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Absensi</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .tab-buttons {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
        }

        .tab-button {
            padding: 0.75rem 1.5rem;
            background: none;
            border: none;
            color: var(--text-secondary);
            font-weight: 600;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: var(--transition);
            font-size: 0.95rem;
        }

        .tab-button.active {
            color: var(--accent-primary);
            border-bottom-color: var(--accent-primary);
        }

        .tab-button:hover {
            color: var(--text-primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .attendance-table select {
            width: 100%;
            padding: 0.5rem;
            background-color: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-primary);
            cursor: pointer;
        }

        .attendance-table select:focus {
            outline: none;
            border-color: var(--accent-primary);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

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
                    <a href="#" class="sidebar-menu-link active" onclick="switchTab('daily'); return false;">
                        <span class="sidebar-menu-icon">📅</span>
                        <span>Absensi Harian</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="#" class="sidebar-menu-link" onclick="switchTab('monthly'); return false;">
                        <span class="sidebar-menu-icon">📊</span>
                        <span>Rekap Bulanan</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="manage_siswa.php" class="sidebar-menu-link">
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
                    <h1>Dashboard Admin</h1>
                    <div class="header-date"><?= date('l, d F Y', strtotime($tanggal)) ?></div>
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
                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <span>✅</span>
                        <span><?= htmlspecialchars($success) ?></span>
                    </div>
                <?php endif; ?>

                <!-- TAB BUTTONS -->
                <div class="tab-buttons">
                    <button class="tab-button active" onclick="switchTab('daily')">📅 Absensi Harian</button>
                    <button class="tab-button" onclick="switchTab('monthly')">📊 Rekap Bulanan</button>
                </div>

                <!-- TAB 1: ABSENSI HARIAN -->
                <div id="daily" class="tab-content active">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Absensi Harian</h3>
                        </div>
                        <div class="card-body">
                            <form method="get" class="form-row">
                                <div class="form-group">
                                    <label for="kelas">Kelas</label>
                                    <select name="kelas" id="kelas" required onchange="this.form.submit()">
                                        <?php foreach ($kelas_list as $k): ?>
                                            <option value="<?= htmlspecialchars($k['kelas']) ?>" <?= $k['kelas'] == $kelas ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($k['kelas']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="tanggal">Tanggal</label>
                                    <input type="date" name="tanggal" id="tanggal" value="<?= $tanggal ?>" required onchange="this.form.submit()">
                                </div>
                            </form>

                            <form method="post" class="attendance-table">
                                <div class="table-container">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Siswa</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $no = 1;
                                            $siswa->data_seek(0);
                                            while ($row = $siswa->fetch_assoc()):
                                                $stmt = $conn->prepare("SELECT keterangan FROM absensi WHERE siswa_id=? AND tanggal=?");
                                                $stmt->bind_param("is", $row['id'], $tanggal);
                                                $stmt->execute();
                                                $abs = $stmt->get_result()->fetch_assoc();
                                                $ket_existing = $abs['keterangan'] ?? '';
                                            ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                                <td>
                                                    <select name="keterangan[<?= $row['id'] ?>]">
                                                        <option value="Hadir" <?= $ket_existing == 'Hadir' ? 'selected' : '' ?>>Hadir</option>
                                                        <option value="Izin"  <?= $ket_existing == 'Izin'  ? 'selected' : '' ?>>Izin</option>
                                                        <option value="Sakit" <?= $ket_existing == 'Sakit' ? 'selected' : '' ?>>Sakit</option>
                                                        <option value="Alfa"  <?= $ket_existing == 'Alfa'  ? 'selected' : '' ?>>Alfa</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="action-buttons">
                                    <button type="submit" name="simpan" class="btn btn-primary">
                                        <span>💾</span>
                                        <span>Simpan Absensi</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: REKAP BULANAN -->
                <div id="monthly" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Rekap Absensi Bulanan</h3>
                        </div>
                        <div class="card-body">
                            <form method="get" class="form-row">
                                <input type="hidden" name="kelas" value="<?= htmlspecialchars($kelas) ?>">
                                
                                <div class="form-group">
                                    <label for="bulan">Bulan</label>
                                    <select name="bulan" id="bulan" required>
                                        <?php 
                                        $current_month = date('n');
                                        for ($i = 1; $i <= 12; $i++) {
                                            $bulan_nama = date("F", mktime(0, 0, 0, $i, 1));
                                            $selected = (isset($_GET['bulan']) && $_GET['bulan'] == $i) ? 'selected' : ($i == $current_month ? 'selected' : '');
                                            echo "<option value='$i' $selected>".ucfirst($bulan_nama)."</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="tahun">Tahun</label>
                                    <select name="tahun" id="tahun" required>
                                        <?php
                                        $year_now = date("Y");
                                        $current_year = date('Y');
                                        for ($y = $year_now - 2; $y <= $year_now; $y++) {
                                            $selected = (isset($_GET['tahun']) && $_GET['tahun'] == $y) ? 'selected' : ($y == $current_year ? 'selected' : '');
                                            echo "<option value='$y' $selected>$y</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary" style="width: 100%;">Tampilkan Rekap</button>
                                </div>
                            </form>

                            <?php if ($rekap && $rekap->num_rows > 0): ?>
                                <div style="margin-top: 2rem;">
                                    <h4 style="margin-bottom: 1rem;">
                                        Rekapan <?= htmlspecialchars($kelas) ?> — 
                                        <?php 
                                        if (isset($_GET['bulan']) && isset($_GET['tahun'])) {
                                            echo date("F Y", strtotime($_GET['tahun']."-".$_GET['bulan']."-01"));
                                        }
                                        ?>
                                    </h4>

                                    <div class="table-container">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nama</th>
                                                    <th>Hadir</th>
                                                    <th>Izin</th>
                                                    <th>Sakit</th>
                                                    <th>Alfa</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $no = 1;
                                                while ($r = $rekap->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= htmlspecialchars($r['nama']) ?></td>
                                                    <td><span class="status-hadir"><?= $r['Hadir'] ?? 0 ?></span></td>
                                                    <td><span class="status-izin"><?= $r['Izin'] ?? 0 ?></span></td>
                                                    <td><span class="status-sakit"><?= $r['Sakit'] ?? 0 ?></span></td>
                                                    <td><span class="status-alfa"><?= $r['Alfa'] ?? 0 ?></span></td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="action-buttons" style="margin-top: 1.5rem;">
                                        <form method="post" action="export_excel.php" target="_blank">
                                            <input type="hidden" name="kelas" value="<?= htmlspecialchars($kelas) ?>">
                                            <input type="hidden" name="dari" value="<?= isset($_GET['tahun']) && isset($_GET['bulan']) ? $_GET['tahun']."-".$_GET['bulan']."-01" : '' ?>">
                                            <input type="hidden" name="sampai" value="<?= isset($_GET['tahun']) && isset($_GET['bulan']) ? date("Y-m-t", strtotime($_GET['tahun']."-".$_GET['bulan']."-01")) : '' ?>">
                                            <button type="submit" name="export" value="1" class="btn btn-success">
                                                <span>📤</span>
                                                <span>Export ke Excel</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tabs
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.classList.remove('active'));

            // Remove active class from all buttons
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(btn => btn.classList.remove('active'));

            // Show selected tab
            document.getElementById(tabName).classList.add('active');

            // Add active class to clicked button
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
