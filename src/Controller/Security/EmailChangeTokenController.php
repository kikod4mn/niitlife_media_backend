<?php

declare(strict_types = 1);

namespace App\Controller\Security;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Entity\Event\EmailResetEvent;
use App\Entity\Event\PasswordResetEvent;
use App\Mail\Mailer;
use App\Repository\UserRepository;
use App\Security\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EmailChangeTokenController extends AbstractController
{
	use JsonNormalizedMessages;
	
	/**
	 * @var TokenGenerator
	 */
	private TokenGenerator $tokenGenerator;
	
	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $entityManager;
	
	/**
	 * @var UserRepository
	 */
	private UserRepository $userRepository;
	
	/**
	 * @var Mailer
	 */
	private Mailer $mailer;
	
	/**
	 * @var EventDispatcherInterface
	 */
	private EventDispatcherInterface $eventDispatcher;
	
	/**
	 * PasswordChangeTokenController constructor.
	 * @param  TokenGenerator            $tokenGenerator
	 * @param  EntityManagerInterface    $entityManager
	 * @param  UserRepository            $userRepository
	 * @param  Mailer                    $mailer
	 * @param  EventDispatcherInterface  $eventDispatcher
	 */
	public function __construct(
		TokenGenerator $tokenGenerator,
		EntityManagerInterface $entityManager,
		UserRepository $userRepository,
		Mailer $mailer,
		EventDispatcherInterface $eventDispatcher
	)
	{
		$this->tokenGenerator  = $tokenGenerator;
		$this->entityManager   = $entityManager;
		$this->userRepository  = $userRepository;
		$this->mailer          = $mailer;
		$this->eventDispatcher = $eventDispatcher;
	}
	
	public function __invoke(Request $request): JsonResponse
	{
		try {
			
			$token = $this->getTokenGenerator()->generateToken();
			
		} catch (Throwable $e) {
			
			return $this->jsonMessage(
				500,
				'Error generating token. Please refresh the page and try again.'
			);
		}
		
		$email = (json_decode($request->getContent()))->email ?? null;
		
		if (! $email) {
			
			return $this->jsonMessage(
				Response::HTTP_BAD_REQUEST,
				'Cannot generate token without an email.'
			);
		}
		
		if ($email) {
			
			$user = $this->getUserRepository()->findOneBy($email);
			
			$this->getEventDispatcher()->dispatch(new EmailResetEvent($user, $token));
			
			$this->getEntityManager()->flush();
			
			$this->getMailer()->sendEmailChangeConfirmationToken($user);
		}
		
		// If no email is found, still return success for security purposes.
		return $this->json(null, Response::HTTP_NO_CONTENT);
	}
	
	public function getTokenGenerator(): TokenGenerator
	{
		return $this->tokenGenerator;
	}
	
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}
	
	public function getUserRepository(): UserRepository
	{
		return $this->userRepository;
	}
	
	public function getMailer(): Mailer
	{
		return $this->mailer;
	}
	
	public function getEventDispatcher(): EventDispatcherInterface
	{
		return $this->eventDispatcher;
	}
}