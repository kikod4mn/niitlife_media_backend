<?php

declare(strict_types = 1);

namespace App\Controller\Image;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Entity\Contracts\Trashable;
use App\Entity\User;
use App\Repository\ImageRepository;
use App\Security\Voter\ImageVoter;
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
	 * @var ImageRepository
	 */
	private ImageRepository $imageRepository;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		ImageRepository $imageRepository
	)
	{
		$this->entityManager   = $entityManager;
		$this->imageRepository = $imageRepository;
	}
	
	public function __invoke(string $id): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$image = $this->getImageRepository()->find($id);
		
		if (! $image) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf('Image with id "%s" not found', $id)
			);
		}
		
		$this->denyAccessUnlessGranted(ImageVoter::DELETE, $image);
		
		if ($image instanceof Trashable) {
			
			if (! $image->isTrashed()) {
				
				return $this->jsonMessage(
					Response::HTTP_FORBIDDEN,
					'Post is not yet trashed. Either send the post to trash or use the forceable delete option.'
				);
			}
		}
		
		$this->getEntityManager()->remove($image);
		$this->getEntityManager()->flush();
		
		return $this->json(null, Response::HTTP_NO_CONTENT);
	}
	
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}
	
	public function getImageRepository(): ImageRepository
	{
		return $this->imageRepository;
	}
	
}