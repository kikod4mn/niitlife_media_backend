<?php

declare(strict_types = 1);

namespace App\Controller\ImageCategory;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Entity\Contracts\Trashable;
use App\Entity\User;
use App\Repository\ImageCategoryRepository;
use App\Security\Voter\ImageCategoryVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DestroyController extends AbstractController
{
	use JsonNormalizedMessages;
	
	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $entityManager;
	
	/**
	 * @var ImageCategoryRepository
	 */
	private ImageCategoryRepository $categoryRepository;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		ImageCategoryRepository $categoryRepository
	)
	{
		$this->entityManager      = $entityManager;
		$this->categoryRepository = $categoryRepository;
	}
	
	public function __invoke(string $id): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$category = $this->getCategoryRepository()->find($id);
		
		if (! $category) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf(
					'Category with id "%s" not found.',
					$id
				)
			);
		}
		
		$this->denyAccessUnlessGranted(ImageCategoryVoter::RESTORE, $category);
		
		if ($category instanceof Trashable) {
			
			if (! $category->isTrashed()) {
				
				return $this->jsonMessage(
					Response::HTTP_FORBIDDEN,
					'Category is not yet trashed. Either send the post to trash or use the forceable delete option.'
				);
			}
		}
		
		$this->getEntityManager()->remove($category);
		$this->getEntityManager()->flush();
		
		return $this->json(null, Response::HTTP_NO_CONTENT);
	}
	
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}
	
	public function getCategoryRepository(): ImageCategoryRepository
	{
		return $this->categoryRepository;
	}
}