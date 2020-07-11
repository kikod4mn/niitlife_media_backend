<?php

declare(strict_types = 1);

namespace App\Controller\Post;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Controller\Concerns\JsonNormalizedResponse;
use App\Entity\Event\TimeStampableUpdatedEvent;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Security\Voter\PostVoter;
use App\Service\EntityService\PostService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateController extends AbstractController
{
	use JsonNormalizedMessages, JsonNormalizedResponse;
	
	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $entityManager;
	
	/**
	 * @var EventDispatcherInterface
	 */
	private EventDispatcherInterface $eventDispatcher;
	
	/**
	 * @var ValidatorInterface
	 */
	private ValidatorInterface $validator;
	
	/**
	 * @var PostRepository
	 */
	private PostRepository $postRepository;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		EventDispatcherInterface $eventDispatcher,
		ValidatorInterface $validator,
		PostRepository $postRepository
	)
	{
		$this->entityManager   = $entityManager;
		$this->eventDispatcher = $eventDispatcher;
		$this->validator       = $validator;
		$this->postRepository  = $postRepository;
	}
	
	public function __invoke(string $id, Request $request): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$post = $this->getPostRepository()->find($id);
		
		if (! $post) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf('Post with id "%s" not found', $id)
			);
		}
		
		$this->denyAccessUnlessGranted(PostVoter::EDIT, $post);
		
		$post = PostService::update($request->getContent(), $post);
		
		$violations = $this->getValidator()->validate($post);
		
		if (count($violations) > 1) {
			
			return $this->jsonViolations($violations);
		}
		
		$this->getEventDispatcher()->dispatch(new TimeStampableUpdatedEvent($post));
		
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($post, ['post:read']);
	}
	
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}
	
	public function getEventDispatcher(): EventDispatcherInterface
	{
		return $this->eventDispatcher;
	}
	
	public function getValidator(): ValidatorInterface
	{
		return $this->validator;
	}
	
	public function getPostRepository(): PostRepository
	{
		return $this->postRepository;
	}
}