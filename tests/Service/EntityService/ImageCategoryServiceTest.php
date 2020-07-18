<?php

declare(strict_types = 1);

namespace App\Tests\Service\EntityService;

use App\Entity\ImageCategory;
use App\Service\EntityService\Exception\ArrayKeyNotSetException;
use App\Service\EntityService\Exception\ClassConstantNotDefinedException;
use App\Service\EntityService\Exception\EmptyValueException;
use App\Service\EntityService\Exception\InvalidArrayKeysException;
use App\Service\EntityService\Exception\MethodNotFoundException;
use App\Service\EntityService\ImageCategoryService;
use App\Tests\Contracts\ExpectsExpections;
use App\Tests\Contracts\FindsFromRawOrValid;
use Faker\Provider\Lorem;
use Generator;
use LogicException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Exception\ValidatorException;

class ImageCategoryServiceTest extends TestCase implements ExpectsExpections
{
	use FindsFromRawOrValid;
	
	const NEW_CATEGORY = 'NEW_CATEGORY';
	
	protected array $validCreateData =
		[
			'title'                 => 'A new category title',
			'some_random_field_for' => 'not_empty_array',
		];
	
	protected array $validUpdateData =
		[
			'title'                 => 'A new edited title',
			'some_random_field_for' => 'not_empty_array',
		];
	
	/**
	 * @dataProvider provideCreate
	 * @param          $raw
	 * @param  string  $expectation
	 * @param  string  $exception
	 * @throws ArrayKeyNotSetException
	 * @throws ClassConstantNotDefinedException
	 * @throws EmptyValueException
	 * @throws InvalidArrayKeysException
	 * @throws MethodNotFoundException
	 */
	public function testCreate($raw, string $expectation, string $exception = null)
	{
		if ($expectation === self::NEW_CATEGORY) {
			
			/** @var ImageCategory $category */
			$category = ImageCategoryService::create($raw);
			
			self::assertTrue($category instanceof ImageCategory);
			
			self::assertEquals(
				$this->validCreateData['title'],
				$category->getTitle()
			);
			
		}
		
		if ($expectation === self::EXCEPTION) {
			
			$this->expectException($exception);
			
			ImageCategoryService::create($raw);
		}
	}
	
	/**
	 * @dataProvider provideUpdate
	 * @param          $raw
	 * @param  string  $expectation
	 * @param  string  $exception
	 * @throws EmptyValueException
	 * @throws InvalidArrayKeysException
	 * @throws MethodNotFoundException
	 */
	public function testUpdate($raw, string $expectation, string $exception = null)
	{
		if ($expectation === self::NEW_CATEGORY) {
			
			$category = new ImageCategory();
			$category->setTitle($this->validCreateData['title']);
			
			$category = ImageCategoryService::update($raw, $category);
			
			self::assertTrue($category instanceof ImageCategory);
			
			self::assertEquals(
				$this->getValueFromRaw('title', $raw),
				$category->getTitle()
			);
			
		}
		
		if ($expectation === self::EXCEPTION) {
			
			$this->expectException($exception);
			
			$category = new ImageCategory();
			
			ImageCategoryService::update($raw, $category);
		}
	}
	
	/**
	 * @return Generator
	 */
	public function provideCreate(): Generator
	{
		yield 'valid data as string' => [
			'raw' => json_encode($this->validCreateData),
			self::NEW_CATEGORY,
		];
		
		yield 'valid data as array' => [
			'raw' => $this->validCreateData,
			self::NEW_CATEGORY,
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
			self::NEW_CATEGORY,
		];
		
		yield 'invalid data type object' => [
			'raw' => (object) $this->validCreateData,
			self::EXCEPTION,
			LogicException::class,
		];
		
		yield 'invalid data, no title' => [
			'raw' => array_filter(
				$this->validCreateData,
				fn($elem) => $elem !== $this->validCreateData['title']
			),
			self::EXCEPTION,
			ArrayKeyNotSetException::class,
		];
		
		yield 'invalid data, blank title' => [
			'raw' => array_merge($this->validCreateData, ['title' => '']),
			self::EXCEPTION,
			EmptyValueException::class,
		];
		
		yield 'valid data, short title' => [
			'raw' => ['title' => 'a'],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, long title' => [
			'raw' => ['title' => Lorem::text(8100)],
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
			self::NEW_CATEGORY,
		];
		
		yield 'valid data as array' => [
			'raw' => $this->validUpdateData,
			self::NEW_CATEGORY,
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
			self::NEW_CATEGORY,
		];
		
		yield 'valid data, no title' => [
			'raw' => array_filter(
				$this->validUpdateData,
				fn($elem) => $elem !== $this->validUpdateData['title']
			),
			self::NEW_CATEGORY,
		];
		
		yield 'valid data, blank title' => [
			'raw' => array_merge($this->validUpdateData, ['title' => '']),
			self::NEW_CATEGORY,
		];
		
		yield 'valid data, short title' => [
			'raw' => ['title' => 'a'],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, long title' => [
			'raw' => ['title' => Lorem::text(8100)],
			self::EXCEPTION,
			ValidatorException::class,
		];
	}
}