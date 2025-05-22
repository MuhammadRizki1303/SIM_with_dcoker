<?php

/**
 * Mahasiswa API
 * RESTful API endpoints for mahasiswa data
 */

// Include necessary files
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Set content type to JSON
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get endpoint
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : 'mahasiswa';

// Initialize response
$response = [
    'status' => 'error',
    'message' => 'Invalid request',
    'data' => null
];

try {
    // Handle different endpoints
    switch ($endpoint) {
        case 'mahasiswa':
            // Get all mahasiswa or specific one
            if ($method === 'GET') {
                // Get specific mahasiswa if ID is provided
                if (isset($_GET['id'])) {
                    $id = (int)$_GET['id'];
                    $query = "SELECT * FROM mahasiswa WHERE id = :id";
                    $result = executeQuerySingle($query, ['id' => $id]);

                    if ($result) {
                        $response = [
                            'status' => 'success',
                            'message' => 'Data mahasiswa retrieved successfully',
                            'data' => $result
                        ];
                    } else {
                        $response = [
                            'status' => 'error',
                            'message' => 'Mahasiswa not found',
                            'data' => null
                        ];
                        http_response_code(404);
                    }
                } else {
                    // Get all mahasiswa with optional filtering
                    $query = "SELECT * FROM mahasiswa WHERE 1=1";
                    $params = [];

                    // Add filters if provided
                    if (isset($_GET['status']) && !empty($_GET['status'])) {
                        $query .= " AND status = :status";
                        $params['status'] = $_GET['status'];
                    }

                    if (isset($_GET['fakultas']) && !empty($_GET['fakultas'])) {
                        $query .= " AND fakultas = :fakultas";
                        $params['fakultas'] = $_GET['fakultas'];
                    }

                    if (isset($_GET['angkatan']) && !empty($_GET['angkatan'])) {
                        $query .= " AND angkatan = :angkatan";
                        $params['angkatan'] = (int)$_GET['angkatan'];
                    }

                    if (isset($_GET['search']) && !empty($_GET['search'])) {
                        $query .= " AND (nim LIKE :search OR nama LIKE :search OR jurusan LIKE :search)";
                        $params['search'] = '%' . $_GET['search'] . '%';
                    }

                    // Add order by
                    $query .= " ORDER BY nim ASC";

                    // Add limit and offset if provided
                    if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
                        $limit = (int)$_GET['limit'];
                        $offset = isset($_GET['offset']) && is_numeric($_GET['offset']) ? (int)$_GET['offset'] : 0;

                        $query .= " LIMIT :limit OFFSET :offset";
                        $params['limit'] = $limit;
                        $params['offset'] = $offset;
                    }

                    $result = executeQuery($query, $params);

                    $response = [
                        'status' => 'success',
                        'message' => 'Data mahasiswa retrieved successfully',
                        'data' => $result
                    ];
                }
            }
            // Create new mahasiswa
            elseif ($method === 'POST') {
                // Check if user has permission to add mahasiswa
                if (!hasPermission(['admin', 'staff'])) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Forbidden']);
                    exit;
                }

                // Get request body
                $data = json_decode(file_get_contents('php://input'), true);

                // Validate required fields
                $requiredFields = ['nim', 'nama', 'jenis_kelamin', 'tanggal_lahir', 'alamat', 'jurusan', 'fakultas', 'angkatan', 'status'];
                $missingFields = [];

                foreach ($requiredFields as $field) {
                    if (!isset($data[$field]) || empty($data[$field])) {
                        $missingFields[] = $field;
                    }
                }

                if (!empty($missingFields)) {
                    $response = [
                        'status' => 'error',
                        'message' => 'Missing required fields: ' . implode(', ', $missingFields),
                        'data' => null
                    ];
                    http_response_code(400);
                } else {
                    // Check if NIM already exists
                    $checkQuery = "SELECT COUNT(*) as count FROM mahasiswa WHERE nim = :nim";
                    $checkResult = executeQuerySingle($checkQuery, ['nim' => $data['nim']]);

                    if ($checkResult && $checkResult['count'] > 0) {
                        $response = [
                            'status' => 'error',
                            'message' => 'NIM already exists',
                            'data' => null
                        ];
                        http_response_code(409);
                    } else {
                        // Prepare data for insertion
                        $insertData = [
                            'nim' => $data['nim'],
                            'nama' => $data['nama'],
                            'jenis_kelamin' => $data['jenis_kelamin'],
                            'tanggal_lahir' => $data['tanggal_lahir'],
                            'alamat' => $data['alamat'],
                            'jurusan' => $data['jurusan'],
                            'fakultas' => $data['fakultas'],
                            'ipk' => isset($data['ipk']) ? $data['ipk'] : null,
                            'angkatan' => $data['angkatan'],
                            'status' => $data['status']
                        ];

                        // Insert mahasiswa
                        $insertQuery = "INSERT INTO mahasiswa (nim, nama, jenis_kelamin, tanggal_lahir, alamat, jurusan, fakultas, ipk, angkatan, status) 
                                       VALUES (:nim, :nama, :jenis_kelamin, :tanggal_lahir, :alamat, :jurusan, :fakultas, :ipk, :angkatan, :status)";
                        $success = executeNonQuery($insertQuery, $insertData);

                        if ($success) {
                            $id = getLastInsertId();
                            $newMahasiswa = executeQuerySingle("SELECT * FROM mahasiswa WHERE id = :id", ['id' => $id]);

                            $response = [
                                'status' => 'success',
                                'message' => 'Mahasiswa added successfully',
                                'data' => $newMahasiswa
                            ];
                            http_response_code(201);
                        } else {
                            $response = [
                                'status' => 'error',
                                'message' => 'Failed to add mahasiswa',
                                'data' => null
                            ];
                            http_response_code(500);
                        }
                    }
                }
            }
            // Update mahasiswa
            elseif ($method === 'PUT') {
                // Check if user has permission to update mahasiswa
                if (!hasPermission(['admin', 'staff'])) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Forbidden']);
                    exit;
                }

                // Check if ID is provided
                if (!isset($_GET['id'])) {
                    $response = [
                        'status' => 'error',
                        'message' => 'Missing mahasiswa ID',
                        'data' => null
                    ];
                    http_response_code(400);
                } else {
                    $id = (int)$_GET['id'];

                    // Check if mahasiswa exists
                    $checkQuery = "SELECT * FROM mahasiswa WHERE id = :id";
                    $mahasiswa = executeQuerySingle($checkQuery, ['id' => $id]);

                    if (!$mahasiswa) {
                        $response = [
                            'status' => 'error',
                            'message' => 'Mahasiswa not found',
                            'data' => null
                        ];
                        http_response_code(404);
                    } else {
                        // Get request body
                        $data = json_decode(file_get_contents('php://input'), true);

                        // Validate required fields
                        $requiredFields = ['nim', 'nama', 'jenis_kelamin', 'tanggal_lahir', 'alamat', 'jurusan', 'fakultas', 'angkatan', 'status'];
                        $missingFields = [];

                        foreach ($requiredFields as $field) {
                            if (!isset($data[$field]) || empty($data[$field])) {
                                $missingFields[] = $field;
                            }
                        }

                        if (!empty($missingFields)) {
                            $response = [
                                'status' => 'error',
                                'message' => 'Missing required fields: ' . implode(', ', $missingFields),
                                'data' => null
                            ];
                            http_response_code(400);
                        } else {
                            // Check if NIM already exists for different mahasiswa
                            if ($data['nim'] !== $mahasiswa['nim']) {
                                $checkNimQuery = "SELECT COUNT(*) as count FROM mahasiswa WHERE nim = :nim AND id != :id";
                                $checkNimResult = executeQuerySingle($checkNimQuery, ['nim' => $data['nim'], 'id' => $id]);

                                if ($checkNimResult && $checkNimResult['count'] > 0) {
                                    $response = [
                                        'status' => 'error',
                                        'message' => 'NIM already exists for another mahasiswa',
                                        'data' => null
                                    ];
                                    http_response_code(409);
                                    echo json_encode($response);
                                    exit;
                                }
                            }

                            // Prepare data for update
                            $updateData = [
                                'nim' => $data['nim'],
                                'nama' => $data['nama'],
                                'jenis_kelamin' => $data['jenis_kelamin'],
                                'tanggal_lahir' => $data['tanggal_lahir'],
                                'alamat' => $data['alamat'],
                                'jurusan' => $data['jurusan'],
                                'fakultas' => $data['fakultas'],
                                'ipk' => isset($data['ipk']) ? $data['ipk'] : null,
                                'angkatan' => $data['angkatan'],
                                'status' => $data['status'],
                                'id' => $id
                            ];

                            // Update mahasiswa
                            $updateQuery = "UPDATE mahasiswa SET nim = :nim, nama = :nama, jenis_kelamin = :jenis_kelamin, 
                                           tanggal_lahir = :tanggal_lahir, alamat = :alamat, jurusan = :jurusan, 
                                           fakultas = :fakultas, ipk = :ipk, angkatan = :angkatan, status = :status 
                                           WHERE id = :id";
                            $success = executeNonQuery($updateQuery, $updateData);

                            if ($success) {
                                $updatedMahasiswa = executeQuerySingle("SELECT * FROM mahasiswa WHERE id = :id", ['id' => $id]);

                                $response = [
                                    'status' => 'success',
                                    'message' => 'Mahasiswa updated successfully',
                                    'data' => $updatedMahasiswa
                                ];
                            } else {
                                $response = [
                                    'status' => 'error',
                                    'message' => 'Failed to update mahasiswa',
                                    'data' => null
                                ];
                                http_response_code(500);
                            }
                        }
                    }
                }
            }
            // Delete mahasiswa
            elseif ($method === 'DELETE') {
                // Check if user has permission to delete mahasiswa
                if (!hasPermission(['admin'])) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Forbidden']);
                    exit;
                }

                // Check if ID is provided
                if (!isset($_GET['id'])) {
                    $response = [
                        'status' => 'error',
                        'message' => 'Missing mahasiswa ID',
                        'data' => null
                    ];
                    http_response_code(400);
                } else {
                    $id = (int)$_GET['id'];

                    // Check if mahasiswa exists
                    $checkQuery = "SELECT * FROM mahasiswa WHERE id = :id";
                    $mahasiswa = executeQuerySingle($checkQuery, ['id' => $id]);

                    if (!$mahasiswa) {
                        $response = [
                            'status' => 'error',
                            'message' => 'Mahasiswa not found',
                            'data' => null
                        ];
                        http_response_code(404);
                    } else {
                        // Delete mahasiswa
                        $deleteQuery = "DELETE FROM mahasiswa WHERE id = :id";
                        $success = executeNonQuery($deleteQuery, ['id' => $id]);

                        if ($success) {
                            $response = [
                                'status' => 'success',
                                'message' => 'Mahasiswa deleted successfully',
                                'data' => $mahasiswa
                            ];
                        } else {
                            $response = [
                                'status' => 'error',
                                'message' => 'Failed to delete mahasiswa',
                                'data' => null
                            ];
                            http_response_code(500);
                        }
                    }
                }
            }
            break;

        case 'stats':
            // Get mahasiswa statistics
            if ($method === 'GET') {
                // Get stats by status
                $statusQuery = "SELECT status, COUNT(*) as total FROM mahasiswa GROUP BY status ORDER BY total DESC";
                $statusStats = executeQuery($statusQuery);

                // Get stats by fakultas
                $fakultasQuery = "SELECT fakultas, COUNT(*) as total FROM mahasiswa GROUP BY fakultas ORDER BY total DESC";
                $fakultasStats = executeQuery($fakultasQuery);

                // Get stats by angkatan
                $angkatanQuery = "SELECT angkatan, COUNT(*) as total FROM mahasiswa GROUP BY angkatan ORDER BY angkatan DESC";
                $angkatanStats = executeQuery($angkatanQuery);

                // Get total mahasiswa
                $totalQuery = "SELECT COUNT(*) as total FROM mahasiswa";
                $totalResult = executeQuerySingle($totalQuery);
                $totalMahasiswa = $totalResult ? $totalResult['total'] : 0;

                $response = [
                    'status' => 'success',
                    'message' => 'Statistics retrieved successfully',
                    'data' => [
                        'total' => $totalMahasiswa,
                        'by_status' => $statusStats,
                        'by_fakultas' => $fakultasStats,
                        'by_angkatan' => $angkatanStats
                    ]
                ];
            }
            break;

        default:
            $response = [
                'status' => 'error',
                'message' => 'Invalid endpoint',
                'data' => null
            ];
            http_response_code(404);
            break;
    }
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => 'An error occurred: ' . $e->getMessage(),
        'data' => null
    ];
    http_response_code(500);
}

// Output JSON response
echo json_encode($response);