<?php

namespace App\Controllers\Api;

use App\Services\AppointmentService;

class AppointmentController {
    private $appointmentService;

    public function __construct() {
        $this->appointmentService = new AppointmentService();
    }

    public function create() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            return;
        }

        try {
            $result = $this->appointmentService->bookAppointment([
                'name' => $input['name'] ?? null,
                'phone' => $input['phone'] ?? null,
                'date' => $input['date'] ?? null,
                'time' => $input['time'] ?? null
            ]);

            http_response_code(201);
            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function cancel() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['token'] ?? null;

        if (!$token) {
            http_response_code(400);
            echo json_encode(['error' => 'Token required']);
            return;
        }

        try {
            $success = $this->appointmentService->cancelAppointment($token);
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Appointment cancelled']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Invalid token or appointment not found']);
            }
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
