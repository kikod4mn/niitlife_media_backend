<?php

declare(strict_types = 1);

namespace App\Controller\Profile;

use App\Controller\Concerns\JsonNormalizedResponse;
use App\Security\Voter\UserProfileVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ViewController extends AbstractController
{
	use JsonNormalizedResponse;
	
	public function __invoke()
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
		
		$profile = $this->getUser()->getProfile();
		
		$this->denyAccessUnlessGranted(UserProfileVoter::VIEW, $profile);
		
		return $this->jsonNormalized($profile, ['profile:self:read']);
	}
}