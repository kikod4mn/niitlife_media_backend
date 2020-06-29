<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\ImageComment;
use App\Entity\Image;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ImageCommentFixtures extends BaseFixture implements DependentFixtureInterface
{
	/**
	 * @param  ObjectManager  $manager
	 */
	protected function loadData(ObjectManager $manager): void
	{
		$faker = Factory::create();
		
		$this->createMany(
			ImageComment::class, 250, function (ImageComment $comment, $i) use ($faker) {
			$comment->setAuthor($this->getRandomReference(User::class));
			$comment->setImage($this->getRandomReference(Image::class));
			$comment->setBody($faker->realText(250));
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
			ImageCategoryFixtures::class,
			ImageFixtures::class,
		];
	}
}