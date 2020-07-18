<?php

declare(strict_types = 1);

namespace App\Controller\Security;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Entity\Event\UserPasswordChangedEvent;
use App\Repository\UserRepository;
use App\Support\Validate;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PasswordChangeVerifyTokenController extends AbstractController
{
	use JsonNormalizedMessages;
	
	/**
	 * @var UserRepository
	 */
	private UserRepository $userRepository;
	
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
		UserRepository $userRepository,
		EntityManagerInterface $entityManager,
		EventDispatcherInterface $eventDispatcher,
		ValidatorInterface $validator
	)
	{
		$this->userRepository  = $userRepository;
		$this->entityManager   = $entityManager;
		$this->eventDispatcher = $eventDispatcher;
		$this->validator       = $validator;
	}
	
	public function __invoke(string $token, Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_ANONYMOUSLY');
		
		$user = $this->getUserRepository()->findOneBy(['passwordResetToken' => $token]);
		
		// Not using factory to only allow changing password fields at this controller.
		$plainPassword        = (json_decode($request->getContent()))->plainPassword ?? '';
		$retypedPlainPassword = (json_decode($request->getContent()))->retypedPlainPassword ?? '';
		
		if (null === $user || Validate::blank($plainPassword) || Validate::blank($retypedPlainPassword)) {
			
			return $this->jsonDefaultError();
		}
		
		$user->setPlainPassword($plainPassword);
		$user->setRetypedPlainPassword($retypedPlainPassword);
		
		$violations = $this->getValidator()->validate($user);
		
		if (count($violations) > 0) {
			
			return $this->jsonViolations($violations);
		}
		
		$this->getEventDispatcher()->dispatch(new UserPasswordChangedEvent($user));
		
		$this->getEntityManager()->flush();
		
		return $this->json(null, Response::HTTP_NO_CONTENT);
	}
	
	public function getUserRepository(): UserRepository
	{
		return $this->userRepository;
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