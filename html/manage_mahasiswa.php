<?php

/**
 * Manage Mahasiswa Page
 * Handles CRUD operations for mahasiswa data
 */

// Include necessary files
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Require user to be logged in
requireLogin();

// Check action
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Initialize variables
$mahasiswa = null;
$mahasiswaList = [];
$errors = [];

// Process form submissions
if (isFormSubmitted()) {
    // Check CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Token keamanan tidak valid. Silakan coba lagi.');
        redirect('manage_mahasiswa.php');
    }

    // Get form data
    $formData = [
        'nim' => $_POST['nim'] ?? '',
        'nama' => $_POST['nama'] ?? '',
        'jenis_kelamin' => $_POST['jenis_kelamin'] ?? '',
        'tanggal_lahir' => $_POST['tanggal_lahir'] ?? '',
        'alamat' => $_POST['alamat'] ?? '',
        'jurusan' => $_POST['jurusan'] ?? '',
        'fakultas' => $_POST['fakultas'] ?? '',
        'ipk' => $_POST['ipk'] ?? null,
        'angkatan' => $_POST['angkatan'] ?? '',
        'status' => $_POST['status'] ?? ''
    ];

    // Validate form data
    if (empty($formData['nim'])) {
        $errors[] = 'NIM harus diisi';
    }

    if (empty($formData['nama'])) {
        $errors[] = 'Nama harus diisi';
    }

    if (empty($formData['jenis_kelamin'])) {
        $errors[] = 'Jenis kelamin harus dipilih';
    }

    if (empty($formData['tanggal_lahir'])) {
        $errors[] = 'Tanggal lahir harus diisi';
    }

    if (empty($formData['alamat'])) {
        $errors[] = 'Alamat harus diisi';
    }

    if (empty($formData['jurusan'])) {
        $errors[] = 'Jurusan harus diisi';
    }

    if (empty($formData['fakultas'])) {
        $errors[] = 'Fakultas harus diisi';
    }

    if (!empty($formData['ipk']) && (!is_numeric($formData['ipk']) || $formData['ipk'] < 0 || $formData['ipk'] > 4.0)) {
        $errors[] = 'IPK harus berupa angka antara 0.0 dan 4.0';
    }

    if (empty($formData['angkatan'])) {
        $errors[] = 'Angkatan harus diisi';
    } elseif (!is_numeric($formData['angkatan'])) {
        $errors[] = 'Angkatan harus berupa angka';
    }

    if (empty($formData['status'])) {
        $errors[] = 'Status harus dipilih';
    }

    // If no errors, process the action
    if (empty($errors)) {
        // Create or update mahasiswa
        if ($action === 'add') {
            // Check if NIM already exists
            $checkQuery = "SELECT COUNT(*) as count FROM mahasiswa WHERE nim = :nim";
            $result = executeQuerySingle($checkQuery, ['nim' => $formData['nim']]);

            if ($result && $result['count'] > 0) {
                $errors[] = 'NIM sudah digunakan';
            } else {
                // Insert new mahasiswa
                $insertQuery = "INSERT INTO mahasiswa (nim, nama, jenis_kelamin, tanggal_lahir, alamat, jurusan, fakultas, ipk, angkatan, status) 
                                VALUES (:nim, :nama, :jenis_kelamin, :tanggal_lahir, :alamat, :jurusan, :fakultas, :ipk, :angkatan, :status)";
                $success = executeNonQuery($insertQuery, $formData);

                if ($success) {
                    setFlashMessage('success', 'Data mahasiswa berhasil ditambahkan');
                    redirect('manage_mahasiswa.php');
                } else {
                    $errors[] = 'Gagal menambahkan data mahasiswa';
                }
            }
        } elseif ($action === 'edit' && $id) {
            // Check if NIM already exists for different mahasiswa
            $checkQuery = "SELECT COUNT(*) as count FROM mahasiswa WHERE nim = :nim AND id != :id";
            $result = executeQuerySingle($checkQuery, ['nim' => $formData['nim'], 'id' => $id]);

            if ($result && $result['count'] > 0) {
                $errors[] = 'NIM sudah digunakan oleh mahasiswa lain';
            } else {
                // Update mahasiswa
                $updateQuery = "UPDATE mahasiswa SET nim = :nim, nama = :nama, jenis_kelamin = :jenis_kelamin, 
                                tanggal_lahir = :tanggal_lahir, alamat = :alamat, jurusan = :jurusan, 
                                fakultas = :fakultas, ipk = :ipk, angkatan = :angkatan, status = :status 
                                WHERE id = :id";
                $params = array_merge($formData, ['id' => $id]);
                $success = executeNonQuery($updateQuery, $params);

                if ($success) {
                    setFlashMessage('success', 'Data mahasiswa berhasil diperbarui');
                    redirect('manage_mahasiswa.php');
                } else {
                    $errors[] = 'Gagal memperbarui data mahasiswa';
                }
            }
        }
    }
} elseif ($action === 'delete' && $id) {
    // Delete mahasiswa
    $deleteQuery = "DELETE FROM mahasiswa WHERE id = :id";
    $success = executeNonQuery($deleteQuery, ['id' => $id]);

    if ($success) {
        setFlashMessage('success', 'Data mahasiswa berhasil dihapus');
        redirect('manage_mahasiswa.php');
    } else {
        setFlashMessage('error', 'Gagal menghapus data mahasiswa');
        redirect('manage_mahasiswa.php');
    }
}

