<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ScheduledTasksService
{
    private $roomAvailabilityService;
    private $sessionFactory;
    private $requestStack;

    public function __construct(
        RoomAvailabilityService $roomAvailabilityService,
        SessionFactoryInterface $session,
        RequestStack $requestStack
    ) {
        $this->roomAvailabilityService = $roomAvailabilityService;
        $this->sessionFactory = $session;
        $this->requestStack = $requestStack;
    }

    public function runScheduledTasks(): void
    {
        $session = $this->requestStack->getSession();
        $lastCheck = $session->get('last_scheduled_tasks', 0);
        $now = time();

        if ($now - $lastCheck >= 3600) {
            $this->roomAvailabilityService->checkAndNotifyPendingReservations();
            $session->set('last_scheduled_tasks', $now);
        }
    }
}