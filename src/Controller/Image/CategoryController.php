<?php

declare(strict_types = 1);

namespace App\Controller\Image;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Controller\Concerns\JsonNormalizedResponse;
use App\Entity\User;
use App\Repository\ImageCategoryRepository;
use App\Repository\ImageRepository;
use App\Security\Voter\ImageVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends AbstractController
{
	use JsonNormalizedResponse, JsonNormalizedMessages;
	
	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $entityManager;
	
	/**
	 * @var ImageRepository
	 */
	private ImageRepository $imageRepository;
	
	/**
	 * @var ImageCategoryRepository
	 */
	private ImageCategoryRepository $categoryRepository;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		ImageRepository $imageRepository,
		ImageCategoryRepository $categoryRepository
	)
	{
		$this->entityManager      = $entityManager;
		$this->imageRepository    = $imageRepository;
		$this->categoryRepository = $categoryRepository;
	}
	
	public function __invoke(string $id, Request $request): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$catId = (json_decode($request->getContent()))->catId ?? null;
		
		$category = $this->getImageCategoryRepository()->find($catId);
		
		if (! $category) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf(
					'Category with id "%s" not found',
					$catId
				)
			);
		}
		
		$image = $this->getImageRepository()->find($id);
		
		if (! $image) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf(
					'Image with id "%s" not found',
					$id
				)
			);
		}
		
		$this->denyAccessUnlessGranted(ImageVoter::EDIT, $image);
		
		$image->setCategory($category);
		
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($image, ['image:read']);
	}
	
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}
	
	public function getImageRepository(): ImageRepository
	{
		return $this->imageRepository;
	}
	
	public function getImageCategoryRepository(): ImageCategoryRepository
	{
		return $this->categoryRepository;
	}
}