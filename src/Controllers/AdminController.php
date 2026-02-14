<?php

namespace App\Controllers;

class AdminController {
    public function index() {
        // Simple auth check for the view (redirect if not logged in)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
             // If not logged in, show login page
             require __DIR__ . '/../../views/admin/login.php';
             return;
        }

        require __DIR__ . '/../../views/admin/dashboard.php';
    }
}
