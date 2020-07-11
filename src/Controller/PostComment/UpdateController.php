<?php

declare(strict_types = 1);

namespace App\Controller\PostComment;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Controller\Concerns\JsonNormalizedResponse;
use App\Entity\Event\TimeStampableUpdatedEvent;
use App\Entity\User;
use App\Repository\PostCommentRepository;
use App\Security\Voter\PostCommentVoter;
use App\Service\EntityService\PostCommentService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateController extends AbstractController
{
	use JsonNormalizedResponse, JsonNormalizedMessages;
	
	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $entityManager;
	
	/**
	 * @var EventDispatcherInterface
	 */
	private EventDispatcherInterface $eventDispatcher;
	
	/**
	 * @var PostCommentRepository
	 */
	private PostCommentRepository $commentRepository;
	
	/**
	 * @var ValidatorInterface
	 */
	private ValidatorInterface $validator;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		EventDispatcherInterface $eventDispatcher,
		PostCommentRepository $commentRepository,
		ValidatorInterface $validator
	)
	{
		$this->entityManager     = $entityManager;
		$this->eventDispatcher   = $eventDispatcher;
		$this->commentRepository = $commentRepository;
		$this->validator         = $validator;
	}
	
	public function __invoke(string $id, Request $request): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_COMMENTATOR);
		
		$comment = $this->getCommentRepository()->find($id);
		
		if (! $comment) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf('Post with id "%s" not found', $id)
			);
		}
		
		$comment = PostCommentService::update($request->getContent(), $comment);
		
		$this->denyAccessUnlessGranted(PostCommentVoter::EDIT, $comment);
		
		$violations = $this->getValidator()->validate($comment);
		
		if (count($violations) > 1) {
			
			return $this->jsonViolations($violations);
		}
		
		$this->getEventDispatcher()->dispatch(new TimeStampableUpdatedEvent($comment));
		
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($comment->getPost(), ['post:read']);
	}
	
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}
	
	public function getEventDispatcher(): EventDispatcherInterface
	{
		return $this->eventDispatcher;
	}
	
	public function getCommentRepository(): PostCommentRepository
	{
		return $this->commentRepository;
	}
	
	public function getValidator(): ValidatorInterface
	{
		return $this->validator;
	}
}