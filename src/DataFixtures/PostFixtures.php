<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\PostCategory;
use App\Entity\Tag;
use App\Repository\UserRepository;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PostFixtures extends BaseFixture implements DependentFixtureInterface
{
	/**
	 * @var UserRepository
	 */
	private UserRepository $userRepository;
	
	/**
	 * PostFixtures constructor.
	 * @param  UserRepository  $userRepository
	 */
	public function __construct(UserRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}
	
	/**
	 * @param  ObjectManager  $manager
	 */
	protected function loadData(ObjectManager $manager): void
	{
		$user  = $this->userRepository->findOneBy(['username' => 'kiko']);
		$faker = Factory::create();
		$this->createMany(
			Post::class, 50, function (Post $post, $i) use ($user, $faker) {
			// Simulate site owner being only one posting
			$post->setAuthor($user);
			$post->setTitle($faker->realText(50));
			$post->setBody($faker->realText(7500));
			$post->setCategory($this->getRandomReference(PostCategory::class));
			for ($i = 0; $i < rand(3, 6); $i++) {
				$post->addTag($this->getRandomReference(Tag::class));
			}
		}
		);
		$manager->flush();
	}
	
	/**
	 * @return array|string[]
	 */
	public function getDependencies(): array
	{
		return [
			UserFixtures::class,
			TagFixtures::class,
			PostCategoryFixtures::class,
		];
	}
}