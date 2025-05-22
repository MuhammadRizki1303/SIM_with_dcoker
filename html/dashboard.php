<?php

/**
 * Dashboard Page
 * Main control panel of the application
 */

// Include necessary files
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Require user to be logged in
requireLogin();

// Get dashboard statistics
$stats = [
    'total_mahasiswa' => 0,
    'mahasiswa_aktif' => 0,
    'mahasiswa_lulus' => 0,
    'mahasiswa_cuti' => 0,
];

try {
    // Total mahasiswa
    $totalQuery = "SELECT COUNT(*) as total FROM mahasiswa";
    $result = executeQuerySingle($totalQuery);
    $stats['total_mahasiswa'] = $result ? $result['total'] : 0;

    // Mahasiswa aktif
    $aktifQuery = "SELECT COUNT(*) as total FROM mahasiswa WHERE status = 'Aktif'";
    $result = executeQuerySingle($aktifQuery);
    $stats['mahasiswa_aktif'] = $result ? $result['total'] : 0;

    // Mahasiswa lulus
    $lulusQuery = "SELECT COUNT(*) as total FROM mahasiswa WHERE status = 'Lulus'";
    $result = executeQuerySingle($lulusQuery);
    $stats['mahasiswa_lulus'] = $result ? $result['total'] : 0;

    // Mahasiswa cuti
    $cutiQuery = "SELECT COUNT(*) as total FROM mahasiswa WHERE status = 'Cuti'";
    $result = executeQuerySingle($cutiQuery);
    $stats['mahasiswa_cuti'] = $result ? $result['total'] : 0;

    // Recent mahasiswa
    $recentQuery = "SELECT * FROM mahasiswa ORDER BY created_at DESC LIMIT 5";
    $recentMahasiswa = executeQuery($recentQuery);

    // Mahasiswa by faculty
    $facultyQuery = "SELECT fakultas, COUNT(*) as total FROM mahasiswa GROUP BY fakultas ORDER BY total DESC";
    $facultyStats = executeQuery($facultyQuery);
} catch (Exception $e) {
    setFlashMessage('error', 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage());
}

// Include header
include 'partials/header.php';
?>

<div class="page-heading">
    <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
    <p>Selamat datang, <?php echo escapeHTML($_SESSION['user_name']); ?>!</p>
</div>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['total_mahasiswa']; ?></h3>
            <p>Total Mahasiswa</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(46, 196, 182, 0.1);">
            <i class="fas fa-user-check" style="color: var(--success);"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['mahasiswa_aktif']; ?></h3>
            <p>Mahasiswa Aktif</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(239, 71, 111, 0.1);">
            <i class="fas fa-user-graduate" style="color: var(--danger);"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['mahasiswa_lulus']; ?></h3>
            <p>Mahasiswa Lulus</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(255, 159, 28, 0.1);">
            <i class="fas fa-user-clock" style="color: var(--warning);"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['mahasiswa_cuti']; ?></h3>
            <p>Mahasiswa Cuti</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Mahasiswa -->
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Mahasiswa Terbaru</h2>
                <a href="manage_mahasiswa.php" class="btn btn-sm btn-outline-primary">
                    Lihat Semua
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Prodi</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentMahasiswa)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">Tidak ada data mahasiswa</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentMahasiswa as $mhs): ?>
                                    <tr>
                                        <td><?php echo escapeHTML($mhs['nim']); ?></td>
                                        <td><?php echo escapeHTML($mhs['nama']); ?></td>
                                        <td><?php echo escapeHTML($mhs['jurusan']); ?></td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            switch ($mhs['status']) {
                                                case 'Aktif':
                                                    $statusClass = 'badge-success';
                                                    break;
                                                case 'Lulus':
                                                    $statusClass = 'badge-primary';
                                                    break;
                                                case 'Cuti':
                                                    $statusClass = 'badge-warning';
                                                    break;
                                                case 'Drop Out':
                                                    $statusClass = 'badge-danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>">
                                                <?php echo escapeHTML($mhs['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Faculty Distribution -->
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Distribusi Jurusan</h2>
            </div>
            <div class="card-body">
                <?php if (empty($facultyStats)): ?>
                    <p class="text-center">Tidak ada data jurusan</p>
                <?php else: ?>
                    <div class="chart-container" data-chart-type="pie">
                        <canvas id="facultyChart" height="250"></canvas>
                    </div>
                    <div class="faculty-list mt-3">
                        <?php foreach ($facultyStats as $faculty): ?>
                            <div class="faculty-item">
                                <div class="faculty-name">
                                    <?php echo escapeHTML($faculty['fakultas']); ?>
                                </div>
                                <div class="faculty-count">
                                    <span class="badge badge-primary">
                                        <?php echo $faculty['total']; ?> mahasiswa
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    /* Dashboard specific styles */
    .page-heading {
        margin-bottom: 1.5rem;
    }

    .page-heading h1 {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.75rem;
        margin-bottom: 0.5rem;
    }

    .page-heading h1 i {
        color: var(--primary);
    }

    .page-heading p {
        color: var(--gray-600);
    }

    .row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -0.75rem;
    }

    .col-md-7 {
        flex: 0 0 calc(58.333% - 1.5rem);
        max-width: calc(58.333% - 1.5rem);
        padding: 0 0.75rem;
    }

    .col-md-5 {
        flex: 0 0 calc(41.667% - 1.5rem);
        max-width: calc(41.667% - 1.5rem);
        padding: 0 0.75rem;
    }

    .faculty-list {
        margin-top: 1rem;
    }

    .faculty-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--gray-200);
    }

    .faculty-item:last-child {
        border-bottom: none;
    }

    @media (max-width: 768px) {
        .row {
            flex-direction: column;
        }

        .col-md-7,
        .col-md-5 {
            flex: 0 0 100%;
            max-width: 100%;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Faculty distribution chart
        const facultyChart = document.getElementById('facultyChart');

        if (facultyChart) {
            const facultyData = <?php echo json_encode($facultyStats ?? []); ?>;

            // This is a placeholder for chart implementation
            // You would need to include a charting library like Chart.js
            console.log('Faculty data for chart:', facultyData);

            // Example with Chart.js (you would need to include the library)
            /*
            new Chart(facultyChart, {
                type: 'pie',
                data: {
                    labels: facultyData.map(item => item.fakultas),
                    datasets: [{
                        data: facultyData.map(item => item.total),
                        backgroundColor: [
                            '#4361ee', '#3a86ff', '#4cc9f0', '#4895ef', 
                            '#3f37c9', '#ff9f1c', '#2ec4b6', '#ef476f'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
            */
        }
    });
</script>

<?php
// Include footer
include 'partials/footer.php';
displayFlashMessage();
?>