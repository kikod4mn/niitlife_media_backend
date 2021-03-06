<?php

declare(strict_types = 1);

namespace App\Controller\Post;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Entity\Contracts\Trashable;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Security\Voter\PostVoter;
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
	 * @var PostRepository
	 */
	private PostRepository $postRepository;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		PostRepository $postRepository
	)
	{
		$this->entityManager  = $entityManager;
		$this->postRepository = $postRepository;
	}
	
	public function __invoke(string $id): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$post = $this->getPostRepository()->find($id);
		
		if (! $post) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf('Post with id "%s" not found', $id)
			);
		}
		
		$this->denyAccessUnlessGranted(PostVoter::DELETE, $post);
		
		if ($post instanceof Trashable) {
			
			if (! $post->isTrashed()) {
				
				return $this->jsonMessage(
					Response::HTTP_FORBIDDEN,
					'Post is not yet trashed. Either send the post to trash or use the forceable delete option.'
				);
			}
		}
		
		$this->getEntityManager()->remove($post);
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
	
}