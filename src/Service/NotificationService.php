<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Entity\AdminNotification;
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

    public function notifyAdminsOfPendingReservations(array $reservations): void
    {
        // Create in-app notification
        $notification = new AdminNotification();
        $notification->setType('pending_reservations');
        $notification->setMessage(sprintf(
            'Vous avez %d réservation(s) en attente qui nécessite(nt) votre attention.',
            count($reservations)
        ));
        $notification->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

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