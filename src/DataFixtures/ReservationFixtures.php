<?php

namespace App\DataFixtures;

use App\Entity\Reservation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\UserFixtures;
use App\DataFixtures\RoomFixtures;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;

class ReservationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $adminUser = $this->getReference(UserFixtures::USER_REFERENCE);

        // Create 50 reservations for the admin user
        for ($i = 0; $i < 50; $i++) {
            $reservation = new Reservation();
            $startDate = new \DateTimeImmutable($faker->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d H:i:s'));
            $reservation
                ->setStartDate($startDate)
                ->setEndDate($startDate->modify('+' . $faker->numberBetween(1, 5) . ' hours'))
                ->setStatus($faker->randomElement([Reservation::STATUS_PRE_RESERVED, Reservation::STATUS_CONFIRMED, Reservation::STATUS_CANCELLED]))
                ->setUser($adminUser)
                ->setRoom($this->getReference(RoomFixtures::ROOM_REFERENCE));
            $manager->persist($reservation);
        }

        // Create reservations for other users
        $otherUsers = $manager->getRepository(User::class)->findAll();
        foreach ($otherUsers as $user) {
            for ($i = 0; $i < 5; $i++) {
                $reservation = new Reservation();
                $startDate = new \DateTimeImmutable($faker->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d H:i:s'));
                $reservation
                    ->setStartDate($startDate)
                    ->setEndDate($startDate->modify('+' . $faker->numberBetween(1, 5) . ' hours'))
                    ->setStatus($faker->randomElement([Reservation::STATUS_PRE_RESERVED, Reservation::STATUS_CONFIRMED, Reservation::STATUS_CANCELLED]))
                    ->setUser($user)
                    ->setRoom($this->getReference(RoomFixtures::ROOM_REFERENCE));
                $manager->persist($reservation);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            RoomFixtures::class,
        ];
    }
}