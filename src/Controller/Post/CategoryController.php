<?php

declare(strict_types = 1);

namespace App\Controller\Post;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Controller\Concerns\JsonNormalizedResponse;
use App\Entity\User;
use App\Repository\PostCategoryRepository;
use App\Repository\PostRepository;
use App\Security\Voter\PostVoter;
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
	 * @var PostRepository
	 */
	private PostRepository $postRepository;
	
	/**
	 * @var PostCategoryRepository
	 */
	private PostCategoryRepository $categoryRepository;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		PostRepository $postRepository,
		PostCategoryRepository $categoryRepository
	)
	{
		$this->entityManager      = $entityManager;
		$this->postRepository     = $postRepository;
		$this->categoryRepository = $categoryRepository;
	}
	
	public function __invoke(string $id, Request $request): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$catId = (json_decode($request->getContent()))->catId ?? null;
		
		$category = $this->getPostCategoryRepository()->find($catId);
		
		if (! $category) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf('Category with id "%s" not found', $catId)
			);
		}
		
		$post = $this->getPostRepository()->find($id);
		
		if (! $post) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf('Post with id "%s" not found', $id)
			);
		}
		
		$this->denyAccessUnlessGranted(PostVoter::EDIT, $post);
		
		$post->setCategory($category);
		
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($post, ['post:read']);
	}
	
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}
	
	public function getPostRepository(): PostRepository
	{
		return $this->postRepository;
	}
	
	public function getPostCategoryRepository(): PostCategoryRepository
	{
		return $this->categoryRepository;
	}
}