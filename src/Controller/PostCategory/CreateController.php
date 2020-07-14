<?php

declare(strict_types = 1);

namespace App\Controller\PostCategory;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Controller\Concerns\JsonNormalizedResponse;
use App\Entity\Event\SluggableCreatedEvent;
use App\Entity\User;
use App\Security\Voter\PostCategoryVoter;
use App\Service\EntityService\PostCategoryService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateController extends AbstractController
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
	
	public function __invoke(string $id, Request $request): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$category = PostCategoryService::create($request->getContent());
		
		$this->denyAccessUnlessGranted(PostCategoryVoter::CREATE, $category);
		
		$violations = $this->getValidator()->validate($category);
		
		if (count($violations) > 1) {
			
			return $this->jsonViolations($violations);
		}
		
		$this->getEventDispatcher()->dispatch(new SluggableCreatedEvent($category));
		
		$this->getEntityManager()->persist($category);
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($category, ['category:read']);
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