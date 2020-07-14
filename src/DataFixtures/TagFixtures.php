<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TagFixtures extends BaseFixture implements DependentFixtureInterface
{
	/**
	 * @param  ObjectManager  $manager
	 */
	protected function loadData(ObjectManager $manager): void
	{
		$faker = Factory::create();
		
		$this->createMany(
			Tag::class, 12, function (Tag $tag, $i) use ($faker) {
			$tag->setTitle($faker->word);
			$tag->setSlug();
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
		];
	}
}