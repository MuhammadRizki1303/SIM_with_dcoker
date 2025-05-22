<?php

/**
 * Index Page
 * Landing page for the application
 */

// Include necessary files
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isLoggedIn();

// Include header
include 'partials/header.php';
?>

<!-- Hero section -->
<section class="hero">
    <div class="hero-content">
        <h1>Sistem Informasi Mahasiswa</h1>
        <p>Kelola data mahasiswa dengan mudah, cepat, dan efisien</p>

        <?php if (!$isLoggedIn): ?>
        <div class="hero-buttons">
            <a href="login.php" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        </div>
        <?php else: ?>
        <div class="hero-buttons">
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Features section -->
<section class="features">
    <div class="section-header">
        <h2>Fitur Utama</h2>
        <p>Sistem informasi mahasiswa yang lengkap dan terintegrasi</p>
    </div>

    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-database"></i>
            </div>
            <h3>Pengelolaan Data</h3>
            <p>Kelola data mahasiswa dengan mudah termasuk biodata, riwayat akademik, dan status.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h3>Laporan & Statistik</h3>
            <p>Hasilkan laporan dan statistik real-time untuk analisis dan evaluasi akademik.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-search"></i>
            </div>
            <h3>Pencarian Cepat</h3>
            <p>Temukan data mahasiswa dengan cepat menggunakan fitur pencarian yang canggih.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3>Keamanan Data</h3>
            <p>Sistem keamanan yang terjamin dengan pembatasan akses dan enkripsi data.</p>
        </div>
    </div>
</section>

<!-- About section -->
<section class="about">
    <div class="section-header">
        <h2>Tentang Aplikasi</h2>
        <p>Solusi terbaik untuk manajemen data mahasiswa</p>
    </div>

    <div class="about-content">
        <div class="about-text">
            <p>Sistem Informasi Mahasiswa merupakan aplikasi berbasis web yang dirancang untuk memudahkan pengelolaan
                data mahasiswa di institusi pendidikan. Dengan antarmuka yang user-friendly dan fitur yang komprehensif,
                aplikasi ini membantu staf akademik dalam mengelola berbagai aspek data mahasiswa.</p>

            <p>Dibangun dengan teknologi modern, aplikasi ini menjamin kecepatan akses, keamanan data, dan kemudahan
                penggunaan. Terintegrasi dengan database yang terpusat, semua data tersimpan dengan aman dan dapat
                diakses sesuai dengan hak akses pengguna.</p>
        </div>
    </div>
</section>

<style>
/* Specific styles for index page */
.hero {
    background-color: var(--primary);
    background-image: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    padding: 3rem 1.5rem;
    text-align: center;
    border-radius: var(--border-radius);
    margin-bottom: 2rem;
}

.hero-content h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.hero-content p {
    font-size: 1.25rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.hero-buttons {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.section-header {
    text-align: center;
    margin-bottom: 2rem;
}

.section-header h2 {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: var(--primary-dark);
}

.section-header p {
    color: var(--gray-600);
}

.features {
    margin-bottom: 3rem;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.feature-card {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    text-align: center;
    transition: var(--transition);
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--box-shadow-md);
}

.feature-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background-color: rgba(67, 97, 238, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.feature-icon i {
    font-size: 1.75rem;
    color: var(--primary);
}

.feature-card h3 {
    margin-bottom: 0.75rem;
}

.about {
    margin-bottom: 3rem;
}

.about-content {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 2rem;
}

.about-text p {
    margin-bottom: 1rem;
}

.about-text p:last-child {
    margin-bottom: 0;
}

@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 2rem;
    }

    .hero-content p {
        font-size: 1rem;
    }

    .section-header h2 {
        font-size: 1.75rem;
    }
}
</style>

<?php
// Include footer
include 'partials/footer.php';
?>