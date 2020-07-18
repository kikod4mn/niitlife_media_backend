<?php

declare(strict_types = 1);

namespace App\Controller\Security;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Controller\Concerns\UserUniqueCheck;
use App\Entity\Event\UserCreatedEvent;
use App\Repository\UserRepository;
use App\Security\Voter\UserVoter;
use App\Service\EntityService\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

class UserRegisterController extends AbstractController
{
	use JsonNormalizedMessages, UserUniqueCheck;
	
	/**
	 * @var EventDispatcherInterface
	 */
	private EventDispatcherInterface $eventDispatcher;
	
	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $entityManager;
	
	/**
	 * @var UserRepository
	 */
	private UserRepository $userRepository;
	
	/**
	 * @var ValidatorInterface
	 */
	private ValidatorInterface $validator;
	
	/**
	 * UserRegisterController constructor.
	 * @param  EntityManagerInterface    $entityManager
	 * @param  EventDispatcherInterface  $eventDispatcher
	 * @param  UserRepository            $userRepository
	 * @param  ValidatorInterface        $validator
	 */
	public function __construct(
		EntityManagerInterface $entityManager,
		EventDispatcherInterface $eventDispatcher,
		UserRepository $userRepository,
		ValidatorInterface $validator
	)
	{
		$this->entityManager   = $entityManager;
		$this->eventDispatcher = $eventDispatcher;
		$this->userRepository  = $userRepository;
		$this->validator       = $validator;
	}
	
	/**
	 * @param  Request  $request
	 * @return JsonResponse
	 */
	public function __invoke(Request $request): JsonResponse
	{
		if (! $this->isGranted('IS_AUTHENTICATED_ANONYMOUSLY')) {
			
			return $this->jsonMessage(
				Response::HTTP_NO_CONTENT,
				'You are already registered.'
			);
		}
		
		try {
			
			$user = UserService::create($request->getContent());
			
		} catch (Throwable $e) {
			
			return $this->jsonDefaultError();
		}
		
		$violations = $this->getValidator()->validate($user);
		
		if (count($violations) > 1) {
			
			return $this->jsonViolations($violations);
		}
		
		if (! $this->isGranted(UserVoter::CREATE, $user)) {
			
			return $this->jsonMessage(
				Response::HTTP_UNAUTHORIZED,
				'Cannot create user. Contact administrator.'
			);
		}
		
		$errors = $this->uniqueCheck($user);
		
		if (count($errors) > 0) {
			
			return $this->jsonMessage(
				Response::HTTP_BAD_REQUEST,
				implode(', ', $errors)
			);
		}
		
		$this->getEventDispatcher()->dispatch(new UserCreatedEvent($user));
		
		$this->getEntityManager()->persist($user);
		$this->getEntityManager()->flush();
		
		return $this->jsonMessage(
			Response::HTTP_OK,
			'Successfully registered. You may now login!'
		);
	}
	
	/**
	 * @return EventDispatcherInterface
	 */
	public function getEventDispatcher(): EventDispatcherInterface
	{
		return $this->eventDispatcher;
	}
	
	/**
	 * @return EntityManagerInterface
	 */
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}
	
	/**
	 * @return UserRepository
	 */
	public function getUserRepository(): UserRepository
	{
		return $this->userRepository;
	}
	
	public function getValidator(): ValidatorInterface
	{
		return $this->validator;
	}
}