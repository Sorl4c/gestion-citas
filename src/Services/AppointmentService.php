<?php

namespace App\Services;

use App\Models\AppointmentRepository;
use App\Models\ScheduleRepository;
use App\Services\AvailabilityService;
use Ramsey\Uuid\Uuid;

class AppointmentService {
    private $appointmentRepo;
    private $availabilityService;
    private $scheduleRepo;

    public function __construct() {
        $this->appointmentRepo = new AppointmentRepository();
        $this->availabilityService = new AvailabilityService();
        $this->scheduleRepo = new ScheduleRepository();
    }

    public function bookAppointment(array $data) {
        // 1. Basic Validation
        if (empty($data['name']) || empty($data['phone']) || empty($data['date']) || empty($data['time'])) {
            throw new \Exception("Missing required fields (name, phone, date, time)");
        }

        $phone = preg_replace('/[^0-9]/', '', $data['phone']);
        if (strlen($phone) < 9) {
            throw new \Exception("Invalid phone number (min 9 digits)");
        }

        $date = $data['date'];
        $time = $data['time'];
        
        // 1b. Time Constraints
        $appointmentDateTime = strtotime("$date $time");
        $now = time();

        // Rule: Minimum Notice (1 hour)
        if ($appointmentDateTime < ($now + 3600)) {
            throw new \Exception("La cita debe reservarse con al menos 1 hora de antelación.");
        }

        // Rule: Future Limit (60 days)
        if ($appointmentDateTime > ($now + (60 * 24 * 3600))) {
            throw new \Exception("No se pueden reservar citas con más de 60 días de antelación.");
        }
        
        // 2. Availability Check (Optimistic)
        $slots = $this->availabilityService->getAvailableSlots($date);
        
        if (!in_array($time, $slots)) {
            throw new \Exception("El hueco seleccionado ya no está disponible.");
        }

        // 3. Anti-Abuse Limit (Max 2 per week per phone)
        $weeklyCount = $this->appointmentRepo->countWeeklyAppointments($phone, $date);
        
        if ($weeklyCount >= 2) {
            throw new \Exception("Límite semanal alcanzado (máx 2 citas por semana).");
        }

        // 4. Generate Tokens
        $id = Uuid::uuid4()->toString();
        $cancelToken = Uuid::uuid4()->toString();

        // 5. Persist (with Transaction for Atomic Check)
        try {
            $this->appointmentRepo->beginTransaction();

            // Strict check inside transaction (Double Booking Prevention)
            if ($this->appointmentRepo->isSlotTaken($date, $time)) {
                $this->appointmentRepo->rollback();
                throw new \Exception("Lo sentimos, este hueco acaba de ser ocupado por otra persona.");
            }

            $appointmentData = [
                'id' => $id,
                'customer_name' => $data['name'],
                'customer_phone' => $phone,
                'date' => $date,
                'time' => $time,
                'cancel_token' => $cancelToken
            ];

            $success = $this->appointmentRepo->create($appointmentData);

            if (!$success) {
                throw new \Exception("Database error while creating appointment");
            }

            $this->appointmentRepo->commit();

        } catch (\Exception $e) {
            if ($this->appointmentRepo->inTransaction()) {
                $this->appointmentRepo->rollback();
            }
            throw $e;
        }

        return [
            'success' => true,
            'appointment_id' => $id,
            'cancel_token' => $cancelToken,
            'message' => 'Appointment confirmed'
        ];
    }

    public function cancelAppointment(string $token) {
        $appointment = $this->appointmentRepo->findByToken($token);
        
        if (!$appointment) {
            return false;
        }

        if ($appointment['status'] === 'cancelled') {
            return true;
        }

        return $this->appointmentRepo->updateStatus($appointment['id'], 'cancelled');
    }

    public function getAppointmentByToken(string $token) {
        return $this->appointmentRepo->findByToken($token);
    }
}
