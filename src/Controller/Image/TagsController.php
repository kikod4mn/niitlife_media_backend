<?php

declare(strict_types = 1);

namespace App\Controller\Image;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Controller\Concerns\JsonNormalizedResponse;
use App\Entity\User;
use App\Repository\ImageRepository;
use App\Repository\TagRepository;
use App\Security\Voter\ImageVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TagsController extends AbstractController
{
	use JsonNormalizedMessages, JsonNormalizedResponse;
	
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
	
	public function __invoke(string $id, Request $request): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$tagIds = (json_decode($request->getContent()))->tagIds ?? null;
		
		$tags = $this->getTagRepository()->findBy(['id' => $tagIds]);
		
		if (! $tags || count($tags) === 0) {
			
			return $this->jsonMessage(
				Response::HTTP_BAD_REQUEST,
				sprintf(
					'No tags found for id\'s "%s"',
					implode(', ', $tagIds)
				)
			);
		}
		
		$image = $this->getImageRepository()->find($id);
		
		if (! $image) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf(
					'Post with id "%s" not found',
					$id
				)
			);
		}
		
		$this->denyAccessUnlessGranted(ImageVoter::EDIT, $image);
		
		$image->setTags($tags);
		
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
	
	public function getTagRepository(): TagRepository
	{
		return $this->tagRepository;
	}
}