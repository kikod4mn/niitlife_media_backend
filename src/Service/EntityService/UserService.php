<?php

declare(strict_types = 1);

namespace App\Service\EntityService;

use App\Entity\User;
use App\Service\EntityService\AbstractService\AbstractService;
use App\Service\EntityService\Contracts\AbstractServiceInterface;
use App\Service\EntityService\Exception\ArrayKeyNotSetException;
use App\Support\Str;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

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
						return Str::purify($username);
					},
				],
				
				'constraints' => [
					new NotBlank(
						[
							'message' => 'Username cannot be blank.',
						]
					),
					new Length(
						[
							'min'        => 6,
							'minMessage' => 'Username must be at least {{ limit }} characters long.',
							'max'        => 250,
							'maxMessage' => 'Username must be not exceed {{ limit }} characters.',
						]
					),
				],
			],
			
			'fullname' => [
				
				'callbacks' => [
					function (string $fullname) {
						return Str::purify($fullname);
					},
				],
				
				'constraints' => [
					new NotBlank(
						[
							'message' => 'Full name cannot be blank.',
						]
					),
					new Length(
						[
							'min'        => 3,
							'minMessage' => 'Full name must be at least {{ limit }} characters long.',
							'max'        => 250,
							'maxMessage' => 'Full name must be not exceed {{ limit }} characters.',
						]
					),
				],
			],
			
			'email' => [
				
				'constraints' => [
					new NotBlank(
						[
							'message' => 'Email cannot be blank.',
						]
					),
					new Email(
						[
							'message' => 'Please use a valid email to sign up.',
						]
					),
					new Length(
						[
							'max'        => 250,
							'maxMessage' => 'Email must be not exceed {{ limit }} characters.',
						]
					),
				],
			],
			
			'plainPassword' => [
				
				'constraints' => [
					new NotBlank(
						[
							'message' => 'Password cannot be blank.',
						]
					),
					new Regex(
						[
							'pattern' => '/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}/',
							'message' => 'Minimum length is 8. The password must also contain one uppercase, one lowercase letter and one digit.',
						]
					),
				],
			],
			
			'retypedPlainPassword' => [
				
				'constraints' => [
					new NotBlank(
						[
							'message' => 'Password cannot be blank.',
						]
					),
					new Regex(
						[
							'pattern' => '/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}/',
							'message' => 'Minimum length is 8. The password must also contain one uppercase, one lowercase letter and one digit.',
						]
					),
				],
			],
		
		];
	}
	
	/**
	 * @param  array  $data
	 * @return null|array
	 */
	public static function rawConstraints(array $data)
	{
		if (isset($data['plainPassword']) && isset($data['retypedPlainPassword'])) {
			
			if ($data['plainPassword'] !== $data['retypedPlainPassword']) {
				
				return [
					'plainPassword'        => 'Passwords do not match',
					'retypedPlainPassword' => 'Passwords do not match',
				];
			}
		}
		
		return null;
	}
}