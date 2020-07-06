<?php

declare(strict_types = 1);

namespace App\Entity\Factory;

use App\Entity\Factory\Concerns\BaseFactoryTrait;
use App\Entity\Factory\Contracts\BaseFactoryInterface;
use App\Entity\Factory\Exception\ArrayKeyNotSetException;
use App\Entity\User;
use App\Security\Exception\PurificationException;
use App\Support\Str;
use App\Support\Validate;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Exception\ValidatorException;

class UserFactory implements BaseFactoryInterface
{
	use BaseFactoryTrait;
	
	/**
	 * @param  array|string  $data
	 * @return User
	 */
	public static function make($data): User
	{
		return self::new($data);
	}
	
	/**
	 * @param  array|string  $data
	 * @param  mixed         $user
	 * @return User
	 */
	public static function update($data, $user): User
	{
		self::entityTypeCheck($user);
		
		return self::modify($data, $user);
	}
	
	/**
	 * @param  array  $data
	 * @return User
	 */
	public function create(array $data): User
	{
		$user = new User();
		
		$user = $this->setUsername($data['username'], $user);
		$user = $this->setFullname($data['fullname'], $user);
		$user = $this->setEmail($data['email'], $user);
		$user = $this->setPassword($data['plainPassword'], $data['retypedPlainPassword'], $user);
		
		return $user;
	}
	
	/**
	 * @param  array  $data
	 * @param         $user
	 * @return User
	 */
	public function edit(array $data, $user): User
	{
		self::entityTypeCheck($user);
		
		if (isset($data['fullname']) && ! Validate::blank($data['fullname'])) {
			
			$user = $this->setFullname($data['fullname'], $user);
		}
		
		if (isset($data['email']) && ! Validate::blank($data['email'])) {
			
			$user = $this->setEmail($data['email'], $user);
		}
		
		if (
			isset($data['plainPassword'])
			&& isset($data['retypedPlainPassword'])
			&& ! Validate::blank($data['plainPassword'])
			&& ! Validate::blank($data['retypedPlainPassword'])
		) {
			$user = $this->setPassword($data['plainPassword'], $data['retypedPlainPassword'], $user);
		}
		
		return $user;
	}
	
	/**
	 * @param  array  $data
	 * @throws ArrayKeyNotSetException
	 */
	public function validArrayKeys(array $data): void
	{
		$requiredKeys = ['username', 'fullname', 'email', 'plainPassword', 'retypedPlainPassword'];
		
		foreach ($requiredKeys as $key) {
			
			if (! array_key_exists($key, $data)) {
				
				throw new ArrayKeyNotSetException(sprintf('Key "%s" not set on raw data!', $key));
			}
		}
	}
	
	/**
	 * @param  string  $username
	 * @param  User    $user
	 * @return User
	 */
	protected function setUsername(string $username, User $user): User
	{
		$clean = Str::purify($username);
		
		if ($clean !== $username) {
			throw new PurificationException('Invalid characters in field "username"');
		}
		
		$error = $this->getValidator()->validate(
			$clean,
			[
				new NotBlank(),
				new Length(
					[
						'min'        => 6,
						'minMessage' => 'Username must be at least {{ limit }} characters long.',
						'max'        => 250,
						'maxMessage' => 'Username must be not exceed {{ limit }} characters.',
					]
				),
			]
		)
		;
		
		if (count($error) > 0) {
			throw new ValidatorException((string) $error);
		}
		
		return $user->setUsername($clean);
	}
	
	/**
	 * @param  string  $fullname
	 * @param  User    $user
	 * @return User
	 */
	protected function setFullname(string $fullname, User $user): User
	{
		$clean = Str::purify($fullname);
		
		if ($clean !== $fullname) {
			throw new PurificationException('Invalid characters in field "full name"');
		}
		
		$error = $this->getValidator()->validate(
			$clean,
			[
				new NotBlank(),
				new Length(
					[
						'min'        => 3,
						'minMessage' => 'Full name must be at least {{ limit }} characters long.',
						'max'        => 250,
						'maxMessage' => 'Full name must be not exceed {{ limit }} characters.',
					]
				),
			]
		)
		;
		
		if (count($error) > 0) {
			throw new ValidatorException((string) $error);
		}
		
		return $user->setFullname($clean);
	}
	
	/**
	 * @param  string  $email
	 * @param  User    $user
	 * @return User
	 */
	protected function setEmail(string $email, User $user): User
	{
		$error = $this->getValidator()->validate(
			$email,
			[
				new NotBlank(),
				new Email(),
				new Length(
					[
						'max'        => 250,
						'maxMessage' => 'Full name must be not exceed {{ limit }} characters.',
					]
				),
			]
		)
		;
		
		if (count($error) > 0) {
			throw new ValidatorException((string) $error);
		}
		
		return $user->setEmail($email);
	}
	
	/**
	 * @param  string  $plainPassword
	 * @param  string  $retypedPlainPassword
	 * @param  User    $user
	 * @return User
	 */
	protected function setPassword(string $plainPassword, string $retypedPlainPassword, User $user): User
	{
		if ($plainPassword !== $retypedPlainPassword) {
			throw new ValidatorException('Passwords do not match. Please retype them and try again.');
		}
		
		$error = $this->getValidator()->validate(
			$plainPassword,
			[
				new NotBlank(),
				new Regex(
					[
						'pattern' => '/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}/',
						'message' => 'Minimum length is 8. The password must also contain one uppercase, one lowercase letter and one digit.',
					]
				),
			]
		)
		;
		
		if (count($error) > 0) {
			throw new ValidatorException((string) $error);
		}
		
		$user->setPlainPassword($plainPassword);
		
		return $user->setRetypedPlainPassword($retypedPlainPassword);
	}
	
	/**
	 * @param $entity
	 * @throws InvalidArgumentException
	 */
	private static function entityTypeCheck($entity)
	{
		if (! $entity instanceof User) {
			throw new InvalidArgumentException('Invalid action. Please contact administrator.');
		}
	}
}