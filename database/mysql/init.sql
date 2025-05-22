-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS mahasiswa_db;
USE mahasiswa_db;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'staff') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create mahasiswa table
CREATE TABLE IF NOT EXISTS mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    jenis_kelamin ENUM('Laki-laki', 'Perempuan') NOT NULL,
    tanggal_lahir DATE NOT NULL,
    alamat TEXT NOT NULL,
    jurusan VARCHAR(100) NOT NULL,
    fakultas VARCHAR(100) NOT NULL,
    ipk DECIMAL(3,2),
    angkatan INT NOT NULL,
    status ENUM('Aktif', 'Cuti', 'Lulus', 'Drop Out') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert demo users
-- Password: password (hashed)
INSERT INTO users (username, password, name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin'),
('staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff User', 'staff');

-- Insert demo mahasiswa
INSERT INTO mahasiswa (nim, nama, jenis_kelamin, tanggal_lahir, alamat, jurusan, fakultas, ipk, angkatan, status) VALUES
('2023001', 'Budi Santoso', 'Laki-laki', '2000-05-15', 'Jl. Merdeka No. 10, Jakarta', 'Teknik Informatika', 'Fakultas Teknologi Informasi', 3.85, 2023, 'Aktif'),
('2023002', 'Siti Nurhaliza', 'Perempuan', '2001-02-20', 'Jl. Pahlawan No. 5, Bandung', 'Sistem Informasi', 'Fakultas Teknologi Informasi', 3.70, 2023, 'Aktif'),
('2022001', 'Andi Wijaya', 'Laki-laki', '1999-11-10', 'Jl. Sudirman No. 25, Surabaya', 'Teknik Elektro', 'Fakultas Teknik', 3.50, 2022, 'Aktif'),
('2022002', 'Diana Putri', 'Perempuan', '2000-08-12', 'Jl. Gajah Mada No. 15, Yogyakarta', 'Manajemen', 'Fakultas Ekonomi dan Bisnis', 3.90, 2022, 'Aktif'),
('2021001', 'Rudi Hermawan', 'Laki-laki', '1998-04-25', 'Jl. Ahmad Yani No. 8, Semarang', 'Akuntansi', 'Fakultas Ekonomi dan Bisnis', 3.40, 2021, 'Aktif');