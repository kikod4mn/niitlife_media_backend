<?php

declare(strict_types = 1);

namespace App\Controller\ImageComment;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Entity\Contracts\Trashable;
use App\Entity\User;
use App\Repository\ImageCommentRepository;
use App\Security\Voter\ImageCommentVoter;
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
	 * @var ImageCommentRepository
	 */
	private ImageCommentRepository $commentRepository;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		ImageCommentRepository $commentRepository
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
		
		$this->denyAccessUnlessGranted(ImageCommentVoter::DELETE, $comment);
		
		if ($comment instanceof Trashable) {
			
			if (! $comment->isTrashed()) {
				
				return $this->jsonMessage(
					Response::HTTP_FORBIDDEN,
					'Post is not yet trashed. Either send the post to trash or use the forceable delete option.'
				);
			}
		}
		
		$this->getEntityManager()->remove($comment);
		$this->getEntityManager()->flush();
		
		return $this->json(null, Response::HTTP_NO_CONTENT);
	}
	
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}
	
	public function getCommentRepository(): ImageCommentRepository
	{
		return $this->commentRepository;
	}
}