<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\UserProfile;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends BaseFixture
{
	/**
	 * @var UserPasswordEncoderInterface
	 */
	private UserPasswordEncoderInterface $passwordEncoder;
	
	/**
	 * UserFixtures constructor.
	 * @param  UserPasswordEncoderInterface  $passwordEncoder
	 */
	public function __construct(UserPasswordEncoderInterface $passwordEncoder)
	{
		$this->passwordEncoder = $passwordEncoder;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function loadData(ObjectManager $manager): void
	{
		$this->createMany(
			User::class, 1, function (User $user, $i) use ($manager) {
			
			$user->setUsername('kiko');
			$user->setFullname('kiko kikopolis');
			$user->setEmail('kiko@kiko.com');
			$user->setPassword($this->passwordEncoder->encodePassword($user, '123'));
			$user->setRole(User::ROLE_SUPER_ADMINISTRATOR);
			//						$user->activate();
			
			$profile = new UserProfile();
			$user->setProfile($profile);
			$profile->setUser($user);
			$profile->setAvatar('images/defaultUserAvatar/defaultAvatar.jpg');
			
			$manager->persist($profile);
		}
		);
		
		$faker = Factory::create();
		$this->createMany(
			User::class, 40, function (User $user, $i) use ($faker, $manager) {
			$user->setUsername($faker->userName);
			$user->setFullname($faker->name);
			$user->setEmail($faker->email);
			$user->setPassword($this->passwordEncoder->encodePassword($user, '123'));
			//			$user->activate();
			
			$profile = new UserProfile();
			$user->setProfile($profile);
			$profile->setUser($user);
			$profile->setAvatar('images/defaultUserAvatar/defaultAvatar.jpg');
			
			$manager->persist($profile);
		}
		);
		
		$manager->flush();
	}
}
