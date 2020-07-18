<?php

declare(strict_types = 1);

namespace App\Tests\Service\EntityService;

use App\Entity\User;
use App\Service\EntityService\Exception\ArrayKeyNotSetException;
use App\Service\EntityService\Exception\ClassConstantNotDefinedException;
use App\Service\EntityService\Exception\EmptyValueException;
use App\Service\EntityService\Exception\InvalidArrayKeysException;
use App\Service\EntityService\Exception\MethodNotFoundException;
use App\Service\EntityService\UserService;
use App\Tests\Contracts\ExpectsExpections;
use App\Tests\Contracts\FindsFromRawOrValid;
use Faker\Provider\Lorem;
use Generator;
use LogicException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Exception\ValidatorException;

class UserServiceTest extends TestCase implements ExpectsExpections
{
	use FindsFromRawOrValid;
	
	const NEW_USER = 'NEW_USER';
	
	protected array $validCreateData =
		[
			'username'             => 'kikokikoomus',
			'fullname'             => 'Kiko of House Kikoomus of Angelus Descent',
			'email'                => 'kiko@rulersofworld.com',
			'plainPassword'        => 'passwordSECRET123',
			'retypedPlainPassword' => 'passwordSECRET123',
		];
	
	protected array $validUpdateData = [
		'fullname'             => 'Kiko, the Emperor of Mankind',
		'email'                => 'emperor_kiko@rulersofworld.com',
		'plainPassword'        => '321secretPASSWORD',
		'retypedPlainPassword' => '321secretPASSWORD',
	];
	
	/**
	 * @dataProvider provideCreate
	 * @param               $raw
	 * @param  string       $expectation
	 * @param  null|string  $exception
	 * @throws ArrayKeyNotSetException
	 * @throws ClassConstantNotDefinedException
	 * @throws EmptyValueException
	 * @throws InvalidArrayKeysException
	 * @throws MethodNotFoundException
	 */
	public function testCreate($raw, string $expectation, string $exception = null)
	{
		if ($expectation === self::NEW_USER) {
			
			/** @var User $user */
			$user = UserService::create($raw);
			
			self::assertTrue($user instanceof User);
			
			self::assertTrue($user->isActivated());
			
			self::assertEquals(
				$this->validCreateData['username'],
				$user->getUsername()
			);
			
			self::assertEquals(
				$this->validCreateData['fullname'],
				$user->getFullname()
			);
			
			self::assertEquals(
				$this->validCreateData['email'],
				$user->getEmail()
			);
			
			self::assertEquals(
				$this->validCreateData['plainPassword'],
				$user->getPlainPassword()
			);
			
			self::assertEquals(
				$this->validCreateData['retypedPlainPassword'],
				$user->getRetypedPlainPassword()
			);
		}
		
		if ($expectation === self::EXCEPTION) {
			
			$this->expectException($exception);
			
			UserService::create($raw);
		}
	}
	
	/**
	 * @dataProvider provideUpdate
	 * @param               $raw
	 * @param  string       $expectation
	 * @param  null|string  $exception
	 * @throws InvalidArrayKeysException
	 * @throws MethodNotFoundException
	 * @throws EmptyValueException
	 */
	public function testUpdate($raw, string $expectation, string $exception = null)
	{
		if ($expectation === self::NEW_USER) {
			
			$user = new User();
			
			$user->setUsername($this->validCreateData['username']);
			$user->setEmail($this->validCreateData['email']);
			$user->setFullname($this->validCreateData['fullname']);
			$user->setPlainPassword($this->validCreateData['plainPassword']);
			$user->setRetypedPlainPassword($this->validCreateData['retypedPlainPassword']);
			
			$user = UserService::update($raw, $user);
			
			self::assertTrue($user instanceof User);
			
			self::assertTrue($user->isActivated());
			
			// Test username remains the same
			self::assertEquals(
				$this->validCreateData['username'],
				$user->getUsername()
			);
			
			self::assertEquals(
				$this->getValueFromRaw(
					'fullname', $raw
				),
				$user->getFullname()
			);
			
			self::assertEquals(
				$this->getValueFromRaw(
					'email', $raw
				),
				$user->getEmail()
			);
			
			self::assertEquals(
				$this->getValueFromRaw(
					'plainPassword', $raw
				),
				$user->getPlainPassword()
			);
			
			self::assertEquals(
				$this->getValueFromRaw(
					'retypedPlainPassword', $raw
				),
				$user->getRetypedPlainPassword()
			);
		}
		
		if ($expectation === self::EXCEPTION) {
			
			$this->expectException($exception);
			
			UserService::update($raw, new User());
		}
	}
	
