<?php

declare(strict_types = 1);

namespace App\Service\EntityService;

use App\Entity\UserProfile;
use App\Service\EntityService\AbstractService\AbstractService;
use App\Service\EntityService\Contracts\AbstractServiceInterface;

class UserProfileService extends AbstractService implements AbstractServiceInterface
{
	/**
	 * @var string
	 */
	const ENTITY = UserProfile::class;
	
	/**
	 * @return array
	 */
	public static function getProps(): array
	{
		return [
			
			'avatar' => [],
		
		];
	}
}