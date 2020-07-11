<?php

declare(strict_types = 1);

namespace App\Controller\ImageComment;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Controller\Concerns\JsonNormalizedResponse;
use App\Entity\Event\AuthorableCreatedEvent;
use App\Entity\Event\TimeStampableCreatedEvent;
use App\Entity\Event\UuidableCreatedEvent;
use App\Entity\User;
use App\Repository\ImageRepository;
use App\Security\Voter\ImageCommentVoter;
use App\Service\EntityService\ImageCommentService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateController extends AbstractController
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
	 * @var ImageRepository
	 */
	private ImageRepository $imageRepository;
	
	/**
	 * @var ValidatorInterface
	 */
	private ValidatorInterface $validator;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		EventDispatcherInterface $eventDispatcher,
		ImageRepository $imageRepository,
		ValidatorInterface $validator
	)
	{
		$this->entityManager   = $entityManager;
		$this->eventDispatcher = $eventDispatcher;
		$this->imageRepository = $imageRepository;
		$this->validator       = $validator;
	}
	
	public function __invoke(string $id, Request $request): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_COMMENTATOR);
		
		$post = $this->getImageRepository()->find($id);
		
		if (! $post) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf(
					'Post with id "%s" not found. Cannot post comment for a nonexistent post.',
					$id
				)
			);
		}
		
		$comment = ImageCommentService::create($request->getContent());
		
		$this->denyAccessUnlessGranted(ImageCommentVoter::CREATE, $comment);
		
		$violations = $this->getValidator()->validate($comment);
		
		if (count($violations) > 1) {
			
			return $this->jsonViolations($violations);
		}
		
		$comment->setPost($post);
		
		$this->getEventDispatcher()->dispatch(new AuthorableCreatedEvent($comment));
		$this->getEventDispatcher()->dispatch(new TimeStampableCreatedEvent($comment));
		$this->getEventDispatcher()->dispatch(new UuidableCreatedEvent($comment));
		
		$this->getEntityManager()->persist($comment);
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($post, ['image:read']);
	}
	
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}
	
	public function getEventDispatcher(): EventDispatcherInterface
	{
		return $this->eventDispatcher;
	}
	
	public function getImageRepository(): ImageRepository
	{
		return $this->imageRepository;
	}
	
	public function getValidator(): ValidatorInterface
	{
		return $this->validator;
	}
}