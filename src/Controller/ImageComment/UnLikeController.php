<?php

declare(strict_types = 1);

namespace App\Controller\ImageComment;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Controller\Concerns\JsonNormalizedResponse;
use App\Entity\User;
use App\Repository\ImageCommentRepository;
use App\Security\Voter\ImageCommentVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UnLikeController extends AbstractController
{
	use JsonNormalizedMessages, JsonNormalizedResponse;
	
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
		
		$this->denyAccessUnlessGranted(ImageCommentVoter::VIEW, $comment);
		
		$comment->unlike($this->getUser());
		
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized(['likeCount' => $comment->getLikeCount()]);
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