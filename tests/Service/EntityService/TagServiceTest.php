<?php

declare(strict_types = 1);

namespace App\Tests\Service\EntityService;

use App\Entity\Tag;
use App\Service\EntityService\Exception\ArrayKeyNotSetException;
use App\Service\EntityService\Exception\ClassConstantNotDefinedException;
use App\Service\EntityService\Exception\EmptyValueException;
use App\Service\EntityService\Exception\InvalidArrayKeysException;
use App\Service\EntityService\Exception\MethodNotFoundException;
use App\Service\EntityService\TagService;
use App\Tests\Contracts\ExpectsExpections;
use App\Tests\Contracts\FindsFromRawOrValid;
use Faker\Provider\Lorem;
use Generator;
use LogicException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Exception\ValidatorException;

class TagServiceTest extends TestCase implements ExpectsExpections
{
	use FindsFromRawOrValid;
	
	const NEW_TAG = 'NEW_TAG';
	
	protected array $validCreateData =
		[
			'title'     => 'New TAG',
			'not_empty' => 'ARRAY',
		];
	
	protected array $validUpdateData =
		[
			'title'     => 'EDITED Tag',
			'not_empty' => 'ARRAY',
		];
	
	/**
	 * @dataProvider provideCreate
	 * @param               $raw
	 * @param  string       $expectation
	 * @param  null|string  $exception
	 * @throws ArrayKeyNotSetException
	 * @throws EmptyValueException
	 * @throws ClassConstantNotDefinedException
	 * @throws InvalidArrayKeysException
	 * @throws MethodNotFoundException
	 */
	public function testCreate($raw, string $expectation, string $exception = null)
	{
		if ($expectation === self::NEW_TAG) {
			
			/** @var Tag $tag */
			$tag = TagService::create($raw);
			
			self::assertTrue($tag instanceof Tag);
			
			self::assertEquals(
				$this->validCreateData['title'],
				$tag->getTitle()
			);
		}
		
		if ($expectation === self::EXCEPTION) {
			
			self::expectException($exception);
			
			TagService::create($raw);
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
		if ($expectation === self::NEW_TAG) {
			
			$tag = new Tag();
			$tag->setTitle($this->validCreateData['title']);
			
			$tag = TagService::update($raw, $tag);
			
			self::assertTrue($tag instanceof Tag);
			
			self::assertEquals(
				$this->getValueFromRaw('title', $raw),
				$tag->getTitle()
			);
		}
		
		if ($expectation === self::EXCEPTION) {
			
			self::expectException($exception);
			
			TagService::update($raw, new Tag());
		}
	}
	
	/**
	 * @return Generator
	 */
	public function provideCreate(): Generator
	{
		yield 'valid data as string' => [
			'raw' => json_encode($this->validCreateData),
			self::NEW_TAG,
		];
		
		yield 'valid data as array' => [
			'raw' => $this->validCreateData,
			self::NEW_TAG,
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
		
		yield 'invalid title before purify' => [
			'raw' => [
				'title' => $this->validCreateData['title']
					. '<script>alert("asd")</script>',
			],
			self::NEW_TAG,
		];
		
		yield 'data with invalid title before purification and empty after' => [
			'raw' => ['title' => '<script>alert("asd")</script>',],
			self::EXCEPTION,
			EmptyValueException::class,
		];
		
		yield 'valid data, no title' => [
			'raw' => array_filter(
				$this->validCreateData,
				fn($elem) => $elem !== $this->validCreateData['title']
			),
			self::EXCEPTION,
			ArrayKeyNotSetException::class,
		];
		
		yield 'valid data, blank title' => [
			'raw' => ['title' => ''],
			self::EXCEPTION,
			EmptyValueException::class,
		];
		
		yield 'valid data, short title' => [
			'raw' => ['title' => 's'],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, long title' => [
			'raw' => ['title' => Lorem::text(1000)],
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
			self::NEW_TAG,
		];
		
		yield 'valid data as array' => [
			'raw' => $this->validUpdateData,
			self::NEW_TAG,
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
		
		yield 'invalid title before purify' => [
			'raw' => [
				'title' => $this->validUpdateData['title']
					. '<script>alert("asd")</script>',
			],
			self::NEW_TAG,
		];
		
		yield 'data with invalid title before purification and empty after' => [
			'raw' => ['title' => '<script>alert("asd")</script>',],
			self::EXCEPTION,
			EmptyValueException::class,
		];
		
		yield 'valid data, no title' => [
			'raw' => array_filter(
				$this->validUpdateData,
				fn($elem) => $elem !== $this->validUpdateData['title']
			),
			self::NEW_TAG,
		];
		
		yield 'valid data, blank title' => [
			'raw' => ['title' => ''],
			self::NEW_TAG,
		];
		
		yield 'valid data, short title' => [
			'raw' => ['title' => 's'],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, long title' => [
			'raw' => ['title' => Lorem::text(1000)],
			self::EXCEPTION,
			ValidatorException::class,
		];
	}
}