<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\PostCategory;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PostCategoryFixtures extends BaseFixture
{
	/**
	 * @param  ObjectManager  $manager
	 */
	protected function loadData(ObjectManager $manager): void
	{
		$faker = Factory::create();
		
		$this->createMany(
			PostCategory::class, 12, function (PostCategory $category, $i) use ($faker) {
			$category->setTitle($faker->realText(20));
			$category->setSlug();
		}
		);
		
		$manager->flush();
	}
}