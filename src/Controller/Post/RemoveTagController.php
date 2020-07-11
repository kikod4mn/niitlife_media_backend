<?php

declare(strict_types = 1);

namespace App\Controller\Post;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use App\Security\Voter\PostVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RemoveTagController extends AbstractController
{
	use JsonNormalizedMessages;
	
	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $entityManager;
	
	/**
	 * @var PostRepository
	 */
	private PostRepository $postRepository;
	
	/**
	 * @var TagRepository
	 */
	private TagRepository $tagRepository;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		PostRepository $postRepository,
		TagRepository $tagRepository
	)
	{
		$this->entityManager  = $entityManager;
		$this->postRepository = $postRepository;
		$this->tagRepository  = $tagRepository;
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
		
		$post = $this->getPostRepository()->find($id);
		
		if (! $post) {
			
			return $this->jsonMessage(
				Response::HTTP_BAD_REQUEST,
				sprintf(
					'No tag found for id\'s "%s"',
					$tagId
				)
			);
		}
		
		$this->denyAccessUnlessGranted(PostVoter::EDIT, $post);
		
		$post->removeTag($tag);
		
		$this->getEntityManager()->flush();
		
		return $this->json(null, Response::HTTP_NO_CONTENT);
	}
	
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}
	
	public function getPostRepository(): PostRepository
	{
		return $this->postRepository;
	}
	
	public function getTagRepository(): TagRepository
	{
		return $this->tagRepository;
	}
}