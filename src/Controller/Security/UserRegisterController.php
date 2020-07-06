<?php

declare(strict_types = 1);

namespace App\Controller\Security;

use App\Controller\Concerns\ManagesEntities;
use App\Controller\Concerns\SendsJsonMessages;
use App\Controller\Concerns\NormalizesJson;
use App\Controller\Concerns\UsesXmlMapping;
use App\Entity\Event\UserCreatedEvent;
use App\Entity\Factory\UserFactory;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\Voter\UserVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

class UserRegisterController extends AbstractController
{
	use SendsJsonMessages;
	
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
	 * UserRegisterController constructor.
	 * @param  string                    $projectDir
	 * @param  EntityManagerInterface    $entityManager
	 * @param  EventDispatcherInterface  $eventDispatcher
	 * @param  UserRepository            $userRepository
	 */
	public function __construct(
		string $projectDir,
		EntityManagerInterface $entityManager,
		EventDispatcherInterface $eventDispatcher,
		UserRepository $userRepository
	)
	{
		$this->entityManager   = $entityManager;
		$this->eventDispatcher = $eventDispatcher;
		$this->userRepository  = $userRepository;
	}
	
	/**
	 * @param  Request  $request
	 * @return JsonResponse
	 */
	public function __invoke(Request $request): JsonResponse
	{
		if (! $this->isGranted('IS_AUTHENTICATED_ANONYMOUSLY')) {
			
			return $this->jsonMessage(Response::HTTP_NO_CONTENT, 'You are already registered.');
		}
		
		try {
			
			$user = UserFactory::make($request->getContent());
		} catch (Throwable $e) {
			
			return $this->jsonMessage(Response::HTTP_BAD_REQUEST, $e->getMessage());
		}
		
		if (! $this->isGranted(UserVoter::CREATE, $user)) {
			
			return $this->jsonMessage(Response::HTTP_UNAUTHORIZED, 'Cannot create user. Contact administrator.');
		}
		
		$errors = $this->uniqueCheck($user);
		
		if (count($errors) > 0) {
			
			return $this->jsonMessage(Response::HTTP_BAD_REQUEST, implode(', ', $errors));
		}
		
		$this->getEventDispatcher()->dispatch(new UserCreatedEvent($user));
		
		$this->getEntityManager()->persist($user);
		$this->getEntityManager()->flush();
		
		return $this->jsonMessage(Response::HTTP_OK, 'Successfully registered. You may now login!');
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
	
	/**
	 * Check an un persisted User object against the DB for username and email uniqueness.
	 * @param  User  $user
	 * @return array
	 */
	private function uniqueCheck(User $user): array
	{
		$errors   = [];
		$username = $this->getUserRepository()->findOneBy(['username' => $user->getUsername()]);
		$email    = $this->getUserRepository()->findOneBy(['email' => $user->getEmail()]);
		
		if ($username) {
			array_push($errors, 'Username is already in use. Please choose another.');
		}
		
		if ($email) {
			array_push($errors, 'Email is already in use. Have You forgotten your password?');
		}
		
		return $errors;
	}
}