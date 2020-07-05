<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\DataFixtures\Concerns\GeneratesRandomColors;
use App\Entity\ImageCategory;
use App\Entity\Image;
use App\Repository\UserRepository;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ImageFixtures extends BaseFixture implements DependentFixtureInterface
{
	use GeneratesRandomColors;
	
	/**
	 * @var UserRepository
	 */
	private UserRepository $userRepository;
	
	/**
	 * ImageFixtures constructor.
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
		$user = $this->userRepository->findOneBy(['username' => 'kiko']);
		
		$faker      = Factory::create();
		$thumbSizes = [120, 150, 170, 190, 200, 250];
		$origSizes  = [900, 1000, 800, 1300];
		
		$this->createMany(
			Image::class, 150, function (Image $image, $i) use ($faker, $user, $origSizes, $thumbSizes) {
			// Simulate site owner only uploading images.
			$image->setAuthor($user);
			$image->setTitle($faker->realText(200));
			$image->setSlug();
			$image->setDescription($faker->realText(500));
			$image->setCategory($this->getRandomReference(ImageCategory::class));
			$thumbWidth  = (int) $thumbSizes[rand(0, count($thumbSizes) - 1)];
			$thumbHeight = $thumbWidth + (mt_rand(0, 1) ? 100 : 0);
			$origWidth   = (int) $origSizes[rand(0, count($origSizes) - 1)];
			$origHeight  = $origWidth + (mt_rand(0, 1) ? 500 : 0);
			$bgCol       = $this->randomHexColor();
			$textCol     = $this->randomHexColor();
			$image->setThumbnail("https://dummyimage.com/{$thumbWidth}x{$thumbHeight}/{$bgCol}/{$textCol}");
			$image->setOriginal("https://dummyimage.com/{$origWidth}x{$origHeight}/{$bgCol}/{$textCol}");
			$image->setCreationTimestamps();
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
		];
	}
}