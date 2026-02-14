<?php

namespace App\Controllers;

use App\Services\AppointmentService;

class CancellationController {
    private $appointmentService;

    public function __construct() {
        $this->appointmentService = new AppointmentService();
    }

    public function index() {
        $token = $_GET['token'] ?? null;
        $appointment = null;
        $error = null;

        if ($token) {
            $appointment = $this->appointmentService->getAppointmentByToken($token);
            if (!$appointment) {
                $error = "Cita no encontrada o token inválido.";
            }
        } else {
            $error = "Token requerido.";
        }

        require __DIR__ . '/../../views/cancel.php';
    }
}
