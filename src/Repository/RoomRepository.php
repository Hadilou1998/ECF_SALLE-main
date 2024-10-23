<?php

namespace App\Repository;

use App\Entity\Room;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Room::class);
    }

    public function searchRooms(array $criteria)
    {
        $qb = $this->createQueryBuilder('r');

        if (!empty($criteria['name'])) {
            $qb->andWhere('r.name LIKE :name')
               ->setParameter('name', '%' . $criteria['name'] . '%');
        }

        if (!empty($criteria['capacity'])) {
            $qb->andWhere('r.capacity >= :capacity')
               ->setParameter('capacity', $criteria['capacity']);
        }

        if (!empty($criteria['equipment'])) {
            foreach ($criteria['equipment'] as $equipment) {
                $qb->andWhere('JSON_CONTAINS(r.equipment, :equipment) = 1')
                   ->setParameter('equipment', '"' . $equipment . '"');
            }
        }

        if (!empty($criteria['ergonomics'])) {
            foreach ($criteria['ergonomics'] as $ergonomic) {
                $qb->andWhere('JSON_CONTAINS(r.ergonomics, :ergonomic) = 1')
                   ->setParameter('ergonomic', '"' . $ergonomic . '"');
            }
        }

        return $qb->getQuery()->getResult();
    }
}