<?php

namespace App\Controller;

use App\Entity\Reservation;
use Psr\Log\LoggerInterface;
use App\Form\ReservationType;
use App\Service\RoomAvailabilityService;
use App\Repository\ReservationRepository;
<<<<<<< HEAD
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
=======
>>>>>>> origin/main
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/reservation')]
#[IsGranted('ROLE_USER')]
class ReservationController extends AbstractController
{
    private $logger;
<<<<<<< HEAD

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
=======
    private $roomAvailabilityService;

    public function __construct(
        LoggerInterface $logger,
        RoomAvailabilityService $roomAvailabilityService
    ) {
        $this->logger = $logger;
        $this->roomAvailabilityService = $roomAvailabilityService;
>>>>>>> origin/main
    }

    #[Route('/', name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationRepository->findBy(['user' => $this->getUser()]),
        ]);
    }

    #[Route('/new', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ReservationRepository $reservationRepository): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour créer une réservation.');
            return $this->redirectToRoute('app_login');
        }

        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
<<<<<<< HEAD
                $reservation->setUser($this->getUser());
                $reservationRepository->save($reservation, true);
                $this->addFlash('success', 'Votre pré-réservation a été créée avec succès et est en attente de validation.');
                $this->logger->info('Nouvelle pré-réservation créée', ['id' => $reservation->getId()]);
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de la création de la pré-réservation', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->addFlash('error', 'Une erreur est survenue lors de la création de votre pré-réservation.');
            }
=======
                // Valider les dates
                $this->roomAvailabilityService->validateReservationDates(
                    $reservation->getStartDate(),
                    $reservation->getEndDate()
                );
>>>>>>> origin/main

                // Vérifier la disponibilité de la salle
                if (!$this->roomAvailabilityService->isRoomAvailable(
                    $reservation->getRoom(),
                    $reservation->getStartDate(),
                    $reservation->getEndDate()
                )) {
                    throw new \Exception('Cette salle n\'est pas disponible pour la période sélectionnée.');
                }

                $reservation->setUser($this->getUser());
                $reservation->setStatus(Reservation::STATUS_PRE_RESERVED);

                $reservationRepository->save($reservation, true);
                $this->addFlash('success', 'Votre pré-réservation a été créée avec succès et est en attente de validation.');
                
                return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de la création de la pré-réservation', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, ReservationRepository $reservationRepository): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Valider les dates
                $this->roomAvailabilityService->validateReservationDates(
                    $reservation->getStartDate(),
                    $reservation->getEndDate()
                );

                // Vérifier la disponibilité de la salle
                if (!$this->roomAvailabilityService->isRoomAvailable(
                    $reservation->getRoom(),
                    $reservation->getStartDate(),
                    $reservation->getEndDate()
                )) {
                    throw new \Exception('Cette salle n\'est pas disponible pour la période sélectionnée.');
                }

                $reservationRepository->save($reservation, true);
                $this->addFlash('success', 'Votre réservation a été mise à jour avec succès.');
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de la mise à jour de la réservation', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->addFlash('error', $e->getMessage());
                return $this->render('reservation/edit.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form->createView(),
                ]);
            }

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

<<<<<<< HEAD
    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, ReservationRepository $reservationRepository): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $reservationRepository->save($reservation, true);
                $this->addFlash('success', 'Votre réservation a été mise à jour avec succès.');
                $this->logger->info('Réservation mise à jour', ['id' => $reservation->getId()]);
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de la mise à jour de la réservation', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour de votre réservation.');
            }

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

=======
>>>>>>> origin/main
    #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, ReservationRepository $reservationRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->request->get('_token'))) {
            try {
                $reservationRepository->remove($reservation, true);
                $this->addFlash('success', 'La réservation a été supprimée avec succès.');
                $this->logger->info('Réservation supprimée', ['id' => $reservation->getId()]);
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de la suppression de la réservation', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression de la réservation.');
            }
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}