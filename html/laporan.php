<?php

/**
 * Laporan Page
 * Displays reports and statistics
 */

// Include necessary files
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Require user to be logged in
requireLogin();

// Get report type
$reportType = $_GET['type'] ?? 'status';

// Initialize variables
$chartData = [];
$tableData = [];
$title = '';

// Generate reports based on type
try {
    switch ($reportType) {
        case 'status':
            $title = 'Laporan Status Mahasiswa';

            // Get status statistics
            $query = "SELECT status, COUNT(*) as total FROM mahasiswa GROUP BY status ORDER BY total DESC";
            $tableData = executeQuery($query);

            // Prepare chart data
            $colors = ['#2ec4b6', '#3a86ff', '#ff9f1c', '#ef476f'];
            $chartData = [
                'labels' => array_column($tableData, 'status'),
                'datasets' => [
                    [
                        'data' => array_column($tableData, 'total'),
                        'backgroundColor' => array_slice($colors, 0, count($tableData))
                    ]
                ]
            ];
            break;

        case 'fakultas':
            $title = 'Laporan Mahasiswa per Jurusan';

            // Get fakultas statistics
            $query = "SELECT fakultas, COUNT(*) as total FROM mahasiswa GROUP BY fakultas ORDER BY total DESC";
            $tableData = executeQuery($query);

            // Prepare chart data
            $chartData = [
                'labels' => array_column($tableData, 'fakultas'),
                'datasets' => [
                    [
                        'data' => array_column($tableData, 'total'),
                        'backgroundColor' => [
                            '#4361ee',
                            '#3a86ff',
                            '#4cc9f0',
                            '#4895ef',
                            '#3f37c9',
                            '#ff9f1c',
                            '#2ec4b6',
                            '#ef476f'
                        ]
                    ]
                ]
            ];
            break;

        case 'jurusan':
            $title = 'Laporan Mahasiswa per Prodi';

            // Get jurusan statistics
            $query = "SELECT jurusan, COUNT(*) as total FROM mahasiswa GROUP BY jurusan ORDER BY total DESC";
            $tableData = executeQuery($query);

            // Prepare chart data
            $chartData = [
                'labels' => array_column($tableData, 'jurusan'),
                'datasets' => [
                    [
                        'data' => array_column($tableData, 'total'),
                        'backgroundColor' => [
                            '#4361ee',
                            '#3a86ff',
                            '#4cc9f0',
                            '#4895ef',
                            '#3f37c9',
                            '#ff9f1c',
                            '#2ec4b6',
                            '#ef476f',
                            '#b5179e',
                            '#7209b7',
                            '#480ca8',
                            '#3a0ca3',
                            '#3f37c9',
                            '#4361ee',
                            '#4895ef',
                            '#4cc9f0'
                        ]
                    ]
                ]
            ];
            break;

        case 'angkatan':
            $title = 'Laporan Mahasiswa per Angkatan';

            // Get angkatan statistics
            $query = "SELECT angkatan, COUNT(*) as total FROM mahasiswa GROUP BY angkatan ORDER BY angkatan";
            $tableData = executeQuery($query);

            // Prepare chart data
            $chartData = [
                'labels' => array_column($tableData, 'angkatan'),
                'datasets' => [
                    [
                        'label' => 'Jumlah Mahasiswa',
                        'data' => array_column($tableData, 'total'),
                        'backgroundColor' => '#4361ee'
                    ]
                ]
            ];
            break;

        case 'ipk':
            $title = 'Laporan IPK Mahasiswa';

            // Get IPK statistics
            $query = "SELECT 
                        CASE 
                            WHEN ipk >= 3.5 THEN 'Sangat Baik (>= 3.5)' 
                            WHEN ipk >= 3.0 THEN 'Baik (3.0 - 3.49)' 
                            WHEN ipk >= 2.5 THEN 'Cukup (2.5 - 2.99)' 
                            WHEN ipk >= 2.0 THEN 'Kurang (2.0 - 2.49)' 
                            WHEN ipk IS NOT NULL THEN 'Sangat Kurang (< 2.0)'
                            ELSE 'Belum Ada Nilai'
                        END as kategori, 
                        COUNT(*) as total 
                      FROM mahasiswa 
                      GROUP BY kategori 
                      ORDER BY 
                        CASE kategori
                            WHEN 'Sangat Baik (>= 3.5)' THEN 1
                            WHEN 'Baik (3.0 - 3.49)' THEN 2
                            WHEN 'Cukup (2.5 - 2.99)' THEN 3
                            WHEN 'Kurang (2.0 - 2.49)' THEN 4
                            WHEN 'Sangat Kurang (< 2.0)' THEN 5
                            ELSE 6
                        END";
            $tableData = executeQuery($query);

            // Prepare chart data
            $colors = ['#2ec4b6', '#4361ee', '#4cc9f0', '#ff9f1c', '#ef476f', '#adb5bd'];
            $chartData = [
                'labels' => array_column($tableData, 'kategori'),
                'datasets' => [
                    [
                        'data' => array_column($tableData, 'total'),
                        'backgroundColor' => array_slice($colors, 0, count($tableData))
                    ]
                ]
            ];
            break;

        default:
            $title = 'Laporan Status Mahasiswa';

            // Get status statistics
            $query = "SELECT status, COUNT(*) as total FROM mahasiswa GROUP BY status ORDER BY total DESC";
            $tableData = executeQuery($query);

            // Prepare chart data
            $colors = ['#2ec4b6', '#3a86ff', '#ff9f1c', '#ef476f'];
            $chartData = [
                'labels' => array_column($tableData, 'status'),
                'datasets' => [
                    [
                        'data' => array_column($tableData, 'total'),
                        'backgroundColor' => array_slice($colors, 0, count($tableData))
                    ]
                ]
            ];
            break;
    }
} catch (Exception $e) {
    setFlashMessage('error', 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage());
}

