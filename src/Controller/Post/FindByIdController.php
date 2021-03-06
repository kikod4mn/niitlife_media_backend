<?php

declare(strict_types = 1);

namespace App\Controller\Post;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Controller\Concerns\JsonNormalizedResponse;
use App\Repository\PostRepository;
use App\Security\Voter\PostVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FindByIdController extends AbstractController
{
	use JsonNormalizedMessages, JsonNormalizedResponse;
	
	/**
	 * @var PostRepository
	 */
	private PostRepository $postRepository;
	
	public function __construct(PostRepository $postRepository)
	{
		$this->postRepository = $postRepository;
	}
	
	public function __invoke(string $id): JsonResponse
	{
		$post = $this->getPostRepository()->find($id);
		
		if (! $post) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf('Post with id "%s" not found', $id)
			);
		}
		
		$this->denyAccessUnlessGranted(PostVoter::VIEW, $post);
		
		return $this->jsonNormalized($post, ['post:read']);
	}
	
	public function getPostRepository(): PostRepository
	{
		return $this->postRepository;
	}
}