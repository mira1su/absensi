# 🚀 School Attendance System (AI-Themed Dashboard)

Sebuah sistem absensi sekolah sederhana berbasis **PHP, MySQL, dan HTML**, dengan tema **dark futuristik ala dashboard AI**.  
Dibuat dengan konsep minimalis, ringan, dan mudah dikembangkan.

---

## ✨ Fitur Utama
- 🔐 **Login System** untuk Admin dan Kelas
  - Admin: `admin / password`
  - Kelas: `[nama_kelas] / password`
- 🧠 **Dashboard Admin**
  - Kelola data siswa (tambah, edit, hapus)
  - Rekap absensi harian & bulanan
  - Export data ke **Excel**
- 👩‍🏫 **Dashboard Kelas**
  - Hanya melihat absensi kelas masing-masing
- 🌌 **Tema Gelap Futuristik**
  - Desain terinspirasi dari interface AI
  - Tanpa framework (pure CSS + PHP native)
- 📅 **Sistem Absensi Real-time**
  - Data otomatis tersimpan ke database MySQL

---

## 🧩 Struktur Database

**Table: `user`**
| Field | Type | Keterangan |
|-------|------|------------|
| id | INT | Primary key |
| username | VARCHAR(50) | Nama akun |
| password | VARCHAR(50) | Password sederhana |
| role | ENUM('admin','kelas') | Level akses |

**Table: `siswa`**
| Field | Type | Keterangan |
|-------|------|------------|
| id | INT | Primary key |
| nama | VARCHAR(100) | Nama siswa |
| kelas | VARCHAR(50) | Nama kelas |
| nis | VARCHAR(20) | Nomor induk siswa |

**Table: `absensi`**
| Field | Type | Keterangan |
|-------|------|------------|
| id | INT | Primary key |
| siswa_id | INT | Relasi ke tabel siswa |
| tanggal | DATE | Tanggal absensi |
| keterangan | ENUM('Hadir','Izin','Sakit','Alpha') | Status kehadiran |

---

## 🧰 Teknologi yang Digunakan
- 💻 **Frontend:** HTML5, CSS3 (custom dark theme)
- ⚙️ **Backend:** PHP Native (tanpa framework)
- 🗄️ **Database:** MySQL
- 📊 **Export Data:** Native Excel export via `header()` method

---

## 🚀 Cara Menjalankan Proyek
1. Clone repository ini
   ```bash
   git clone https://github.com/username/absensi-sekolah.git
