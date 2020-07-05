<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PostCommentFixtures extends BaseFixture implements DependentFixtureInterface
{
	/**
	 * @param  ObjectManager  $manager
	 */
	protected function loadData(ObjectManager $manager): void
	{
		$faker = Factory::create();
		
		$this->createMany(
			PostComment::class, 250, function (PostComment $comment, $i) use ($faker) {
			$comment->setAuthor($this->getRandomReference(User::class));
			$comment->setPost($this->getRandomReference(Post::class));
			$comment->setBody($faker->realText(250));
			$comment->setCreationTimestamps();
		}
		);
		
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