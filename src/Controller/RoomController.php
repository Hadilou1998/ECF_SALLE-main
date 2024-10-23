<?php

namespace App\Controller;

use App\Entity\Room;
use App\Form\RoomType;
use App\Form\RoomSearchType;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/room')]
class RoomController extends AbstractController
{
    #[Route('/', name: 'app_room_index', methods: ['GET', 'POST'])]
    public function index(Request $request, RoomRepository $roomRepository): Response
    {
        $form = $this->createForm(RoomSearchType::class);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
            $rooms = $roomRepository->searchRooms($criteria);
        } else {
            $rooms = $roomRepository->findAll();
        }

        // Suppression des doublons
        $uniqueRooms = [];
        foreach ($rooms as $room) {
            $roomName = $room->getName(); // ou un autre identifiant unique
            if (!isset($uniqueRooms[$roomName])) {
                $uniqueRooms[$roomName] = $room;
            }
        }
        
        // Convertir le tableau en indices numériques
        $rooms = array_values($uniqueRooms);
    
        return $this->render('room/search.html.twig', [
            'rooms' => $rooms,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_room_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $room = new Room();
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($room);
            $entityManager->flush();

            return $this->redirectToRoute('app_room_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('room/new.html.twig', [
            'room' => $room,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_room_show', methods: ['GET'])]
    public function show($id, RoomRepository $roomRepository): Response
    {
        $room = $roomRepository->find($id);

        return $this->render('room/show.html.twig', [
            'room' => $room,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_room_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id, RoomRepository $roomRepository, Room $room, EntityManagerInterface $entityManager): Response
    {
        // Pas de changements nécessaires ici ; Room sera injecté par ParamConverter
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_room_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('room/edit.html.twig', [
            'room' => $room,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_room_delete', methods: ['POST'])]
    public function delete(Request $request, Room $room, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$room->getId(), $request->request->get('_token'))) {
            $entityManager->remove($room);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_room_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/availability', name: 'app_room_availability', methods: ['GET', 'POST'])]
    public function availability(Request $request, RoomRepository $roomRepository, ReservationRepository $reservationRepository): Response
    {
        $form = $this->createForm(RoomSearchType::class);
        $form->handleRequest($request);

        $availableRooms = [];
        $searchPerformed = false;

        if ($form->isSubmitted() && $form->isValid()) {
            $searchPerformed = true;
            $criteria = $form->getData();
            
            // Obtenez les chambres basées sur les critères de recherche
            $rooms = $roomRepository->searchRooms($criteria);
            
            // Suppression des doublons
            $uniqueRooms = [];
            foreach ($rooms as $room) {
                $roomName = $room->getName(); // ou un autre identifiant unique
                if (!isset($uniqueRooms[$roomName])) {
                    $uniqueRooms[$roomName] = $room;
                }
            }

            // Convertir le tableau en indices numériques
            $rooms = array_values($uniqueRooms);
            
            foreach ($rooms as $room) {
                // Vérifiez la disponibilité de chaque chambre
                $availability = $this->getRoomAvailability($room, $reservationRepository);
                if (!empty($availability)) {
                    $availableRooms[$room->getId()] = [
                        'room' => $room,
                        'availability' => $availability
                    ];
                }
            }
        }

        return $this->render('room/availability.html.twig', [
            'form' => $form->createView(),
            'availableRooms' => $availableRooms,
            'searchPerformed' => $searchPerformed,
        ]);
    }

    private function getRoomAvailability(Room $room, ReservationRepository $reservationRepository): array
    {
        $startDate = new \DateTime();
        $endDate = (clone $startDate)->modify('+7 days');
        $reservations = $reservationRepository->findReservationsForRoom($room, $startDate, $endDate);

        $availability = [];
        $currentDate = clone $startDate;

        while ($currentDate <= $endDate) {
            $dayAvailability = $this->getDayAvailability($currentDate, $reservations);
            if (!empty($dayAvailability)) {
                $availability[$currentDate->format('Y-m-d')] = $dayAvailability;
            }
            $currentDate->modify('+1 day');
        }

        return $availability;
    }

    private function getDayAvailability(\DateTime $date, array $reservations): array
    {
        $dayStart = (clone $date)->setTime(9, 0);
        $dayEnd = (clone $date)->setTime(18, 0);
        $availableSlots = [];

        $currentSlot = clone $dayStart;
        while ($currentSlot < $dayEnd) {
            $slotEnd = (clone $currentSlot)->modify('+1 heure');
            $isAvailable = true;

            foreach ($reservations as $reservation) {
                if ($reservation->getStartDate() < $slotEnd && $reservation->getEndDate() > $currentSlot) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                $availableSlots[] = [
                    'start' => $currentSlot->format('H:i'),
                    'end' => $slotEnd->format('H:i')
                ];
            }

            $currentSlot = $slotEnd;
        }

        return $availableSlots;
    }
}