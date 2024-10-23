<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\_AdminReservationType;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/reservation')]
class AdminReservationController extends AbstractController
{
    #[Route('/', name: 'admin_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        return $this->render('admin_reservation/index.html.twig', [
            'reservations' => $reservationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ReservationRepository $reservationRepository): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(_AdminReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($reservationRepository->isRoomAvailable($reservation->getRoom(), $reservation->getStartDate(), $reservation->getEndDate())) {
                $reservationRepository->save($reservation, true);
                $this->addFlash('success', 'La réservation a été créée avec succès.');
                return $this->redirectToRoute('admin_reservation_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $this->addFlash('error', 'La salle n\'est pas disponible pour cette période.');
            }
        }

        return $this->render('admin_reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        return $this->render('admin_reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, ReservationRepository $reservationRepository): Response
    {
        $form = $this->createForm(_AdminReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reservationRepository->save($reservation, true);
            $this->addFlash('success', 'La réservation a été mise à jour avec succès.');

            return $this->redirectToRoute('admin_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, ReservationRepository $reservationRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->request->get('_token'))) {
            $reservationRepository->remove($reservation, true);
            $this->addFlash('success', 'La réservation a été supprimée avec succès.');
        }

        return $this->redirectToRoute('admin_reservation_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/validate', name: 'admin_reservation_validate', methods: ['POST'])]
    public function validate(Request $request, Reservation $reservation, ReservationRepository $reservationRepository): Response
    {
        if ($this->isCsrfTokenValid('validate'.$reservation->getId(), $request->request->get('_token'))) {
            $reservation->setStatus(Reservation::STATUS_CONFIRMED);
            $reservationRepository->save($reservation, true);
            $this->addFlash('success', 'La pré-réservation a été validée avec succès.');
        }

        return $this->redirectToRoute('admin_reservation_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/cancel', name: 'admin_reservation_cancel', methods: ['POST'])]
    public function cancel(Request $request, Reservation $reservation, ReservationRepository $reservationRepository): Response
    {
        if ($this->isCsrfTokenValid('cancel'.$reservation->getId(), $request->request->get('_token'))) {
            $reservation->setStatus(Reservation::STATUS_CANCELLED);
            $reservationRepository->save($reservation, true);
            $this->addFlash('success', 'La pré-réservation a été annulée avec succès.');
        }

        return $this->redirectToRoute('admin_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}