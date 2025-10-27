<?php
include "config.php";
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Header agar browser mengenali file sebagai Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=rekap_absensi_" . date('Y-m-d') . ".xls");

// Query rekap semua absensi
$query = mysqli_query($conn, "
    SELECT siswa.nama, siswa.kelas, absensi.tanggal, absensi.keterangan
    FROM absensi 
    JOIN siswa ON absensi.siswa_id = siswa.id 
    ORDER BY absensi.tanggal DESC, siswa.kelas ASC, siswa.nama ASC
");

echo "<table border='1'>";
echo "<tr>
        <th>No</th>
        <th>Nama Siswa</th>
        <th>Kelas</th>
        <th>Tanggal</th>
        <th>Keterangan</th>
      </tr>";

$no = 1;
while ($row = mysqli_fetch_assoc($query)) {
    echo "<tr>
            <td>{$no}</td>
            <td>{$row['nama']}</td>
            <td>{$row['kelas']}</td>
            <td>{$row['tanggal']}</td>
            <td>{$row['keterangan']}</td>
          </tr>";
    $no++;
}
echo "</table>";
?>
