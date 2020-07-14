<?php

declare(strict_types = 1);

namespace App\Controller\Tag;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Controller\Concerns\JsonNormalizedResponse;
use App\Entity\Event\SluggableCreatedEvent;
use App\Entity\User;
use App\Security\Voter\TagVoter;
use App\Service\EntityService\TagService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
	 * @var ValidatorInterface
	 */
	private ValidatorInterface $validator;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		EventDispatcherInterface $eventDispatcher,
		ValidatorInterface $validator
	)
	{
		$this->entityManager   = $entityManager;
		$this->eventDispatcher = $eventDispatcher;
		$this->validator       = $validator;
	}
	
	public function __invoke(Request $request): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$tag = TagService::create($request->getContent());
		
		$this->denyAccessUnlessGranted(TagVoter::CREATE, $tag);
		
		$violations = $this->getValidator()->validate($tag);
		
		if (count($violations) > 1) {
			
			return $this->jsonViolations($violations);
		}
		
		$this->eventDispatcher->dispatch(new SluggableCreatedEvent($tag));
		
		$this->getEntityManager()->persist($tag);
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($tag, ['tag:read']);
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
}