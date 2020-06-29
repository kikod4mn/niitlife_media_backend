<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;

class PostLikeFixtures extends BaseFixture implements DependentFixtureInterface
{
	/**
	 * @var PostRepository
	 */
	private PostRepository $postRepository;
	
	/**
	 * @var UserRepository
	 */
	private UserRepository $userRepository;
	
	/**
	 * ImageLikesFixtures constructor.
	 * @param  PostRepository  $postRepository
	 * @param  UserRepository  $userRepository
	 */
	public function __construct(PostRepository $postRepository, UserRepository $userRepository)
	{
		$this->postRepository = $postRepository;
		$this->userRepository = $userRepository;
	}
	
	/**
	 * @param  ObjectManager  $manager
	 * @throws Exception
	 */
	protected function loadData(ObjectManager $manager): void
	{
		$posts = $this->postRepository->findAll();
		
		$kiko = $this->userRepository->findOneBy(['username' => 'kiko']);
		
		foreach ($posts as $post) {
			
			if ((int) $post->getId() % 2) {
				$post->like($kiko);
			}
			
			for ($i = 0; $i < mt_rand(5, 40); $i++) {
				$post->like($this->getRandomReference(User::class));
			}
		}
		
		$manager->flush();
	}
	
	/**
	 * @return array
	 */
	public function getDependencies(): array
	{
		return [
			UserFixtures::class,
			PostCategoryFixtures::class,
			PostFixtures::class,
		];
	}
}