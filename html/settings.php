<?php

/**
 * Settings Page
 * Handles application settings and user management
 */

// Include necessary files
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Require admin role
requireRole(['admin']);

// Get action
$action = $_GET['action'] ?? 'general';
$userId = $_GET['id'] ?? null;

// Initialize variables
$user = null;
$userList = [];
$errors = [];
$success = false;

// Process form submissions
if (isFormSubmitted()) {
    // Check CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Token keamanan tidak valid. Silakan coba lagi.');
        redirect('settings.php?action=' . $action);
    }

    switch ($action) {
        case 'password':
            // Change password
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validate input
            if (empty($currentPassword)) {
                $errors[] = 'Password saat ini harus diisi';
            }

            if (empty($newPassword)) {
                $errors[] = 'Password baru harus diisi';
            } elseif (strlen($newPassword) < 6) {
                $errors[] = 'Password baru minimal 6 karakter';
            }

            if ($newPassword !== $confirmPassword) {
                $errors[] = 'Konfirmasi password tidak sesuai';
            }

            if (empty($errors)) {
                // Change password
                $result = changePassword($_SESSION['user_id'], $currentPassword, $newPassword);

                if ($result === true) {
                    setFlashMessage('success', 'Password berhasil diubah');
                    redirect('settings.php?action=password');
                } else {
                    $errors[] = $result;
                }
            }
            break;

        case 'add_user':
            // Add new user
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $name = $_POST['name'] ?? '';
            $role = $_POST['role'] ?? '';

            // Validate input
            if (empty($username)) {
                $errors[] = 'Username harus diisi';
            } else {
                // Check if username already exists
                $checkQuery = "SELECT COUNT(*) as count FROM users WHERE username = :username";
                $result = executeQuerySingle($checkQuery, ['username' => $username]);

                if ($result && $result['count'] > 0) {
                    $errors[] = 'Username sudah digunakan';
                }
            }

            if (empty($password)) {
                $errors[] = 'Password harus diisi';
            } elseif (strlen($password) < 6) {
                $errors[] = 'Password minimal 6 karakter';
            }

            if (empty($name)) {
                $errors[] = 'Nama harus diisi';
            }

            if (empty($role) || !in_array($role, ['admin', 'staff'])) {
                $errors[] = 'Role tidak valid';
            }

            if (empty($errors)) {
                // Insert new user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $insertQuery = "INSERT INTO users (username, password, name, role) VALUES (:username, :password, :name, :role)";
                $success = executeNonQuery($insertQuery, [
                    'username' => $username,
                    'password' => $hashedPassword,
                    'name' => $name,
                    'role' => $role
                ]);

                if ($success) {
                    setFlashMessage('success', 'Pengguna baru berhasil ditambahkan');
                    redirect('settings.php?action=users');
                } else {
                    $errors[] = 'Gagal menambahkan pengguna baru';
                }
            }
            break;

        case 'edit_user':
            // Edit user
            $name = $_POST['name'] ?? '';
            $role = $_POST['role'] ?? '';
            $password = $_POST['password'] ?? '';

            // Validate input
            if (empty($name)) {
                $errors[] = 'Nama harus diisi';
            }

            if (empty($role) || !in_array($role, ['admin', 'staff'])) {
                $errors[] = 'Role tidak valid';
            }

            if (!empty($password) && strlen($password) < 6) {
                $errors[] = 'Password minimal 6 karakter';
            }

            if (empty($errors)) {
                if (!empty($password)) {
                    // Update user with new password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateQuery = "UPDATE users SET password = :password, name = :name, role = :role WHERE id = :id";
                    $params = [
                        'password' => $hashedPassword,
                        'name' => $name,
                        'role' => $role,
                        'id' => $userId
                    ];
                } else {
                    // Update user without changing password
                    $updateQuery = "UPDATE users SET name = :name, role = :role WHERE id = :id";
                    $params = [
                        'name' => $name,
                        'role' => $role,
                        'id' => $userId
                    ];
                }

                $success = executeNonQuery($updateQuery, $params);

                if ($success) {
                    setFlashMessage('success', 'Data pengguna berhasil diperbarui');
                    redirect('settings.php?action=users');
                } else {
                    $errors[] = 'Gagal memperbarui data pengguna';
                }
            }
            break;

        case 'delete_user':
            // Delete user
            $deleteQuery = "DELETE FROM users WHERE id = :id AND id != :current_id";
            $success = executeNonQuery($deleteQuery, [
                'id' => $userId,
                'current_id' => $_SESSION['user_id']
            ]);

            if ($success) {
                setFlashMessage('success', 'Pengguna berhasil dihapus');
                redirect('settings.php?action=users');
            } else {
                setFlashMessage('error', 'Gagal menghapus pengguna');
                redirect('settings.php?action=users');
            }
            break;
    }
}

// Get data based on action
if ($action === 'users') {
    // Get user list
    $query = "SELECT * FROM users ORDER BY username ASC";
    $userList = executeQuery($query);
} elseif (($action === 'edit_user' || $action === 'delete_user') && $userId) {
    // Get user by ID
    $query = "SELECT * FROM users WHERE id = :id";
    $user = executeQuerySingle($query, ['id' => $userId]);

    if (!$user) {
        setFlashMessage('error', 'Pengguna tidak ditemukan');
        redirect('settings.php?action=users');
    }
}

// Include header
include 'partials/header.php';
?>

<div class="page-heading d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-cog"></i> Pengaturan</h1>
        <p>Kelola pengaturan aplikasi dan pengguna</p>
    </div>
</div>

