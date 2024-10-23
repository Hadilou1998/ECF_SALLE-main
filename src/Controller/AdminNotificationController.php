<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminNotificationController extends AbstractController
{
    #[Route('/admin/notifications', name: 'admin_notifications')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        $pendingReservations = $reservationRepository->findPendingReservations();

        return $this->render('admin/notifications.html.twig', [
            'pending_reservations' => $pendingReservations,
        ]);
    }
}