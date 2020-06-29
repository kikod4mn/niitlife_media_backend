<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\User;
use App\Repository\PostCommentRepository;
use App\Repository\UserRepository;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;

class PostCommentLikeFixtures extends BaseFixture implements DependentFixtureInterface
{
	/**
	 * @var PostCommentRepository
	 */
	private PostCommentRepository $postCommentRepository;
	
	/**
	 * @var UserRepository
	 */
	private UserRepository $userRepository;
	
	/**
	 * ImageLikesFixtures constructor.
	 * @param  PostCommentRepository  $postCommentRepository
	 * @param  UserRepository         $userRepository
	 */
	public function __construct(PostCommentRepository $postCommentRepository, UserRepository $userRepository)
	{
		$this->postCommentRepository = $postCommentRepository;
		$this->userRepository        = $userRepository;
	}
	
	/**
	 * @param  ObjectManager  $manager
	 * @throws Exception
	 */
	protected function loadData(ObjectManager $manager): void
	{
		$comments = $this->postCommentRepository->findAll();
		
		$kiko = $this->userRepository->findOneBy(['username' => 'kiko']);
		
		foreach ($comments as $comment) {
			
			if ((int) $comment->getId() % 2) {
				$comment->like($kiko);
			}
			
			for ($i = 0; $i < mt_rand(5, 40); $i++) {
				$comment->like($this->getRandomReference(User::class));
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
			PostFixtures::class,
			PostCommentFixtures::class,
		];
	}
}