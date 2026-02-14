<?php

namespace App\Services;

use App\Models\ScheduleRepository;
use App\Models\AppointmentRepository;
use DateTime;
use DateInterval;
use DatePeriod;

class AvailabilityService {
    private $scheduleRepo;
    private $appointmentRepo;

    public function __construct() {
        $this->scheduleRepo = new ScheduleRepository();
        $this->appointmentRepo = new AppointmentRepository();
    }

    public function getAvailableSlots(string $date, int $slotDurationMinutes = 30): array {
        $timestamp = strtotime($date);
        $dayOfWeek = (int)date('w', $timestamp);
        
        $config = $this->scheduleRepo->getConfigForDay($dayOfWeek);

        if (!$config || !$config['is_open']) {
            return [];
        }

        try {
            $startTime = $config['start_time'];
            $endTime = $config['end_time'];
            
            $start = new DateTime("$date $startTime");
            $end = new DateTime("$date $endTime");
            $interval = new DateInterval("PT{$slotDurationMinutes}M");
            
            $period = new DatePeriod($start, $interval, $end);
            
            $potentialSlots = [];
            
            $breakStart = null;
            $breakEnd = null;
            if ($config['break_start'] && $config['break_end']) {
                $breakStart = new DateTime("$date {$config['break_start']}");
                $breakEnd = new DateTime("$date {$config['break_end']}");
            }

            foreach ($period as $dt) {
                // If the slot starts during the break, skip it
                if ($breakStart && $breakEnd) {
                    if ($dt >= $breakStart && $dt < $breakEnd) {
                        continue;
                    }
                }
                
                $potentialSlots[] = $dt->format('H:i');
            }

            $bookedTimes = $this->appointmentRepo->getAppointmentsForDate($date);

            // Filter booked slots
            $availableSlots = array_values(array_diff($potentialSlots, $bookedTimes));

            // Filter past slots and slots less than 1 hour away
            $now = time();
            $minNotice = 3600; // 1 hour
            
            $finalSlots = [];
            foreach ($availableSlots as $slotTime) {
                $slotTimestamp = strtotime("$date $slotTime");
                if ($slotTimestamp > ($now + $minNotice)) {
                    $finalSlots[] = $slotTime;
                }
            }

            return $finalSlots;
        } catch (\Exception $e) {
            return [];
        }
    }
}
