<?php

declare(strict_types = 1);

namespace App\Controller\Account;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Controller\Concerns\JsonNormalizedResponse;
use App\Controller\Concerns\UserUniqueCheck;
use App\Entity\Event\UserPasswordChangedEvent;
use App\Repository\UserRepository;
use App\Security\Voter\UserVoter;
use App\Service\EntityService\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

class UpdateController extends AbstractController
{
	use JsonNormalizedResponse, JsonNormalizedMessages, UserUniqueCheck;
	
	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $entityManager;
	
	/**
	 * @var UserRepository
	 */
	private UserRepository $userRepository;
	
	/**
	 * @var SerializerInterface
	 */
	private SerializerInterface $serializer;
	
	/**
	 * @var ValidatorInterface
	 */
	private ValidatorInterface $validator;
	
	/**
	 * @var EventDispatcherInterface
	 */
	private EventDispatcherInterface $eventDispatcher;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		UserRepository $userRepository,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		EventDispatcherInterface $eventDispatcher
	)
	{
		$this->entityManager   = $entityManager;
		$this->userRepository  = $userRepository;
		$this->serializer      = $serializer;
		$this->validator       = $validator;
		$this->eventDispatcher = $eventDispatcher;
	}
	
	public function __invoke(Request $request): JsonResponse
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
		
		$currentUser = $this->getUser();
		
		$this->denyAccessUnlessGranted(UserVoter::EDIT, $currentUser);
		
		try {
			
			$user = UserService::update($request->getContent(), $currentUser);
			
		} catch (Throwable $e) {
			
			return $this->jsonDefaultError();
		}
		
		$violations = $this->getValidator()->validate($user);
		
		if (count($violations) > 1) {
			
			return $this->jsonViolations($violations);
		}
		
		$errors = $this->uniqueCheck($user);
		
		if (count($errors) > 0) {
			
			return $this->jsonMessage(
				Response::HTTP_BAD_REQUEST,
				implode(', ', $errors)
			);
		}
		
		if (null !== $user->getPlainPassword() && null !== $user->getRetypedPlainPassword()) {
			
			$this->getEventDispatcher()->dispatch(new UserPasswordChangedEvent($user));
		}
		
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($user, ['user:self:read']);
	}
	
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}
	
	public function getUserRepository(): UserRepository
	{
		return $this->userRepository;
	}
	
	public function getSerializer(): SerializerInterface
	{
		return $this->serializer;
	}
	
	public function getValidator(): ValidatorInterface
	{
		return $this->validator;
	}
	
	public function getEventDispatcher(): EventDispatcherInterface
	{
		return $this->eventDispatcher;
	}
}