<?php

declare(strict_types = 1);

namespace App\Controller\PostComment;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Entity\Contracts\Trashable;
use App\Entity\User;
use App\Repository\PostCommentRepository;
use App\Security\Voter\PostCommentVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UnTrashController extends AbstractController
{
	use JsonNormalizedMessages;
	
	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $entityManager;
	
	/**
	 * @var PostCommentRepository
	 */
	private PostCommentRepository $commentRepository;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		PostCommentRepository $commentRepository
	)
	{
		$this->entityManager     = $entityManager;
		$this->commentRepository = $commentRepository;
	}
	
	public function __invoke(string $id): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_COMMENTATOR);
		
		$comment = $this->getCommentRepository()->find($id);
		
		if (! $comment) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf(
					'Comment with id "%s" not found',
					$id
				)
			);
		}
		
		$this->denyAccessUnlessGranted(PostCommentVoter::TRASH, $comment);
		
		if ($comment instanceof Trashable) {
			
			$comment->setTrashedAt(null);
		}
		
		$this->getEntityManager()->flush();
		
		return $this->json(null, Response::HTTP_NO_CONTENT);
	}
	
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}
	
	public function getCommentRepository(): PostCommentRepository
	{
		return $this->commentRepository;
	}
}