// Get mahasiswa data based on action
if ($action === 'edit' || $action === 'view') {
    $query = "SELECT * FROM mahasiswa WHERE id = :id";
    $mahasiswa = executeQuerySingle($query, ['id' => $id]);

    if (!$mahasiswa) {
        setFlashMessage('error', 'Data mahasiswa tidak ditemukan');
        redirect('manage_mahasiswa.php');
    }
} elseif ($action === 'list') {
    // Get search parameters
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $fakultas = $_GET['fakultas'] ?? '';

    // Build query
    $query = "SELECT * FROM mahasiswa WHERE 1=1";
    $params = [];

    // Add search filter
    if (!empty($search)) {
        $query .= " AND (nim LIKE :search OR nama LIKE :search OR jurusan LIKE :search)";
        $params['search'] = "%$search%";
    }

    // Add status filter
    if (!empty($status)) {
        $query .= " AND status = :status";
        $params['status'] = $status;
    }

    // Add fakultas filter
    if (!empty($fakultas)) {
        $query .= " AND fakultas = :fakultas";
        $params['fakultas'] = $fakultas;
    }

    // Add order by
    $query .= " ORDER BY nim ASC";

    // Execute query
    $mahasiswaList = executeQuery($query, $params);

    // Get fakultas list for filter
    $fakultasQuery = "SELECT DISTINCT fakultas FROM mahasiswa ORDER BY fakultas";
    $fakultasList = executeQuery($fakultasQuery);
}

// Include header
include 'partials/header.php';
?>

