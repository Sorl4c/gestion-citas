<?php

namespace App\Controllers\Api;

use App\Models\AdminRepository;

class AuthController {
    private $adminRepo;

    public function __construct() {
        $this->adminRepo = new AdminRepository();
    }

    public function login() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        $user = $this->adminRepo->findByUsername($username);

        if ($user && password_verify($password, $user['password_hash'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user['username'];
            
            echo json_encode(['success' => true, 'message' => 'Logged in']);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
        }
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Logged out']);
    }
    
    public function check() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user_id'])) {
             echo json_encode(['authenticated' => true, 'user' => $_SESSION['user_id']]);
        } else {
             echo json_encode(['authenticated' => false]);
        }
    }
}
