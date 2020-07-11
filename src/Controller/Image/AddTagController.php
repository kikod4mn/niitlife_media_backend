<?php

declare(strict_types = 1);

namespace App\Controller\Image;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Entity\User;
use App\Repository\ImageRepository;
use App\Repository\TagRepository;
use App\Security\Voter\ImageVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AddTagController extends AbstractController
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
	
	/**
	 * @var TagRepository
	 */
	private TagRepository $tagRepository;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		ImageRepository $imageRepository,
		TagRepository $tagRepository
	)
	{
		$this->entityManager   = $entityManager;
		$this->imageRepository = $imageRepository;
		$this->tagRepository   = $tagRepository;
	}
	
	public function __invoke(string $id, string $tagId): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$tag = $this->getTagRepository()->find($tagId);
		
		if (! $tag) {
			
			return $this->jsonMessage(
				Response::HTTP_BAD_REQUEST,
				sprintf(
					'No tag found for id\'s "%s"',
					$tagId
				)
			);
		}
		
		$image = $this->getImageRepository()->find($id);
		
		if (! $image) {
			
			return $this->jsonMessage(
				Response::HTTP_BAD_REQUEST,
				sprintf(
					'No image found for id\'s "%s"',
					$id
				)
			);
		}
		
		$this->denyAccessUnlessGranted(ImageVoter::EDIT, $image);
		
		$image->addTag($tag);
		
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
	
	public function getTagRepository(): TagRepository
	{
		return $this->tagRepository;
	}
}