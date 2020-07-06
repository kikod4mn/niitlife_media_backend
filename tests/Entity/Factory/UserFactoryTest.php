<?php

declare(strict_types = 1);

namespace App\Tests\Entity\Factory;

use App\Entity\Factory\Exception\ArrayKeyNotSetException;
use App\Entity\Factory\UserFactory;
use App\Entity\User;
use App\Support\Str;
use App\Support\Validate;
use Exception;
use Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Exception\ValidatorException;

class UserFactoryTest extends TestCase
{
	const NEW_USER     = 'NEW_USER';
	
	const UPDATED_USER = 'UPDATED_USER';
	
	const EXCEPTION    = 'EXCEPTION';
	
	/**
	 * @dataProvider provideTestMake
	 * @param               $data
	 * @param               $result
	 * @param  null|string  $exception
	 */
	public function testMake($data, $result, string $exception = null)
	{
		if ($result === self::NEW_USER) {
			$user = UserFactory::make($data);
			
			self::assertTrue($user instanceof User);
			
			if (is_string($data)) {
				$data = (array) json_decode($data);
			}
			
			self::assertEquals($data['username'], $user->getUsername());
			self::assertEquals($data['fullname'], $user->getFullname());
			self::assertEquals($data['email'], $user->getEmail());
			self::assertEquals($data['plainPassword'], $user->getPlainPassword());
			self::assertEquals($data['retypedPlainPassword'], $user->getRetypedPlainPassword());
		}
		
		if ($result === self::EXCEPTION && $exception) {
			$this->expectException($exception);
			
			UserFactory::make($data);
		}
	}
	
	/**
	 * @dataProvider provideTestUpdate
	 * @param               $data
	 * @param               $result
	 * @param  null|string  $exception
	 */
	public function testUpdate($data, $result, string $exception = null)
	{
		$userMock = new User();
		$userMock->setFullname('kikopolis kikodamus');
		$userMock->setUsername('kikopolis');
		$userMock->setEmail('kiko@kiko.ee');
		$userMock->setPlainPassword('SUPERsecretp$4');
		$userMock->setRetypedPlainPassword('SUPERsecretp$4');
		
		if ($result === self::NEW_USER) {
			$user = UserFactory::update($data, $userMock);
			
			self::assertTrue($user instanceof User);
			
			if (is_string($data)) {
				$data = (array) json_decode($data);
			}
			
			// Assert that username does not change when updating user
			if (isset($data['username']) && ! Validate::blank($data['username'])) {
				self::assertNotEquals($data['username'], $user->getUsername());
			}
			
			self::assertEquals('kikopolis', $user->getUsername());
			
			// Allow all other parameters to be changed if present in the $data
			if (isset($data['fullname']) && ! Validate::blank($data['fullname'])) {
				self::assertEquals($data['fullname'], $user->getFullname());
				self::assertNotEquals('kikopolis kikodamus', $user->getFullname());
			} else {
				self::assertEquals('kikopolis kikodamus', $user->getFullname());
			}
			
			if (isset($data['email']) && ! Validate::blank($data['email'])) {
				self::assertEquals($data['email'], $user->getEmail());
				self::assertNotEquals('kiko@kiko.ee', $user->getEmail());
			} else {
				self::assertEquals('kiko@kiko.ee', $user->getEmail());
			}
			
			if (isset($data['plainPassword'])
				&& isset($data['retypedPlainPassword'])
				&& ! Validate::blank($data['plainPassword'])
				&& ! Validate::blank($data['retypedPlainPassword'])
			) {
				self::assertEquals($data['plainPassword'], $user->getPlainPassword());
				self::assertNotEquals('SUPERsecretp$4', $user->getPlainPassword());
				
				self::assertEquals($data['retypedPlainPassword'], $user->getRetypedPlainPassword());
				self::assertNotEquals('SUPERsecretp$4', $user->getRetypedPlainPassword());
			} else {
				self::assertEquals('SUPERsecretp$4', $user->getPlainPassword());
				self::assertEquals('SUPERsecretp$4', $user->getRetypedPlainPassword());
			}
		}
		
		if ($result === self::EXCEPTION && $exception) {
			$this->expectException($exception);
			
			UserFactory::make($data);
		}
	}
	
	/**
	 * @return Generator
	 * @throws Exception
	 */
	public function provideTestMake(): Generator
	{
		$username             = 'kikod4mn';
		$fullname             = 'Kristo Leas';
		$email                = 'kiko@kiko.com';
		$plainPassword        = 'SecretP@$$w0rd';
		$retypedPlainPassword = 'SecretP@$$w0rd';
		
		yield 'valid data as array' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::NEW_USER,
		];
		
		yield 'valid data as string' => [
			json_encode(
				[
					'username'             => $username,
					'fullname'             => $fullname,
					'email'                => $email,
					'plainPassword'        => $plainPassword,
					'retypedPlainPassword' => $retypedPlainPassword,
				]
			),
			self::NEW_USER,
		];
		
		yield 'no username' => [
			[
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::EXCEPTION,
			ArrayKeyNotSetException::class,
		];
		
		yield 'no fullname' => [
			[
				'username'             => $username,
				'email'                => $email,
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::EXCEPTION,
			ArrayKeyNotSetException::class,
		];
		
		yield 'no email' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::EXCEPTION,
			ArrayKeyNotSetException::class,
		];
		
		yield 'no plainPassword' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => $email,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::EXCEPTION,
			ArrayKeyNotSetException::class,
		];
		
