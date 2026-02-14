<?php

namespace App\Controllers\Api;

use App\Services\AvailabilityService;

class AvailabilityController {
    private $availabilityService;

    public function __construct() {
        $this->availabilityService = new AvailabilityService();
    }

    public function index() {
        header('Content-Type: application/json');
        
        $date = $_GET['date'] ?? null;
        
        if (!$date) {
            http_response_code(400);
            echo json_encode(['error' => 'Date is required']);
            return;
        }

        try {
            // Validate date format
            $d = \DateTime::createFromFormat('Y-m-d', $date);
            if (!$d || $d->format('Y-m-d') !== $date) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid date format (YYYY-MM-DD)']);
                return;
            }

            $slots = $this->availabilityService->getAvailableSlots($date);
            echo json_encode(['date' => $date, 'slots' => $slots]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error']);
        }
    }
}
