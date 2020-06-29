<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\ImageCategory;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ImageCategoryFixtures extends BaseFixture
{
	/**
	 * @param  ObjectManager  $manager
	 */
	protected function loadData(ObjectManager $manager): void
	{
		$faker = Factory::create();
		
		$this->createMany(
			ImageCategory::class, 5, function (ImageCategory $category, $i) use ($faker) {
			$category->setTitle($faker->word());
		}
		);
		
		$manager->flush();
	}
}