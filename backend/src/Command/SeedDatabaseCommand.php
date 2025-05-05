<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\VacationRequest;
use App\Enum\VacationStatus;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed-database',
    description: 'Seeds the database with fake users and vacation requests.',
)]
class SeedDatabaseCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $faker = Factory::create();

        $owner = new User();
        $owner->setName('Owner Manager');
        $owner->setEmail('owner@example.com');
        $owner->setEmployeeCode('0000001');
        $owner->setRoles(['ROLE_MANAGER']);
        $owner->setPassword($this->hasher->hashPassword($owner, 'password'));

        $this->em->persist($owner);

        // Create 2 managers
        for ($i = 0; $i < 2; $i++) {
            $manager = new User();
            $manager->setName($faker->name());
            $manager->setEmail($faker->unique()->safeEmail());
            $manager->setEmployeeCode($faker->unique()->bothify('MGR###'));
            $manager->setRoles(['ROLE_MANAGER']);
            $manager->setPassword($this->hasher->hashPassword($manager, 'password'));

            $this->em->persist($manager);
        }

        // Create 2 employees and some vacation requests for them
        for ($i = 0; $i < 2; $i++) {
            $employee = new User();
            $employee->setName($faker->name());
            $employee->setEmail($faker->unique()->safeEmail());
            $employee->setEmployeeCode($faker->unique()->bothify('EMP###'));
            $employee->setRoles(['ROLE_EMPLOYEE']);
            $employee->setPassword($this->hasher->hashPassword($employee, 'password'));

            $this->em->persist($employee);

            // Add 1–2 vacation requests per employee
            $requestCount = rand(1, 2);
            for ($j = 0; $j < $requestCount; $j++) {
                $start = $faker->dateTimeBetween('+1 days', '+2 weeks');
                $end = (clone $start)->modify('+' . rand(2, 10) . ' days');

                $vacation = new VacationRequest();
                $vacation->setUser($employee);
                $vacation->setStartDate($start);
                $vacation->setEndDate($end);
                $vacation->setReason($faker->sentence());
                $vacation->setStatus(VacationStatus::Pending);
                $vacation->setSubmittedAt($faker->dateTimeBetween('-2 weeks', 'now'));

                $this->em->persist($vacation);
            }
        }

        $this->em->flush();

        $output->writeln('<info>Database seeded successfully!</info>');
        return Command::SUCCESS;
    }
}