// Include header
include 'partials/header.php';
?>

<div class="page-heading d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-chart-bar"></i> Laporan</h1>
        <p>Ringkasan dan visualisasi data mahasiswa</p>
    </div>

    <div>
        <button class="btn btn-primary" id="print-report">
            <i class="fas fa-print"></i> Cetak Laporan
        </button>
    </div>
</div>

<!-- Report Tabs -->
<div class="tabs">
    <ul class="tabs-nav">
        <li class="<?php echo $reportType === 'status' ? 'active' : ''; ?>">
            <a href="laporan.php?type=status">Status</a>
        </li>
        <li class="<?php echo $reportType === 'fakultas' ? 'active' : ''; ?>">
            <a href="laporan.php?type=fakultas">Jurusan</a>
        </li>
        <li class="<?php echo $reportType === 'jurusan' ? 'active' : ''; ?>">
            <a href="laporan.php?type=jurusan">Prodi</a>
        </li>
        <li class="<?php echo $reportType === 'angkatan' ? 'active' : ''; ?>">
            <a href="laporan.php?type=angkatan">Angkatan</a>
        </li>
        <li class="<?php echo $reportType === 'ipk' ? 'active' : ''; ?>">
            <a href="laporan.php?type=ipk">IPK</a>
        </li>
    </ul>
</div>

<div class="card" id="report-container">
    <div class="card-header">
        <h2 class="card-title"><?php echo escapeHTML($title); ?></h2>
    </div>

    <div class="card-body">
        <div class="report-header">
            <div class="report-logo">
                <i class="fas fa-graduation-cap"></i>
                <span>SIMahasiswa</span>
            </div>
            <div class="report-title">
                <h3><?php echo escapeHTML($title); ?></h3>
                <p>Tanggal: <?php echo date('d M Y'); ?></p>
            </div>
        </div>

        <div class="row">
            <!-- Chart -->
            <div class="col-md-6">
                <div class="chart-container"
                    data-chart-type="<?php echo $reportType === 'angkatan' ? 'bar' : 'pie'; ?>">
                    <canvas id="reportChart" height="300"></canvas>
                </div>
            </div>

            <!-- Table -->
            <div class="col-md-6">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?php echo $reportType === 'status' ? 'Status' : ($reportType === 'fakultas' ? 'Fakultas' : ($reportType === 'jurusan' ? 'Jurusan' : ($reportType === 'angkatan' ? 'Angkatan' : 'Kategori IPK'))); ?>
                                </th>
                                <th>Jumlah</th>
                                <th>Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total = array_sum(array_column($tableData, 'total'));
                            foreach ($tableData as $i => $row):
                                $percentage = $total > 0 ? round(($row['total'] / $total) * 100, 2) : 0;
                            ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo escapeHTML($row[$reportType === 'ipk' ? 'kategori' : $reportType]); ?>
                                    </td>
                                    <td><?php echo $row['total']; ?></td>
                                    <td><?php echo $percentage; ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2">Total</th>
                                <th><?php echo $total; ?></th>
                                <th>100%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Laporan specific styles */
    .tabs {
        margin-bottom: 1.5rem;
    }

    .tabs-nav {
        display: flex;
        list-style: none;
        background-color: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        overflow: hidden;
    }

    .tabs-nav li {
        flex: 1;
    }

    .tabs-nav a {
        display: block;
        padding: 0.75rem 1rem;
        text-align: center;
        color: var(--gray-700);
        border-bottom: 2px solid transparent;
        transition: var(--transition);
    }

    .tabs-nav li.active a {
        color: var(--primary);
        border-bottom-color: var(--primary);
        background-color: rgba(67, 97, 238, 0.05);
    }

    .tabs-nav a:hover {
        background-color: var(--gray-100);
    }

    .report-header {
        display: flex;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--gray-200);
    }

    .report-logo {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--gray-800);
        margin-right: 2rem;
    }

    .report-logo i {
        color: var(--primary);
    }

    .report-title h3 {
        margin-bottom: 0.25rem;
    }

    .report-title p {
        margin: 0;
        color: var(--gray-600);
    }

    .chart-container {
        background-color: white;
        border-radius: var(--border-radius);
        padding: 1rem;
        height: 100%;
    }

    @media print {

        .main-header,
        .sidebar,
        .main-footer,
        .page-heading,
        .tabs {
            display: none !important;
        }

        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }

        .card {
            box-shadow: none !important;
            border: 1px solid var(--gray-300) !important;
        }

        .chart-container {
            page-break-inside: avoid;
        }
    }

    @media (max-width: 768px) {
        .tabs-nav {
            flex-wrap: wrap;
        }

        .tabs-nav li {
            flex: 0 0 50%;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Chart data
        const chartData = <?php echo json_encode($chartData); ?>;
        const chartType = '<?php echo $reportType === 'angkatan' ? 'bar' : 'pie'; ?>';

        // Initialize chart (placeholder for Chart.js implementation)
        const reportChart = document.getElementById('reportChart');

        if (reportChart) {
            console.log('Chart data:', chartData);
            console.log('Chart type:', chartType);

            // Example with Chart.js (you would need to include the library)
            /*
            new Chart(reportChart, {
                type: chartType,
                data: {
                    labels: chartData.labels,
                    datasets: chartData.datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            */
        }

        // Print report
        const printButton = document.getElementById('print-report');

        if (printButton) {
            printButton.addEventListener('click', function() {
                window.print();
            });
        }
    });
</script>

<?php
// Include footer
include 'partials/footer.php';
displayFlashMessage();
?>