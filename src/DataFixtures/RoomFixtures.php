<?php

namespace App\DataFixtures;

use App\Entity\Room;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class RoomFixtures extends Fixture
{
    public const ROOM_REFERENCE = 'room_reference';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $equipmentOptions = ['projector', 'whiteboard', 'computer', 'video_conferencing', 'touch_screen'];
        $ergonomicsOptions = ['natural_light', 'wheelchair_accessible', 'air_conditioning', 'soundproof', 'ergonomic_furniture'];

        // Create 50 rooms
        for ($i = 0; $i < 50; $i++) {
            $room = new Room();
            $room
                ->setName($faker->company . ' Room')
                ->setCapacity($faker->numberBetween(5, 50))
                ->setEquipment($faker->randomElements($equipmentOptions, $faker->numberBetween(1, count($equipmentOptions))))
                ->setErgonomics($faker->randomElements($ergonomicsOptions, $faker->numberBetween(1, count($ergonomicsOptions))));
            $manager->persist($room);

            if ($i === 0) {
                $this->setReference(self::ROOM_REFERENCE, $room);
            }
        }

        $manager->flush();
    }
}