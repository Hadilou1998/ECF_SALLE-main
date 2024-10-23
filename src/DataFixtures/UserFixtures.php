<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public const USER_REFERENCE = 'user_reference';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Create admin user
        $admin = new User();
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin');
        $admin
            ->setEmail('admin@example.com')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($hashedPassword);
        $manager->persist($admin);

        echo "Admin hashed password: " . $hashedPassword . "\n";
        
        $this->setReference(self::USER_REFERENCE, $admin);

        // Create 50 regular users
        for ($i = 0; $i < 50; $i++) {
            $user = new User();
            $user
                ->setEmail($faker->unique()->safeEmail)
                ->setRoles(['ROLE_USER'])
                ->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $manager->persist($user);
        }

        $manager->flush();
    }
}