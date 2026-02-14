<?php

namespace App\Controllers\Api;

use App\Models\AppointmentRepository;
use App\Services\WhatsAppService;

class AdminApiController {
    private $appointmentRepo;
    private $whatsappService;

    public function __construct() {
        // Here we should check auth again if not done by middleware
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $this->appointmentRepo = new AppointmentRepository();
        $this->whatsappService = new WhatsAppService();
    }

    public function getAppointments() {
        header('Content-Type: application/json');
        
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $appointments = $this->appointmentRepo->getAllForDate($date);
        
        // Helper to construct base URL dynamically
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        // Assuming index.php is in public/ and request is routed there.
        // SCRIPT_NAME usually returns /path/to/public/index.php
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        // Ensure no backslashes on Windows
        $scriptDir = str_replace('\\', '/', $scriptDir);
        // Remove trailing slash if exists (root dir case)
        $scriptDir = rtrim($scriptDir, '/');
        
        // Construct public base URL (e.g., http://localhost/gestion/public)
        $baseUrl = $protocol . "://" . $host . $scriptDir;

        foreach ($appointments as &$appt) {
            $cancelUrl = $baseUrl . "/cancel?token=" . $appt['cancel_token'];
            $msg = $this->whatsappService->getConfirmationMessage($appt, $cancelUrl);
            $appt['whatsapp_link'] = $this->whatsappService->generateLink($appt['customer_phone'], $msg);
        }
        
        echo json_encode($appointments);
    }

    public function cancel($id) {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID required']);
            return;
        }

        // We can reuse updateStatus from repo
        $success = $this->appointmentRepo->updateStatus($id, 'cancelled');

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Appointment cancelled by admin']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to cancel appointment']);
        }
    }

    public function updateStatus($id) {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $status = $input['status'] ?? null;
        
        // Allowed statuses based on DB constraint
        $allowed = ['booked', 'attended', 'cancelled'];

        if (!$id || !in_array($status, $allowed)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid ID or Status']);
            return;
        }

        $success = $this->appointmentRepo->updateStatus($id, $status);

        if ($success) {
            echo json_encode(['success' => true, 'status' => $status]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update status']);
        }
    }

    public function update($id) {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $date = $input['date'] ?? null;
        $time = $input['time'] ?? null;

        if (!$id || !$date || !$time) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing date or time']);
            return;
        }

        // Validate format? (Simple check)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !preg_match('/^\d{2}:\d{2}$/', $time)) {
             http_response_code(400);
             echo json_encode(['error' => 'Invalid date/time format']);
             return;
        }

        // Check for conflicts
        if ($this->appointmentRepo->checkConflict($date, $time, $id)) {
            http_response_code(409); // Conflict
            echo json_encode(['error' => 'Slot already taken']);
            return;
        }

        $success = $this->appointmentRepo->updateDateAndTime($id, $date, $time);

        if ($success) {
            echo json_encode(['success' => true, 'date' => $date, 'time' => $time]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Database update failed']);
        }
    }
}
