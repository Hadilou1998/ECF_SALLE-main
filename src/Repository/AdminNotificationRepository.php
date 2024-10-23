<?php

namespace App\Repository;

use App\Entity\AdminNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AdminNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminNotification::class);
    }

    public function findUnreadNotifications()
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.isRead = :isRead')
            ->setParameter('isRead', false)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}