<div class="row">
    <!-- Settings Sidebar -->
    <div class="col-md-3">
        <div class="card settings-nav">
            <div class="list-group">
                <a href="settings.php?action=password"
                    class="list-group-item <?php echo $action === 'password' ? 'active' : ''; ?>">
                    <i class="fas fa-key"></i> Ubah Password
                </a>
                <a href="settings.php?action=users"
                    class="list-group-item <?php echo in_array($action, ['users', 'add_user', 'edit_user']) ? 'active' : ''; ?>">
                    <i class="fas fa-users-cog"></i> Kelola Pengguna
                </a>
            </div>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="col-md-9">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo escapeHTML($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <p class="mb-0">Data berhasil disimpan</p>
            </div>
        <?php endif; ?>

        <?php if ($action === 'password'): ?>
            <!-- Change Password Form -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Ubah Password</h2>
                </div>

                <div class="card-body">
                    <form method="POST" action="settings.php?action=password">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <div class="form-group">
                            <label for="current_password" class="form-label">Password Saat Ini</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-lock"></i></span>
                                <input type="password" id="current_password" name="current_password" class="form-control"
                                    required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="new_password" class="form-label">Password Baru</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-lock"></i></span>
                                <input type="password" id="new_password" name="new_password" class="form-control" required
                                    minlength="6">
                            </div>
                            <div class="form-text">Password minimal 6 karakter</div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-lock"></i></span>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                                    required minlength="6">
                            </div>
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php elseif ($action === 'users'): ?>
            <!-- User Management -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Kelola Pengguna</h2>
                    <div class="card-tools">
                        <a href="settings.php?action=add_user" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Tambah Pengguna
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Username</th>
                                    <th>Nama</th>
                                    <th>Role</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($userList)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada data pengguna</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($userList as $i => $user): ?>
                                        <tr>
                                            <td><?php echo $i + 1; ?></td>
                                            <td><?php echo escapeHTML($user['username']); ?></td>
                                            <td><?php echo escapeHTML($user['name']); ?></td>
                                            <td>
                                                <span
                                                    class="badge <?php echo $user['role'] === 'admin' ? 'badge-danger' : 'badge-primary'; ?>">
                                                    <?php echo ucfirst(escapeHTML($user['role'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="settings.php?action=edit_user&id=<?php echo $user['id']; ?>"
                                                        class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <a href="settings.php?action=delete_user&id=<?php echo $user['id']; ?>"
                                                            class="btn btn-sm btn-danger confirm-action"
                                                            data-confirm="Apakah Anda yakin ingin menghapus pengguna ini?"
                                                            title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
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
        <?php elseif ($action === 'add_user'): ?>
            <!-- Add User Form -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Tambah Pengguna Baru</h2>
                </div>

                <div class="card-body">
                    <form method="POST" action="settings.php?action=add_user">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <div class="form-group">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-user"></i></span>
                                <input type="text" id="username" name="username" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-lock"></i></span>
                                <input type="password" id="password" name="password" class="form-control" required
                                    minlength="6">
                            </div>
                            <div class="form-text">Password minimal 6 karakter</div>
                        </div>

                        <div class="form-group">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-id-card"></i></span>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="role" class="form-label">Role</label>
                            <select id="role" name="role" class="form-select" required>
                                <option value="staff">Staff</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <a href="settings.php?action=users" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php elseif ($action === 'edit_user' && $user): ?>
            <!-- Edit User Form -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Edit Pengguna</h2>
                </div>

                <div class="card-body">
                    <form method="POST" action="settings.php?action=edit_user&id=<?php echo $user['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <div class="form-group">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-user"></i></span>
                                <input type="text" id="username" class="form-control"
                                    value="<?php echo escapeHTML($user['username']); ?>" readonly disabled>
                            </div>
                            <div class="form-text">Username tidak dapat diubah</div>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-lock"></i></span>
                                <input type="password" id="password" name="password" class="form-control" minlength="6">
                            </div>
                            <div class="form-text">Kosongkan jika tidak ingin mengubah password</div>
                        </div>

                        <div class="form-group">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-id-card"></i></span>
                                <input type="text" id="name" name="name" class="form-control" required
                                    value="<?php echo escapeHTML($user['name']); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="role" class="form-label">Role</label>
                            <select id="role" name="role" class="form-select" required>
                                <option value="staff" <?php echo $user['role'] === 'staff' ? 'selected' : ''; ?>>Staff
                                </option>
                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin
                                </option>
                            </select>
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <a href="settings.php?action=users" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Settings page specific styles */
    .settings-nav .list-group-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        border: none;
        border-radius: 0;
        margin-bottom: 0;
        color: var(--gray-700);
        transition: var(--transition);
    }

    .settings-nav .list-group-item:first-child {
        border-top-right-radius: var(--border-radius);
        border-top-left-radius: var(--border-radius);
    }

    .settings-nav .list-group-item:last-child {
        border-bottom-right-radius: var(--border-radius);
        border-bottom-left-radius: var(--border-radius);
    }

    .settings-nav .list-group-item.active {
        background-color: var(--primary);
        color: white;
    }

    .settings-nav .list-group-item:not(.active):hover {
        background-color: var(--gray-100);
        color: var(--primary);
    }

    .settings-nav .list-group-item i {
        width: 1.25rem;
        text-align: center;
    }

    .form-text {
        font-size: 0.875rem;
        color: var(--gray-600);
        margin-top: 0.25rem;
    }

    @media (max-width: 768px) {
        .row {
            flex-direction: column;
        }

        .col-md-3,
        .col-md-9 {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .col-md-3 {
            margin-bottom: 1.5rem;
        }
    }
</style>

<?php
// Include footer
include 'partials/footer.php';
displayFlashMessage();
?>