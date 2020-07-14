<?php

declare(strict_types = 1);

namespace App\Service\EntityService;

use App\Entity\User;
use App\Service\EntityService\AbstractService\AbstractService;
use App\Service\EntityService\Contracts\AbstractServiceInterface;
use App\Support\Str;

class UserService extends AbstractService implements AbstractServiceInterface
{
	/**
	 * @var string
	 */
	const ENTITY = User::class;
	
	private static array $editingDenied = ['username'];
	
	/**
	 * @return array
	 */
	public static function getProps(): array
	{
		return [
			
			'username' => [
				'callbacks' => [
					function (string $username) {
						if ($username === '') {
							
							return null;
						}
						
						return Str::purify($username);
					},
				],
			],
			
			'fullname' => [
				'callbacks' => [
					function (string $fullname) {
						if ($fullname === '') {
							
							return null;
						}
						
						return Str::purify($fullname);
					},
				],
			],
			
			'email' => [],
			
			'plainPassword' => [],
			
			'retypedPlainPassword' => [],
		
		];
	}
}