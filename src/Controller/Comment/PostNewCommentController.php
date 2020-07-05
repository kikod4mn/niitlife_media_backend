<?php

declare(strict_types = 1);

namespace App\Controller\Comment;

use App\Entity\PostComment;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Security;

class PostNewCommentController
{
	/**
	 * @var PostRepository
	 */
	private PostRepository $postRepository;
	
	/**
	 * @var Security
	 */
	private Security $security;
	
	/**
	 * PostNewCommentController constructor.
	 * @param  PostRepository  $postRepository
	 * @param  Security        $security
	 */
	public function __construct(PostRepository $postRepository, Security $security)
	{
		$this->postRepository = $postRepository;
		$this->security       = $security;
	}
	
	/**
	 * @param  PostComment  $data
	 * @param  string       $id
	 * @return PostComment
	 * @throws NotFoundHttpException
	 */
	public function __invoke(PostComment $data, string $id)
	{
		$post = $this->postRepository->findOneBy(['id' => $id]);
		
		if (! $post) {
			
			throw new NotFoundHttpException();
		}
		
		$data->setPost($post);
		
		return $data;
	}
}