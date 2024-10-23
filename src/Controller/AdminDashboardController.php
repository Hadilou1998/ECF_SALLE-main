<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use App\Service\ScheduledTasksService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminDashboardController extends AbstractController
{
    private $scheduledTasksService;

    public function __construct(ScheduledTasksService $scheduledTasksService)
    {
        $this->scheduledTasksService = $scheduledTasksService;
    }

    #[Route('/admin', name: 'admin_dashboard')]
    public function index(
        ReservationRepository $reservationRepo,
        RoomRepository $roomRepo,
        UserRepository $userRepo
    ): Response {
        // Run scheduled tasks when admin visits dashboard
        $this->scheduledTasksService->runScheduledTasks();

        // Get pending reservations that need attention
        $pendingReservations = $reservationRepo->findBy([
            'status' => Reservation::STATUS_PRE_RESERVED
        ]);

        return $this->render('admin/dashboard.html.twig', [
            'pre_reservations_count' => $reservationRepo->countByStatus(Reservation::STATUS_PRE_RESERVED),
            'confirmed_reservations_count' => $reservationRepo->countByStatus(Reservation::STATUS_CONFIRMED),
            'rooms_count' => $roomRepo->count([]),
            'users_count' => $userRepo->count([]),
            'pending_reservations' => $pendingReservations,
        ]);
    }
}