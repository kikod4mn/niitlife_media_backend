<?php

declare(strict_types = 1);

namespace App\Controller\Account;

use App\Controller\Concerns\JsonNormalizedResponse;
use App\Security\Voter\UserVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class ViewController extends AbstractController
{
	use JsonNormalizedResponse;
	
	/**
	 * @var SerializerInterface
	 */
	private SerializerInterface $serializer;
	
	public function __construct(SerializerInterface $serializer)
	{
		$this->serializer = $serializer;
	}
	
	public function __invoke(): JsonResponse
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
		
		$user = $this->getUser();
		
		$this->denyAccessUnlessGranted(UserVoter::VIEW, $user);
		
		return $this->jsonNormalized($user, ['user:self:read']);
	}
	
	public function getSerializer(): SerializerInterface
	{
		return $this->serializer;
	}
}