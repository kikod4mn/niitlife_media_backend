<?php

declare(strict_types = 1);

namespace App\Controller\Image;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Controller\Concerns\JsonNormalizedResponse;
use App\Entity\Event\AuthorableCreatedEvent;
use App\Entity\Event\SluggableCreatedEvent;
use App\Entity\Event\TimeStampableCreatedEvent;
use App\Entity\Event\UuidableCreatedEvent;
use App\Entity\Image;
use App\Entity\User;
use App\Security\Voter\ImageVoter;
use App\Service\EntityService\ImageService;
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
		
		/** @var Image $image */
		$image = ImageService::create($request->getContent());
		
		$this->denyAccessUnlessGranted(ImageVoter::CREATE, $image);
		
		$violations = $this->getValidator()->validate($image);
		
		if (count($violations) > 1) {
			
			return $this->jsonViolations($violations);
		}
		
		$this->getEventDispatcher()->dispatch(new SluggableCreatedEvent($image));
		$this->getEventDispatcher()->dispatch(new AuthorableCreatedEvent($image));
		$this->getEventDispatcher()->dispatch(new TimeStampableCreatedEvent($image));
		$this->getEventDispatcher()->dispatch(new UuidableCreatedEvent($image));
		
		$this->getEntityManager()->persist($image);
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($image, ['image:read']);
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