<?php

declare(strict_types = 1);

namespace App\Controller\Tag;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Controller\Concerns\JsonNormalizedResponse;
use App\Entity\Event\SluggableCreatedEvent;
use App\Entity\Event\SluggableEditedEvent;
use App\Entity\User;
use App\Repository\TagRepository;
use App\Security\Voter\TagVoter;
use App\Service\EntityService\TagService;
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
	 * @var ValidatorInterface
	 */
	private ValidatorInterface $validator;
	
	/**
	 * @var TagRepository
	 */
	private TagRepository $tagRepository;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		EventDispatcherInterface $eventDispatcher,
		ValidatorInterface $validator,
		TagRepository $tagRepository
	)
	{
		$this->entityManager   = $entityManager;
		$this->eventDispatcher = $eventDispatcher;
		$this->validator       = $validator;
		$this->tagRepository   = $tagRepository;
	}
	
	public function __invoke(string $id, Request $request): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$tag = $this->getTagRepository()->find($id);
		
		if (! $tag) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf(
					'Tag with id "%s" not found.',
					$id
				)
			);
		}
		
		$this->denyAccessUnlessGranted(TagVoter::EDIT, $tag);
		
		$tag = TagService::update($request->getContent(), $tag);
		
		$violations = $this->getValidator()->validate($tag);
		
		if (count($violations) > 1) {
			
			return $this->jsonViolations($violations);
		}
		
		$this->getEventDispatcher()->dispatch(new SluggableEditedEvent($tag));
		
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
	
	public function getTagRepository(): TagRepository
	{
		return $this->tagRepository;
	}
}