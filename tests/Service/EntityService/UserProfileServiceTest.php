<?php

declare(strict_types = 1);

namespace App\Tests\Service\EntityService;

use App\Entity\UserProfile;
use App\Service\EntityService\Exception\ArrayKeyNotSetException;
use App\Service\EntityService\Exception\ClassConstantNotDefinedException;
use App\Service\EntityService\Exception\EmptyValueException;
use App\Service\EntityService\Exception\InvalidArrayKeysException;
use App\Service\EntityService\Exception\MethodNotFoundException;
use App\Service\EntityService\UserProfileService;
use App\Tests\Contracts\ExpectsExpections;
use App\Tests\Contracts\FindsFromRawOrValid;
use Generator;
use LogicException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Exception\ValidatorException;

class UserProfileServiceTest extends TestCase implements ExpectsExpections
{
	use FindsFromRawOrValid;
	
	const NEW_PROFILE = 'NEW_PROFILE';
	
	protected array $validCreateData =
		[
			'avatar'       => 'https://dummimage.com/150',
			'data_of_ever' => 'data_of_ever-data_of_ever',
		];
	
	protected array $validUpdateData =
		[
			'avatar'       => 'https://dummimage.com/150',
			'data_of_ever' => 'data_of_ever-data_of_ever',
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
		if ($expectation === self::NEW_PROFILE) {
			
			/** @var UserProfile $profile */
			$profile = UserProfileService::create($raw);
			
			self::assertTrue($profile instanceof UserProfile);
			
			self::assertEquals(
				$this->validCreateData['avatar'],
				$profile->getAvatar()
			);
		}
		
		if ($expectation === self::EXCEPTION) {
			
			self::expectException($exception);
			
			UserProfileService::create($raw);
		}
	}
	
	/**
	 * @dataProvider provideUpdate
	 * @param               $raw
	 * @param  string       $expectation
	 * @param  null|string  $exception
	 * @throws EmptyValueException
	 * @throws InvalidArrayKeysException
	 * @throws MethodNotFoundException
	 */
	public function testUpdate($raw, string $expectation, string $exception = null)
	{
		if ($expectation === self::NEW_PROFILE) {
			
			$profile = new UserProfile();
			$profile->setAvatar($this->validCreateData['avatar']);
			
			$profile = UserProfileService::update($raw, $profile);
			
			self::assertTrue($profile instanceof UserProfile);
			
			self::assertEquals(
				$this->getValueFromRaw('avatar', $raw),
				$profile->getAvatar()
			);
		}
		
		if ($expectation === self::EXCEPTION) {
			
			self::expectException($exception);
			
			UserProfileService::update($raw, new UserProfile());
		}
	}
	
	/**
	 * @return Generator
	 */
	public function provideCreate(): Generator
	{
		yield 'valid data as string' => [
			'raw' => json_encode($this->validCreateData),
			self::NEW_PROFILE,
		];
		
		yield 'valid data as array' => [
			'raw' => $this->validCreateData,
			self::NEW_PROFILE,
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
		
		yield 'valid data, no avatar' => [
			'raw' => array_filter(
				$this->validCreateData,
				fn($elem) => $elem !== $this->validCreateData['avatar']
			),
			self::EXCEPTION,
			ArrayKeyNotSetException::class,
		];
		
		yield 'valid data, blank avatar' => [
			'raw' => ['avatar' => ''],
			self::EXCEPTION,
			EmptyValueException::class,
		];
		
		yield 'valid data, invalid url avatar' => [
			'raw' => ['avatar' => 's'],
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
			'raw' => json_encode($this->validCreateData),
			self::NEW_PROFILE,
		];
		
		yield 'valid data as array' => [
			'raw' => $this->validCreateData,
			self::NEW_PROFILE,
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
		
		yield 'valid data, no avatar' => [
			'raw' => array_filter(
				$this->validCreateData,
				fn($elem) => $elem !== $this->validCreateData['avatar']
			),
			self::NEW_PROFILE,
		];
		
		yield 'valid data, blank avatar' => [
			'raw' => ['avatar' => ''],
			self::NEW_PROFILE,
		];
		
		yield 'valid data, invalid url avatar' => [
			'raw' => ['avatar' => 's'],
			self::EXCEPTION,
			ValidatorException::class,
		];
	}
}