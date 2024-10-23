<?php

namespace App\Service;

use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class NotificationService
{
    private $mailer;
    private $entityManager;

    public function __construct(
        MailerInterface $mailer,
        EntityManagerInterface $entityManager
    ) {
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
    }

    public const STATUS_PENDING = 'pending';

    public function notifyAdminsOfPendingReservations(array $reservations): void
    {
        // Create a query to fetch pending reservations
        $pendingReservationsQuery = $this->entityManager->getRepository(Reservation::class)
            ->createQueryBuilder('r')
            ->where('r.status = :pending')
            ->setParameter('pending', self::STATUS_PENDING)
            ->getQuery();

        $reservations = $pendingReservationsQuery->getResult();

        // Check if any pending reservations exist
        if (empty($reservations)) {
            return;
        }

        // Sort reservations by start date
        usort($reservations, function (Reservation $a, Reservation $b) {
            return $a->getStartDate()->diff($b->getStartDate())->days;
        });

        // Limit to the first 10 reservations
        $reservations = array_slice($reservations, 0, 10);

        // Save the updated status for the reservations
        foreach ($reservations as $reservation) {
            $reservation->setStatus(Reservation::STATUS_CONFIRMED);
        }

        // Send email
        $this->sendEmailNotification($reservations);
    }

    private function sendEmailNotification(array $reservations): void
    {
        $email = (new Email())
            ->from('noreply@roomreservation.com')
            ->to('admin@example.com') // Configure this
            ->subject('Réservations en attente de validation')
            ->html($this->generateEmailContent($reservations));

        $this->mailer->send($email);
    }

    private function generateEmailContent(array $reservations): string
    {
        $content = "<h1>Réservations en attente</h1>";
        $content .= "<p>Les réservations suivantes nécessitent votre attention :</p>";
        $content .= "<ul>";

        foreach ($reservations as $reservation) {
            $content .= sprintf(
                "<li>Salle: %s, Date: %s, Utilisateur: %s</li>",
                $reservation->getRoom()->getName(),
                $reservation->getStartDate()->format('Y-m-d H:i'),
                $reservation->getUser()->getEmail()
            );
        }

        $content .= "</ul>";
        return $content;
    }
}