<?php

namespace App\DataFixtures;

use App\Entity\Game;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        $admin = new User();

        $admin
            ->setEmail('a@a.a')
            ->setFirstName('Enzo')
            ->setPseudo('ArcanAdmin')
            ->setBirthDate($faker->dateTimeBetween('-60 years','-18 years'))
            ->setPhoto('photo.jpg')
            ->setLastname('Renaud')
            ->setRoles(["ROLE_ADMIN"])
            ->setPassword(
                $this->encoder->hashPassword($admin, 'a')
            );
        $manager->persist($admin);

        $nonadmin = new User();

        $nonadmin
            ->setEmail('b@b.b')
            ->setFirstName('Renaud')
            ->setBirthDate($faker->dateTimeBetween('-60 years','-18 years'))
            ->setPhoto('photo.jpg')
            ->setPseudo('ArcanNonAdmin')
            ->setLastname('Enzo')
            ->setPassword(
                $this->encoder->hashPassword($nonadmin, 'a')
            );
        $manager->persist($nonadmin);

        for ($i = 0; $i < 50; $i++) {
            $user = new User();
            $user
                ->setEmail($faker->email)
                ->setFirstName($faker->firstName)
                ->setBirthDate($faker->dateTimeBetween('-60 years','-18 years'))
                ->setPhoto('photo.jpg')
                ->setPseudo($faker->firstName)
                ->setLastname($faker->userName)
                ->setPassword(
                    $this->encoder->hashPassword($user, 'a'));
            $manager->persist($user);
            $this->setReference('user',$admin);
        }


            $gdn = new Game();

            $gdn
                ->setName('le jeu')
                ->setDescription($faker->paragraph('10'))
                ->setDateStart($faker->dateTimeBetween('+20 days','+25 days'))
                ->setDateEnd($faker->dateTimeBetween('+30 days','+30 days'))
                ->setIsPublished(false)
                ->setBanner('something.jpg');
            $manager->persist($gdn);
            $this->addReference('gdn', $gdn);


        $manager->flush();
    }
}
