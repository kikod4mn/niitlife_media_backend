<?php

declare(strict_types = 1);

namespace App\Controller\ImageCategory;

use App\Controller\Concerns\JsonNormalizedResponse;
use App\Repository\ImageCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class ListController extends AbstractController
{
	use JsonNormalizedResponse;
	
	/**
	 * @var ImageCategoryRepository
	 */
	private ImageCategoryRepository $categoryRepository;
	
	/**
	 * @var SerializerInterface
	 */
	private SerializerInterface $serializer;
	
	public function __construct(
		ImageCategoryRepository $categoryRepository,
		SerializerInterface $serializer
	)
	{
		$this->categoryRepository = $categoryRepository;
		$this->serializer         = $serializer;
	}
	
	public function __invoke(): JsonResponse
	{
		return $this->jsonNormalized(
			$this->getCategoryRepository()->findAll(),
			['postCategory:list']
		);
	}
	
	public function getCategoryRepository(): ImageCategoryRepository
	{
		return $this->categoryRepository;
	}
	
	public function getSerializer(): SerializerInterface
	{
		return $this->serializer;
	}
}