<div class="page-heading d-flex justify-content-between align-items-center">
    <div>
        <h1>
            <?php if ($action === 'add'): ?>
                <i class="fas fa-plus-circle"></i> Tambah Mahasiswa
            <?php elseif ($action === 'edit'): ?>
                <i class="fas fa-edit"></i> Edit Mahasiswa
            <?php elseif ($action === 'view'): ?>
                <i class="fas fa-eye"></i> Detail Mahasiswa
            <?php else: ?>
                <i class="fas fa-user-graduate"></i> Data Mahasiswa
            <?php endif; ?>
        </h1>
    </div>

    <?php if ($action === 'list'): ?>
        <div>
            <a href="manage_mahasiswa.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Mahasiswa
            </a>
        </div>
    <?php else: ?>
        <div>
            <a href="manage_mahasiswa.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo escapeHTML($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
    <!-- List Mahasiswa -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Daftar Mahasiswa</h2>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-outline-primary" id="toggle-filter">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
        </div>

        <div class="card-body">
            <!-- Filter -->
            <div class="filter-container" id="filter-container"
                style="display: <?php echo (!empty($search) || !empty($status) || !empty($fakultas)) ? 'block' : 'none'; ?>">
                <form method="GET" action="manage_mahasiswa.php" class="filter-form">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="search" class="form-label">Pencarian</label>
                                <input type="text" id="search" name="search" class="form-control"
                                    placeholder="Cari NIM, nama, atau jurusan" value="<?php echo escapeHTML($search); ?>">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-select">
                                    <option value="">-- Semua Status --</option>
                                    <option value="Aktif" <?php echo $status === 'Aktif' ? 'selected' : ''; ?>>Aktif
                                    </option>
                                    <option value="Cuti" <?php echo $status === 'Cuti' ? 'selected' : ''; ?>>Cuti</option>
                                    <option value="Lulus" <?php echo $status === 'Lulus' ? 'selected' : ''; ?>>Lulus
                                    </option>
                                    <option value="Drop Out" <?php echo $status === 'Drop Out' ? 'selected' : ''; ?>>Drop
                                        Out</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fakultas" class="form-label">Jurusan</label>
                                <select id="fakultas" name="fakultas" class="form-select">
                                    <option value="">-- Semua Jurusan --</option>
                                    <?php foreach ($fakultasList as $f): ?>
                                        <option value="<?php echo escapeHTML($f['fakultas']); ?>"
                                            <?php echo $fakultas === $f['fakultas'] ? 'selected' : ''; ?>>
                                            <?php echo escapeHTML($f['fakultas']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                    <a href="manage_mahasiswa.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Mahasiswa Table -->
            <div class="table-responsive">
                <table class="table data-table" id="mahasiswa-table">
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Prodi</th>
                            <th>Jurusan</th>
                            <th>Angkatan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($mahasiswaList)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data mahasiswa</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($mahasiswaList as $mhs): ?>
                                <tr>
                                    <td data-column="nim"><?php echo escapeHTML($mhs['nim']); ?></td>
                                    <td data-column="nama"><?php echo escapeHTML($mhs['nama']); ?></td>
                                    <td data-column="jurusan"><?php echo escapeHTML($mhs['jurusan']); ?></td>
                                    <td data-column="fakultas"><?php echo escapeHTML($mhs['fakultas']); ?></td>
                                    <td data-column="angkatan"><?php echo escapeHTML($mhs['angkatan']); ?></td>
                                    <td data-column="status">
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
                                    <td>
                                        <div class="btn-group">
                                            <a href="manage_mahasiswa.php?action=view&id=<?php echo $mhs['id']; ?>"
                                                class="btn btn-sm btn-info" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="manage_mahasiswa.php?action=edit&id=<?php echo $mhs['id']; ?>"
                                                class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="manage_mahasiswa.php?action=delete&id=<?php echo $mhs['id']; ?>"
                                                class="btn btn-sm btn-danger confirm-action"
                                                data-confirm="Apakah Anda yakin ingin menghapus mahasiswa ini?" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php elseif ($action === 'view' && $mahasiswa): ?>
    <!-- View Mahasiswa Detail -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Detail Mahasiswa</h2>
        </div>

        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">NIM</div>
                    <div class="detail-value"><?php echo escapeHTML($mahasiswa['nim']); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Nama</div>
                    <div class="detail-value"><?php echo escapeHTML($mahasiswa['nama']); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Jenis Kelamin</div>
                    <div class="detail-value"><?php echo escapeHTML($mahasiswa['jenis_kelamin']); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Tanggal Lahir</div>
                    <div class="detail-value"><?php echo formatDateID($mahasiswa['tanggal_lahir']); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Alamat</div>
                    <div class="detail-value"><?php echo escapeHTML($mahasiswa['alamat']); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Prodi</div>
                    <div class="detail-value"><?php echo escapeHTML($mahasiswa['jurusan']); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Jurusan</div>
                    <div class="detail-value"><?php echo escapeHTML($mahasiswa['fakultas']); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">IPK</div>
                    <div class="detail-value">
                        <?php echo $mahasiswa['ipk'] ? escapeHTML(number_format($mahasiswa['ipk'], 2)) : '-'; ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Angkatan</div>
                    <div class="detail-value"><?php echo escapeHTML($mahasiswa['angkatan']); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">
                        <?php
                        $statusClass = '';
                        switch ($mahasiswa['status']) {
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
                            <?php echo escapeHTML($mahasiswa['status']); ?>
                        </span>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Terdaftar Pada</div>
                    <div class="detail-value"><?php echo date('d M Y H:i', strtotime($mahasiswa['created_at'])); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Terakhir Diperbarui</div>
                    <div class="detail-value"><?php echo date('d M Y H:i', strtotime($mahasiswa['updated_at'])); ?></div>
                </div>
            </div>

            <div class="mt-3 text-center">
                <a href="manage_mahasiswa.php?action=edit&id=<?php echo $mahasiswa['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="manage_mahasiswa.php?action=delete&id=<?php echo $mahasiswa['id']; ?>"
                    class="btn btn-danger confirm-action" data-confirm="Apakah Anda yakin ingin menghapus mahasiswa ini?">
                    <i class="fas fa-trash"></i> Hapus
                </a>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Add/Edit Mahasiswa Form -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <?php echo $action === 'add' ? 'Tambah Mahasiswa Baru' : 'Edit Data Mahasiswa'; ?>
            </h2>
        </div>

        <div class="card-body">
            <form method="POST" action="<?php echo escapeHTML($_SERVER['REQUEST_URI']); ?>" class="needs-validation">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nim" class="form-label">NIM *</label>
                            <input type="text" id="nim" name="nim" class="form-control" required
                                value="<?php echo $action === 'edit' ? escapeHTML($mahasiswa['nim']) : ''; ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama" class="form-label">Nama Lengkap *</label>
                            <input type="text" id="nama" name="nama" class="form-control" required
                                value="<?php echo $action === 'edit' ? escapeHTML($mahasiswa['nama']) : ''; ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Jenis Kelamin *</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" id="jk-laki" name="jenis_kelamin" value="Laki-laki"
                                        class="form-check-input" required
                                        <?php echo ($action === 'edit' && $mahasiswa['jenis_kelamin'] === 'Laki-laki') ? 'checked' : ''; ?>>
                                    <label for="jk-laki" class="form-check-label">Laki-laki</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" id="jk-perempuan" name="jenis_kelamin" value="Perempuan"
                                        class="form-check-input" required
                                        <?php echo ($action === 'edit' && $mahasiswa['jenis_kelamin'] === 'Perempuan') ? 'checked' : ''; ?>>
                                    <label for="jk-perempuan" class="form-check-label">Perempuan</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal_lahir" class="form-label">Tanggal Lahir *</label>
                            <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-control" required
                                value="<?php echo $action === 'edit' ? escapeHTML($mahasiswa['tanggal_lahir']) : ''; ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="alamat" class="form-label">Alamat *</label>
                    <textarea id="alamat" name="alamat" class="form-control" rows="3"
                        required><?php echo $action === 'edit' ? escapeHTML($mahasiswa['alamat']) : ''; ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="jurusan" class="form-label">Prodi *</label>
                            <input type="text" id="jurusan" name="jurusan" class="form-control" required
                                value="<?php echo $action === 'edit' ? escapeHTML($mahasiswa['jurusan']) : ''; ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fakultas" class="form-label">Jurusan *</label>
                            <input type="text" id="fakultas" name="fakultas" class="form-control" required
                                value="<?php echo $action === 'edit' ? escapeHTML($mahasiswa['fakultas']) : ''; ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="ipk" class="form-label">IPK</label>
                            <input type="number" id="ipk" name="ipk" class="form-control" min="0" max="4" step="0.01"
                                value="<?php echo $action === 'edit' && $mahasiswa['ipk'] ? escapeHTML($mahasiswa['ipk']) : ''; ?>">
                            <div class="form-text">Masukkan nilai IPK (opsional, antara 0.00 - 4.00)</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="angkatan" class="form-label">Angkatan *</label>
                            <input type="number" id="angkatan" name="angkatan" class="form-control" required
                                value="<?php echo $action === 'edit' ? escapeHTML($mahasiswa['angkatan']) : date('Y'); ?>">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="status" class="form-label">Status *</label>
                            <select id="status" name="status" class="form-select" required>
                                <option value="Aktif"
                                    <?php echo ($action === 'edit' && $mahasiswa['status'] === 'Aktif') ? 'selected' : ''; ?>>
                                    Aktif</option>
                                <option value="Cuti"
                                    <?php echo ($action === 'edit' && $mahasiswa['status'] === 'Cuti') ? 'selected' : ''; ?>>
                                    Cuti</option>
                                <option value="Lulus"
                                    <?php echo ($action === 'edit' && $mahasiswa['status'] === 'Lulus') ? 'selected' : ''; ?>>
                                    Lulus</option>
                                <option value="Drop Out"
                                    <?php echo ($action === 'edit' && $mahasiswa['status'] === 'Drop Out') ? 'selected' : ''; ?>>
                                    Drop Out</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-4 text-center">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $action === 'add' ? 'Simpan' : 'Perbarui'; ?>
                    </button>
                    <a href="manage_mahasiswa.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<style>
    /* Mahasiswa page specific styles */
    .filter-container {
        background-color: var(--gray-100);
        padding: 1rem;
        border-radius: var(--border-radius);
        margin-bottom: 1.5rem;
    }

    .filter-form .row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -0.5rem;
    }

    .filter-form .col-md-4,
    .filter-form .col-md-3,
    .filter-form .col-md-2 {
        padding: 0 0.5rem;
    }

    .filter-form .col-md-4 {
        flex: 0 0 33.333%;
        max-width: 33.333%;
    }

    .filter-form .col-md-3 {
        flex: 0 0 25%;
        max-width: 25%;
    }

    .filter-form .col-md-2 {
        flex: 0 0 16.666%;
        max-width: 16.666%;
    }

    .btn-group {
        display: flex;
        gap: 0.25rem;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .detail-item {
        border-bottom: 1px solid var(--gray-200);
        padding-bottom: 0.75rem;
    }

    .detail-label {
        font-weight: 600;
        color: var(--gray-700);
        margin-bottom: 0.25rem;
    }

    .detail-value {
        color: var(--gray-900);
    }

    .row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -0.75rem;
    }

    .col-md-6 {
        flex: 0 0 50%;
        max-width: 50%;
        padding: 0 0.75rem;
    }

    .col-md-4 {
        flex: 0 0 33.333%;
        max-width: 33.333%;
        padding: 0 0.75rem;
    }

    @media (max-width: 768px) {
        .row {
            flex-direction: column;
        }

        .col-md-6,
        .col-md-4 {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .filter-form .row {
            flex-direction: column;
        }

        .filter-form .col-md-4,
        .filter-form .col-md-3,
        .filter-form .col-md-2 {
            flex: 0 0 100%;
            max-width: 100%;
            margin-bottom: 0.5rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle filter
        const toggleFilter = document.getElementById('toggle-filter');
        const filterContainer = document.getElementById('filter-container');

        if (toggleFilter && filterContainer) {
            toggleFilter.addEventListener('click', function() {
                filterContainer.style.display = filterContainer.style.display === 'none' ? 'block' : 'none';
            });
        }
    });
</script>

<?php
// Include footer
include 'partials/footer.php';
displayFlashMessage();
?>