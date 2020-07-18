<?php

declare(strict_types = 1);

namespace App\Controller\Profile;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Controller\Concerns\JsonNormalizedResponse;
use App\Security\Voter\UserProfileVoter;
use App\Service\EntityService\UserProfileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

class UpdateController extends AbstractController
{
	use JsonNormalizedResponse, JsonNormalizedMessages;
	
	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $entityManager;
	
	/**
	 * @var SerializerInterface
	 */
	private SerializerInterface $serializer;
	
	/**
	 * @var ValidatorInterface
	 */
	private ValidatorInterface $validator;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		SerializerInterface $serializer,
		ValidatorInterface $validator
	)
	{
		$this->entityManager = $entityManager;
		$this->serializer    = $serializer;
		$this->validator     = $validator;
	}
	
	public function __invoke(Request $request): JsonResponse
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
		
		$profile = $this->getUser()->getProfile();
		
		$this->denyAccessUnlessGranted(UserProfileVoter::EDIT, $profile);
		
		try {
			
			$profile = UserProfileService::update($request->getContent(), $profile);
			
		} catch (Throwable $exception) {
			
			return $this->jsonDefaultError();
		}
		
		$violations = $this->getValidator()->validate($profile);
		
		if (count($violations) > 1) {
			
			return $this->jsonViolations($violations);
		}
		
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($profile, ['profile:self:read']);
	}
	
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}
	
	public function getSerializer(): SerializerInterface
	{
		return $this->serializer;
	}
	
	public function getValidator(): ValidatorInterface
	{
		return $this->validator;
	}
}