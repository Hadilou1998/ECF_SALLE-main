<?php

namespace App\Repository;

use App\Entity\Room;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeInterface;

class ReservationRepository extends ServiceEntityRepository
{
    private $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Reservation::class);
        $this->entityManager = $entityManager;
    }

    public function save(Reservation $entity, bool $flush = false): void
    {
        $this->entityManager->persist($entity);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function remove(Reservation $entity, bool $flush = false): void
    {
        $this->entityManager->remove($entity);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function hasOverlappingReservations(Room $room, DateTimeInterface $startDate, DateTimeInterface $endDate): bool
    {
        $overlappingReservations = $this->createQueryBuilder('r')
            ->andWhere('r.room = :room')
            ->andWhere('r.status != :cancelled')
            ->andWhere('r.startDate < :endDate')
            ->andWhere('r.endDate > :startDate')
            ->setParameter('room', $room)
            ->setParameter('cancelled', Reservation::STATUS_CANCELLED)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();

        return count($overlappingReservations) > 0;
    }

    public function findPendingReservationsBeforeDate(DateTimeInterface $date)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.status = :status')
            ->andWhere('r.startDate <= :date')
            ->setParameter('status', Reservation::STATUS_PRE_RESERVED)
            ->setParameter('date', $date)
            ->orderBy('r.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countByStatus(string $status): int
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findReservationsForRoom(Room $room, DateTimeInterface $start, DateTimeInterface $end)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.room = :room')
            ->andWhere('r.startDate <= :end')
            ->andWhere('r.endDate >= :start')
            ->setParameter('room', $room)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }

    public function findUpcomingReservations()
    {
        $now = new \DateTime();
        return $this->createQueryBuilder('r')
            ->andWhere('r.startDate > :now')
            ->setParameter('now', $now)
            ->orderBy('r.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}