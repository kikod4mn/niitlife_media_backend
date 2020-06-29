<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\User;
use App\Repository\ImageRepository;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;

class ImageLikesFixtures extends BaseFixture implements DependentFixtureInterface
{
	/**
	 * @var ImageRepository
	 */
	private ImageRepository $imageRepository;
	
	/**
	 * ImageLikesFixtures constructor.
	 * @param  ImageRepository  $imageRepository
	 */
	public function __construct(ImageRepository $imageRepository)
	{
		$this->imageRepository = $imageRepository;
	}
	
	/**
	 * @param  ObjectManager  $manager
	 * @throws Exception
	 */
	protected function loadData(ObjectManager $manager): void
	{
		$images = $this->imageRepository->findAll();
		
		foreach ($images as $image) {
			for ($i = 0; $i < mt_rand(5, 40); $i++) {
				$image->like($this->getRandomReference(User::class));
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
			ImageCategoryFixtures::class,
			ImageFixtures::class,
		];
	}
}