	/**
	 * @return Generator
	 */
	public function provideCreate(): Generator
	{
		yield 'valid data as string' => [
			'raw' => json_encode($this->validCreateData),
			self::NEW_USER,
		];
		
		yield 'valid data as array' => [
			'raw' => $this->validCreateData,
			self::NEW_USER,
		];
		
		yield 'invalid data type object' => [
			'raw' => (object) $this->validCreateData,
			self::EXCEPTION,
			LogicException::class,
		];
		
		yield 'empty array' => [
			'raw' => [],
			self::EXCEPTION,
			LogicException::class,
		];
		
		yield 'empty string' => [
			'raw' => '',
			self::EXCEPTION,
			LogicException::class,
		];
		
		yield 'valid data, no username' => [
			'raw' => array_filter(
				$this->validCreateData,
				fn($elem) => $elem !== $this->validCreateData['username']
			),
			self::EXCEPTION,
			ArrayKeyNotSetException::class,
		];
		
		yield 'valid data, blank username' => [
			'raw' => array_merge(
				$this->validCreateData,
				['username' => '']
			),
			self::EXCEPTION,
			EmptyValueException::class,
		];
		
		yield 'valid data, no email' => [
			'raw' => array_filter(
				$this->validCreateData,
				fn($elem) => $elem !== $this->validCreateData['email']
			),
			self::EXCEPTION,
			ArrayKeyNotSetException::class,
		];
		
		yield 'valid data, blank email' => [
			'raw' => array_merge(
				$this->validCreateData,
				['email' => '']
			),
			self::EXCEPTION,
			EmptyValueException::class,
		];
		
		yield 'valid data, no fullname' => [
			'raw' => array_filter(
				$this->validCreateData,
				fn($elem) => $elem !== $this->validCreateData['fullname']
			),
			self::EXCEPTION,
			ArrayKeyNotSetException::class,
		];
		
		yield 'valid data, blank fullname' => [
			'raw' => array_merge(
				$this->validCreateData,
				['fullname' => '']
			),
			self::EXCEPTION,
			EmptyValueException::class,
		];
		
		yield 'valid data, no plainPassword' => [
			'raw' => array_filter(
				$this->validCreateData,
				fn($elem) => $elem !== $this->validCreateData['plainPassword']
			),
			self::EXCEPTION,
			ArrayKeyNotSetException::class,
		];
		
		yield 'valid data, blank plainPassword' => [
			'raw' => array_merge(
				$this->validCreateData,
				['plainPassword' => '']
			),
			self::EXCEPTION,
			EmptyValueException::class,
		];
		
		yield 'valid data, no retypedPlainPassword' => [
			'raw' => array_filter(
				$this->validCreateData,
				fn($elem) => $elem !== $this->validCreateData['retypedPlainPassword']
			),
			self::EXCEPTION,
			ArrayKeyNotSetException::class,
		];
		
		yield 'valid data, blank retypedPlainPassword' => [
			'raw' => array_merge(
				$this->validCreateData,
				['retypedPlainPassword' => '']
			),
			self::EXCEPTION,
			EmptyValueException::class,
		];
		
		yield 'valid data, short username' => [
			'raw' => array_merge(
				$this->validCreateData,
				['username' => 'asd']
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, long username' => [
			'raw' => array_merge(
				$this->validCreateData,
				['username' => Lorem::text(1000)]
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, invalid email' => [
			'raw' => array_merge(
				$this->validCreateData,
				['email' => 'asd.com']
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, long email' => [
			'raw' => array_merge(
				$this->validCreateData,
				['email' => Lorem::text(1000)]
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, long fullname' => [
			'raw' => array_merge(
				$this->validCreateData,
				['fullname' => Lorem::text(1000)]
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, short passwords' => [
			'raw' => array_merge(
				$this->validCreateData,
				[
					'plainPassword'        => 'asd',
					'retypedPlainPassword' => 'asd',
				]
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, only lowercase passwords' => [
			'raw' => array_merge(
				$this->validCreateData,
				[
					'plainPassword'        => 'asdasdasdasd',
					'retypedPlainPassword' => 'asdasdasdasd',
				]
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, only uppercase passwords' => [
			'raw' => array_merge(
				$this->validCreateData,
				[
					'plainPassword'        => 'ASDASDASDASD',
					'retypedPlainPassword' => 'ASDASDASDASD',
				]
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, only numbers passwords' => [
			'raw' => array_merge(
				$this->validCreateData,
				[
					'plainPassword'        => '123123123123',
					'retypedPlainPassword' => '123123123123',
				]
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, not matching valid passwords' => [
			'raw' => array_merge(
				$this->validCreateData,
				[
					'plainPassword'        => 'asdASD1234444',
					'retypedPlainPassword' => 'qweQWE3210000',
				]
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
	}
	
	/**
	 * @return Generator
	 */
	public function provideUpdate(): Generator
	{
		yield 'valid data as string' => [
			'raw' => json_encode($this->validUpdateData),
			self::NEW_USER,
		];
		
		yield 'valid data as array' => [
			'raw' => $this->validUpdateData,
			self::NEW_USER,
		];
		
		yield 'valid data as array add username' => [
			'raw' => array_merge($this->validUpdateData, ['username' => 'kikopolis']),
			self::NEW_USER,
		];
		
		yield 'invalid data type object' => [
			'raw' => (object) $this->validUpdateData,
			self::EXCEPTION,
			LogicException::class,
		];
		
		yield 'empty array' => [
			'raw' => [],
			self::EXCEPTION,
			LogicException::class,
		];
		
		yield 'empty string' => [
			'raw' => '',
			self::EXCEPTION,
			LogicException::class,
		];
		
		yield 'valid data, no email' => [
			'raw' => array_filter(
				$this->validUpdateData,
				fn($elem) => $elem !== $this->validUpdateData['email']
			),
			self::NEW_USER,
		];
		
		yield 'valid data, blank email' => [
			'raw' => array_merge($this->validUpdateData, ['email' => '']),
			self::NEW_USER,
		];
		
		yield 'valid data, no fullname' => [
			'raw' => array_filter(
				$this->validUpdateData,
				fn($elem) => $elem !== $this->validUpdateData['fullname']
			),
			self::NEW_USER,
		];
		
		yield 'valid data, blank fullname' => [
			'raw' => array_merge($this->validUpdateData, ['fullname' => '']),
			self::NEW_USER,
		];
		
		yield 'valid data, no plainPassword' => [
			'raw' => array_filter(
				$this->validUpdateData,
				fn($elem) => $elem !== $this->validUpdateData['plainPassword']
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, blank plainPassword' => [
			'raw' => array_merge($this->validUpdateData, ['plainPassword' => '']),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, no retypedPlainPassword' => [
			'raw' => array_filter(
				$this->validUpdateData,
				fn($elem) => $elem !== $this->validUpdateData['retypedPlainPassword']
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, blank retypedPlainPassword' => [
			'raw' => array_merge($this->validUpdateData, ['retypedPlainPassword' => '']),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, short username' => [
			'raw' => array_merge(
				$this->validCreateData,
				['username' => 'asd']
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, long username' => [
			'raw' => array_merge(
				$this->validCreateData,
				['username' => Lorem::text(1000)]
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, invalid email' => [
			'raw' => array_merge(
				$this->validCreateData,
				['email' => 'asd.com']
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, long email' => [
			'raw' => array_merge(
				$this->validCreateData,
				['email' => Lorem::text(1000)]
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, long fullname' => [
			'raw' => array_merge(
				$this->validCreateData,
				['fullname' => Lorem::text(1000)]
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, short passwords' => [
			'raw' => array_merge(
				$this->validCreateData,
				[
					'plainPassword'        => 'asd',
					'retypedPlainPassword' => 'asd',
				]
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, only lowercase passwords' => [
			'raw' => array_merge(
				$this->validCreateData,
				[
					'plainPassword'        => 'asdasdasdasd',
					'retypedPlainPassword' => 'asdasdasdasd',
				]
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, only uppercase passwords' => [
			'raw' => array_merge(
				$this->validCreateData,
				[
					'plainPassword'        => 'ASDASDASDASD',
					'retypedPlainPassword' => 'ASDASDASDASD',
				]
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, only numbers passwords' => [
			'raw' => array_merge(
				$this->validCreateData,
				[
					'plainPassword'        => '123123123123',
					'retypedPlainPassword' => '123123123123',
				]
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, not matching valid passwords' => [
			'raw' => array_merge(
				$this->validCreateData,
				[
					'plainPassword'        => 'asdASD1234444',
					'retypedPlainPassword' => 'qweQWE3210000',
				]
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
	}
}