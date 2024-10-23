<?php
namespace App\Service;

use App\Entity\Room;
use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use App\Service\NotificationService;
use DateTime;
use DateTimeInterface;
use Exception;

class RoomAvailabilityService
{
    private $reservationRepository;
    private $notificationService;

    public function __construct(
        ReservationRepository $reservationRepository,
        NotificationService $notificationService
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->notificationService = $notificationService;
    }

    public function isRoomAvailable(Room $room, DateTimeInterface $startDate, DateTimeInterface $endDate): bool
    {
        // Vérifier que la date n'est pas dans le passé
        $now = new DateTime();
        if ($startDate < $now) {
            throw new Exception('La date de début ne peut pas être dans le passé.');
        }

        // Vérifier que la date de fin n'est pas avant la date de début
        if ($endDate < $startDate) {
            throw new Exception('La date de fin ne peut pas être avant la date de début.');
        }

        // Check business hours (9 AM to 9 PM)
        $startHour = (int)$startDate->format('H');
        $endHour = (int)$endDate->format('H');
        
        if ($startHour < 9 || $endHour > 21) {
            throw new Exception('Les réservations ne sont possibles qu\'entre 9h et 21h.');
        }

        // Si la réservation est pour aujourd'hui, vérifier que l'heure n'est pas déjà passée
        if ($startDate->format('Y-m-d') === $now->format('Y-m-d')) {
            $currentHour = (int)$now->format('H');
            if ($startHour <= $currentHour) {
                throw new Exception('L\'heure de début doit être ultérieure à l\'heure actuelle.');
            }
        }

        return !$this->reservationRepository->hasOverlappingReservations($room, $startDate, $endDate);
    }

    public function validateReservationDates(DateTimeInterface $startDate, DateTimeInterface $endDate): void
    {
        $now = new DateTime();
        
        // Vérifier que la date n'est pas dans le passé
        if ($startDate < $now) {
            throw new Exception('La date de début ne peut pas être dans le passé.');
        }

        // Vérifier que la date de fin n'est pas avant la date de début
        if ($endDate < $startDate) {
            throw new Exception('La date de fin ne peut pas être avant la date de début.');
        }

        // Vérifier les heures d'ouverture
        $startHour = (int)$startDate->format('H');
        $endHour = (int)$endDate->format('H');

        if ($startHour < 9 || $endHour > 21) {
            throw new Exception('Les réservations ne sont possibles qu\'entre 9h et 21h.');
        }

        // Si la réservation est pour aujourd'hui, vérifier que l'heure n'est pas déjà passée
        if ($startDate->format('Y-m-d') === $now->format('Y-m-d')) {
            $currentHour = (int)$now->format('H');
            if ($startHour <= $currentHour) {
                throw new Exception('L\'heure de début doit être ultérieure à l\'heure actuelle.');
            }
        }
    }

    public function checkAndNotifyPendingReservations(): void
    {
        $fiveDaysFromNow = new DateTime('+5 days');
        $pendingReservations = $this->reservationRepository->findPendingReservationsBeforeDate($fiveDaysFromNow);

        if (!empty($pendingReservations)) {
            $this->notificationService->notifyAdminsOfPendingReservations($pendingReservations);
        }
    }

    public function getAvailableTimeSlots(Room $room, DateTimeInterface $date): array
    {
        $slots = [];
        $currentHour = 9; // Start at 9 AM
        $endHour = 21;   // End at 9 PM

        // Convert to DateTime to use setTime
        $dateTime = new DateTime($date->format('Y-m-d'));

        while ($currentHour < $endHour) {
            $startTime = clone $dateTime;
            $startTime->setTime($currentHour, 0);
            
            $endTime = clone $dateTime;
            $endTime->setTime($currentHour + 1, 0);

            if ($this->isRoomAvailable($room, $startTime, $endTime)) {
                $slots[] = [
                    'start' => $startTime->format('H:i'),
                    'end' => $endTime->format('H:i')
                ];
            }

            $currentHour++;
        }

        return $slots;
    }
}