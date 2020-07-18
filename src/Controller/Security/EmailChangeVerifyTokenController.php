<?php

declare(strict_types = 1);

namespace App\Controller\Security;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Mail\Mailer;
use App\Repository\UserRepository;
use App\Support\Validate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EmailChangeVerifyTokenController extends AbstractController
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
	 * @var ValidatorInterface
	 */
	private ValidatorInterface $validator;
	
	/**
	 * @var Mailer
	 */
	private Mailer $mailer;
	
	public function __construct(
		UserRepository $userRepository,
		EntityManagerInterface $entityManager,
		ValidatorInterface $validator,
		Mailer $mailer
	)
	{
		$this->userRepository = $userRepository;
		$this->entityManager  = $entityManager;
		$this->validator      = $validator;
		$this->mailer         = $mailer;
	}
	
	public function __invoke(string $token, Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_ANONYMOUSLY');
		
		$user = $this->getUserRepository()->findOneBy(['emailResetToken' => $token]);
		
		// Not using factory to only allow changing of email in this controller.
		$email = (json_decode($request->getContent()))->email ?? '';
		
		if (null === $user || Validate::blank($email)) {
			
			return $this->jsonDefaultError();
		}
		
		if ($user->getOldEmail() !== $user->getEmail()) {
			
			$user->setOldEmail($user->getEmail());
		}
		
		$user->setEmail($email);
		$user->setPasswordResetToken(null);
		
		$violations = $this->getValidator()->validate($user);
		
		if (count($violations) > 0) {
			
			return $this->jsonViolations($violations);
		}
		
		$this->getMailer()->sendEmailChangeNotificationToNewEmail($user);
		$this->getMailer()->sendEmailChangeNotificationToOldEmail($user);
		
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
	
	public function getValidator(): ValidatorInterface
	{
		return $this->validator;
	}
	
	public function getMailer(): Mailer
	{
		return $this->mailer;
	}
}