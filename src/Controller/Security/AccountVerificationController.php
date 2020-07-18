<?php

declare(strict_types = 1);

namespace App\Controller\Security;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class AccountVerificationController extends AbstractController
{
	use JsonNormalizedMessages;
	
	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $entityManager;
	
	/**
	 * @var UserRepository
	 */
	private UserRepository $userRepository;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		UserRepository $userRepository
	)
	{
		$this->entityManager  = $entityManager;
		$this->userRepository = $userRepository;
	}
	
	public function __invoke(string $token)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_ANONYMOUSLY');
		
		$user = $this->getUserRepository()->findOneBy(['activationToken' => $token]);
		
		if (! $user) {
			
			return $this->jsonDefaultError();
		}
		
		$user->activate();
		$user->setActivationToken(null);
		
		$this->getEntityManager()->flush();
		
		return $this->json(null, Response::HTTP_NO_CONTENT);
	}
	
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}
	
	public function getUserRepository(): UserRepository
	{
		return $this->userRepository;
	}
}