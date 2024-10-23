<?php

namespace App\EventSubscriber;

use App\Service\RoomAvailabilityService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Session\SessionFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ReservationCheckSubscriber implements EventSubscriberInterface
{
    private RoomAvailabilityService $roomAvailabilityService;
    private SessionFactoryInterface $sessionFactory;
    private RequestStack $requestStack;
    private const CHECK_INTERVAL = 3600;

    public function __construct(
        RoomAvailabilityService $roomAvailabilityService,
        SessionFactoryInterface $session,
        RequestStack $requestStack
    ) {
        $this->roomAvailabilityService = $roomAvailabilityService;
        $this->sessionFactory = $session;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $session = $this->requestStack->getSession();
        $lastCheck = $session->get('last_reservation_check', 0);
        $now = time();

        if ($now - $lastCheck >= self::CHECK_INTERVAL) {
            $this->roomAvailabilityService->checkAndNotifyPendingReservations();
            $session->set('last_reservation_check', $now);
        }
    }
}