		yield 'no retypedPlainPassword' => [
			[
				'username'      => $username,
				'fullname'      => $fullname,
				'email'         => $email,
				'plainPassword' => $plainPassword,
			],
			self::EXCEPTION,
			ArrayKeyNotSetException::class,
		];
		
		yield 'too short username' => [
			[
				'username'             => 'ki',
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'too long username' => [
			[
				'username'             => Str::random(276),
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'blank username' => [
			[
				'username'             => '',
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'too short fullname' => [
			[
				'username'             => $username,
				'fullname'             => 'ki',
				'email'                => $email,
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'too long fullname' => [
			[
				'username'             => $username,
				'fullname'             => Str::random(276),
				'email'                => $email,
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'blank fullname' => [
			[
				'username'             => $username,
				'fullname'             => '',
				'email'                => $email,
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'invalid email' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => 'ki.kiko.com',
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'too long email' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => Str::random(260) . '@' . 'kiko.com',
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'blank email' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => '',
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'short password' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => '123sS',
				'retypedPlainPassword' => '123sS',
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'password without lowercase letter' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => '123SSSDDDSFF',
				'retypedPlainPassword' => '123SSSDDDSFF',
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'password without uppercase letter' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => '123sdasdasdasd',
				'retypedPlainPassword' => '123sdasdasdasd',
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'password without number' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => 'fffsdasdasdasd',
				'retypedPlainPassword' => 'fffsdasdasdasd',
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'blank password' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => '',
				'retypedPlainPassword' => '123SSSDDDSFFasaq',
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'blank plainPassword' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => '123SSSDDDSFFasaq',
				'retypedPlainPassword' => '',
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'non matching passwords' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => '123SSSDDDSFFasaq!!',
				'retypedPlainPassword' => '123SSSDDDSFFasaq@@',
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'blank passwords' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => '',
				'retypedPlainPassword' => '',
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
	}
	
	/**
	 * @return Generator
	 * @throws Exception
	 */
	public function provideTestUpdate(): Generator
	{
		$username             = 'kikod4mn';
		$fullname             = 'Kristo Leas';
		$email                = 'kiko@kiko.com';
		$plainPassword        = 'SecretP@$$w0rd';
		$retypedPlainPassword = 'SecretP@$$w0rd';
		
		yield 'valid data as array' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::NEW_USER,
		];
		
		yield 'valid data as string' => [
			json_encode(
				[
					'username'             => $username,
					'fullname'             => $fullname,
					'email'                => $email,
					'plainPassword'        => $plainPassword,
					'retypedPlainPassword' => $retypedPlainPassword,
				]
			),
			self::NEW_USER,
		];
		
		yield 'no username' => [
			[
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::NEW_USER,
		];
		
		yield 'no fullname' => [
			[
				'username'             => $username,
				'email'                => $email,
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::NEW_USER,
		];
		
		yield 'no email' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::NEW_USER,
		];
		
		yield 'no plainPassword' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => $email,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::EXCEPTION,
			ArrayKeyNotSetException::class,
		];
		
		yield 'no retypedPlainPassword' => [
			[
				'username'      => $username,
				'fullname'      => $fullname,
				'email'         => $email,
				'plainPassword' => $plainPassword,
			],
			self::EXCEPTION,
			ArrayKeyNotSetException::class,
		];
		
		yield 'too short username' => [
			[
				'username'             => 'ki',
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::NEW_USER,
		];
		
		yield 'too long username' => [
			[
				'username'             => Str::random(276),
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'blank username' => [
			[
				'username'             => '',
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::NEW_USER,
		];
		
		yield 'too short fullname' => [
			[
				'username'             => $username,
				'fullname'             => 'ki',
				'email'                => $email,
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'too long fullname' => [
			[
				'username'             => $username,
				'fullname'             => Str::random(276),
				'email'                => $email,
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'blank fullname' => [
			[
				'username'             => $username,
				'fullname'             => '',
				'email'                => $email,
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::NEW_USER,
		];
		
		yield 'invalid email' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => 'ki.kiko.com',
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'too long email' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => Str::random(260) . '@' . 'kiko.com',
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'blank email' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => '',
				'plainPassword'        => $plainPassword,
				'retypedPlainPassword' => $retypedPlainPassword,
			],
			self::NEW_USER,
		];
		
		yield 'short password' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => '123sS',
				'retypedPlainPassword' => '123sS',
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'password without lowercase letter' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => '123SSSDDDSFF',
				'retypedPlainPassword' => '123SSSDDDSFF',
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'password without uppercase letter' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => '123sdasdasdasd',
				'retypedPlainPassword' => '123sdasdasdasd',
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'password without number' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => 'fffsdasdasdasd',
				'retypedPlainPassword' => 'fffsdasdasdasd',
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'blank password' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => '',
				'retypedPlainPassword' => '123SSSDDDSFFasaq',
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'blank plainPassword' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => '123SSSDDDSFFasaq',
				'retypedPlainPassword' => '',
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'non matching passwords' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => '123SSSDDDSFFasaq!!',
				'retypedPlainPassword' => '123SSSDDDSFFasaq@@',
			],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'blank passwords' => [
			[
				'username'             => $username,
				'fullname'             => $fullname,
				'email'                => $email,
				'plainPassword'        => '',
				'retypedPlainPassword' => '',
			],
			self::NEW_USER,
		];
	}
}