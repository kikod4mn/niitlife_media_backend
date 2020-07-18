<?php

declare(strict_types = 1);

namespace App\Tests\Service\EntityService;

use App\Entity\Image;
use App\Service\EntityService\Exception\ArrayKeyNotSetException;
use App\Service\EntityService\Exception\ClassConstantNotDefinedException;
use App\Service\EntityService\Exception\EmptyValueException;
use App\Service\EntityService\Exception\InvalidArrayKeysException;
use App\Service\EntityService\Exception\MethodNotFoundException;
use App\Service\EntityService\ImageService;
use App\Support\Validate;
use App\Tests\Contracts\ExpectsExpections;
use App\Tests\Contracts\FindsFromRawOrValid;
use Generator;
use LogicException;
use PHPUnit\Framework\TestCase;

class ImageServiceTest extends TestCase implements ExpectsExpections
{
	const NEW_IMAGE = 'NEW_IMAGE';
	
	protected array $thumbSizes      = [120, 150, 170, 190, 200, 250];
	
	protected array $originalSizes   = [900, 1000, 800, 1300];
	
	protected array $validCreateData = [
		'original'  => 'https://dummyimage.com/1200',
		'thumbnail' => 'https://dummyimage.com/150',
		'half'      => 'https://dummyimage.com/600',
	];
	
	protected array $validUpdateData = [
		'original'  => 'https://dummyimage.com/999',
		'thumbnail' => 'https://dummyimage.com/125',
		'half'      => 'https://dummyimage.com/449',
	];
	
	protected array $titleAndDesc    = [
		'title'       => 'this is a creative post title. IT IS!!!',
		'description' => 'lorem and ipsum go nothin on my writing skills! Shakespeare is lit!',
	];
	
	/**
	 * NOTE - Not using the trait of FindsFromRawOrValid because
	 * the fields title and description are optional.
	 * No need to rewrite the method for this edge case.
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
		if ($expectation === self::NEW_IMAGE) {
			
			/** @var Image $image */
			$image = ImageService::create($raw);
			
			self::assertTrue($image instanceof Image);
			
			self::assertTrue($image->isPublished());
			
			self::assertEquals(
				$this->validCreateData['original'],
				$image->getOriginal()
			);
			
			self::assertEquals(
				$this->validCreateData['half'],
				$image->getHalf()
			);
			
			self::assertEquals(
				$this->validCreateData['thumbnail'],
				$image->getThumbnail()
			);
			
			if (! is_array($raw)) {
				
				if (is_string($raw)) {
					
					$raw = (array) json_decode($raw);
				} else {
					
					$raw = (array) $raw;
				}
			}
			
			if (
				array_key_exists('title', $raw)
				&& ! Validate::blank($raw['title'])
			) {
				
				self::assertEquals($this->titleAndDesc['title'], $image->getTitle());
			}
			
			if (
				array_key_exists('description', $raw)
				&& ! Validate::blank($raw['description'])
			) {
				
				self::assertEquals($this->titleAndDesc['description'], $image->getDescription());
			}
			
		}
		
		if ($expectation === self::EXCEPTION) {
			
			$this->expectException($exception);
			
			ImageService::create($raw);
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
		if ($expectation === self::NEW_IMAGE) {
			
			$image = new Image();
			
			$image->setOriginal($this->validCreateData['original']);
			$image->setHalf($this->validCreateData['half']);
			$image->setThumbnail($this->validCreateData['thumbnail']);
			$image->setTitle('random title of this image');
			$image->setDescription('random description of this image. lorem and ipsum and whatnot.');
			
			$image = ImageService::update($raw, $image);
			
			self::assertTrue($image instanceof Image);
			
			self::assertTrue($image->isPublished());
			
			if (! is_array($raw)) {
				
				if (is_string($raw)) {
					
					$raw = (array) json_decode($raw);
				} else {
					
					$raw = (array) $raw;
				}
			}
			
			if (
				array_key_exists('original', $raw)
				&& ! Validate::blank($raw['original'])
			) {
				self::assertEquals(
					$this->validUpdateData['original'],
					$image->getOriginal()
				);
			} else {
				self::assertEquals(
					$this->validCreateData['original'],
					$image->getOriginal()
				);
			}
			
			if (
				array_key_exists('half', $raw)
				&& ! Validate::blank($raw['half'])
			) {
				self::assertEquals(
					$this->validUpdateData['half'],
					$image->getHalf()
				);
			} else {
				self::assertEquals(
					$this->validCreateData['half'],
					$image->getHalf()
				);
			}
			
			if (
				array_key_exists('thumbnail', $raw)
				&& ! Validate::blank($raw['thumbnail'])
			) {
				self::assertEquals(
					$this->validUpdateData['thumbnail'],
					$image->getThumbnail()
				);
			} else {
				self::assertEquals(
					$this->validCreateData['thumbnail'],
					$image->getThumbnail()
				);
			}
			
			if (
				array_key_exists('title', $raw)
				&& ! Validate::blank($raw['title'])
			) {
				
				self::assertEquals($this->titleAndDesc['title'], $image->getTitle());
			}
			
			if (
				array_key_exists('description', $raw)
				&& ! Validate::blank($raw['description'])
			) {
				
				self::assertEquals($this->titleAndDesc['description'], $image->getDescription());
			}
			
		}
		
		if ($expectation === self::EXCEPTION) {
			
			$this->expectException($exception);
			
			ImageService::update($raw, new Image());
		}
	}
	
	/**
	 * @return Generator
	 */
	public function provideCreate(): Generator
	{
		yield 'valid data as string' => [
			'raw' => json_encode($this->validCreateData),
			self::NEW_IMAGE,
		];
		
		yield 'valid data as array' => [
			'raw' => $this->validCreateData,
			self::NEW_IMAGE,
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
		
		yield 'valid data, no original' => [
			'raw' => array_filter(
				$this->validCreateData,
				fn($elem) => $elem !== $this->validCreateData['original']
			),
			self::EXCEPTION,
			ArrayKeyNotSetException::class,
		];
		
		yield 'valid data, blank original' => [
			'raw' => array_merge($this->validCreateData, ['original' => '']),
			self::EXCEPTION,
			EmptyValueException::class,
		];
		
		yield 'valid data, no half' => [
			'raw' => array_filter(
				$this->validCreateData,
				fn($elem) => $elem !== $this->validCreateData['half']
			),
			self::EXCEPTION,
			ArrayKeyNotSetException::class,
		];
		
		yield 'valid data, blank half' => [
			'raw' => array_merge($this->validCreateData, ['half' => '']),
			self::EXCEPTION,
			EmptyValueException::class,
		];
		
		yield 'valid data, no thumbnail' => [
			'raw' => array_filter(
				$this->validCreateData,
				fn($elem) => $elem !== $this->validCreateData['thumbnail']
			),
			self::EXCEPTION,
			ArrayKeyNotSetException::class,
		];
		
		yield 'valid data, blank thumbnail' => [
			'raw' => array_merge($this->validCreateData, ['thumbnail' => '']),
			self::EXCEPTION,
			EmptyValueException::class,
		];
		
		yield 'valid data with title and desc as string' => [
			'raw' => json_encode(
				array_merge(
					$this->validCreateData,
					$this->titleAndDesc
				)
			),
			self::NEW_IMAGE,
		];
		
		yield 'valid data with title and desc as array' => [
			'raw' => array_merge(
				$this->validCreateData,
				$this->titleAndDesc
			),
			self::NEW_IMAGE,
		];
		
		yield 'data with title and desc as invalid data object' => [
			'raw' => (object) array_merge(
				$this->validCreateData,
				$this->titleAndDesc
			),
			self::EXCEPTION,
			LogicException::class,
		];
		
		yield 'valid data with title and desc as string and invalid before purification' => [
			'raw' => json_encode(
				array_merge(
					$this->validCreateData,
					[
						'title'       => $this->titleAndDesc['title'] . '<script>alert("asd")</script>',
						'description' => $this->titleAndDesc['description'] . '<script>alert("asd")</script>',
					]
				)
			),
			self::NEW_IMAGE,
		];
		
		yield 'valid data with title and desc as array and invalid before purification' => [
			'raw' => array_merge(
				$this->validCreateData,
				[
					'title'       => $this->titleAndDesc['title'] . '<script>alert("asd")</script>',
					'description' => $this->titleAndDesc['description'] . '<script>alert("asd")</script>',
				]
			),
			self::NEW_IMAGE,
		];
		
		yield 'data with title and desc as invalid data object and invalid before purification' => [
			'raw' => (object) array_merge(
				$this->validCreateData,
				[
					'title'       => $this->titleAndDesc['title'] . '<script>alert("asd")</script>',
					'description' => $this->titleAndDesc['description'] . '<script>alert("asd")</script>',
				]
			),
			self::EXCEPTION,
			LogicException::class,
		];
		
		yield 'data with invalid before purification and empty after' => [
			'raw' => array_merge(
				$this->validCreateData,
				[
					'title'       => '<script>alert("asd")</script>',
					'description' => '<script>alert("asd")</script>',
				]
			),
			self::EXCEPTION,
			EmptyValueException::class,
		];
	}
	
	/**
	 * @return Generator
	 */
	public function provideUpdate(): Generator
	{
		yield 'valid data as string' => [
			'raw' => json_encode($this->validUpdateData),
			self::NEW_IMAGE,
		];
		
		yield 'valid data as array' => [
			'raw' => $this->validUpdateData,
			self::NEW_IMAGE,
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
		
		yield 'valid data, no original' => [
			'raw' => array_filter(
				$this->validUpdateData,
				fn($elem) => $elem !== $this->validUpdateData['original']
			),
			self::NEW_IMAGE,
		];
		
		yield 'valid data, blank original' => [
			'raw' => array_merge($this->validUpdateData, ['original' => '']),
			self::NEW_IMAGE,
		];
		
		yield 'valid data, no half' => [
			'raw' => array_filter(
				$this->validUpdateData,
				fn($elem) => $elem !== $this->validUpdateData['half']
			),
			self::NEW_IMAGE,
		];
		
		yield 'valid data, blank half' => [
			'raw' => array_merge($this->validUpdateData, ['half' => '']),
			self::NEW_IMAGE,
		];
		
		yield 'valid data, no thumbnail' => [
			'raw' => array_filter(
				$this->validUpdateData,
				fn($elem) => $elem !== $this->validUpdateData['thumbnail']
			),
			self::NEW_IMAGE,
		];
		
		yield 'valid data, blank thumbnail' => [
			'raw' => array_merge($this->validUpdateData, ['thumbnail' => '']),
			self::NEW_IMAGE,
		];
		
		yield 'valid data with title and desc as string' => [
			'raw' => json_encode(
				array_merge(
					$this->validUpdateData,
					$this->titleAndDesc
				)
			),
			self::NEW_IMAGE,
		];
		
		yield 'valid data with title and desc as array' => [
			'raw' => array_merge(
				$this->validUpdateData,
				$this->titleAndDesc
			),
			self::NEW_IMAGE,
		];
		
		yield 'data with title and desc as invalid data object' => [
			'raw' => (object) array_merge(
				$this->validUpdateData,
				$this->titleAndDesc
			),
			self::EXCEPTION,
			LogicException::class,
		];
		
		yield 'valid data with title and desc as string and invalid before purification' => [
			'raw' => json_encode(
				array_merge(
					$this->validUpdateData,
					[
						'title'       => $this->titleAndDesc['title'] . '<script>alert("asd")</script>',
						'description' => $this->titleAndDesc['description'] . '<script>alert("asd")</script>',
					]
				)
			),
			self::NEW_IMAGE,
		];
		
		yield 'valid data with title and desc as array and invalid before purification' => [
			'raw' => array_merge(
				$this->validUpdateData,
				[
					'title'       => $this->titleAndDesc['title'] . '<script>alert("asd")</script>',
					'description' => $this->titleAndDesc['description'] . '<script>alert("asd")</script>',
				]
			),
			self::NEW_IMAGE,
		];
		
		yield 'data with title and desc as invalid data object and invalid before purification' => [
			'raw' => (object) array_merge(
				$this->validUpdateData,
				[
					'title'       => $this->titleAndDesc['title'] . '<script>alert("asd")</script>',
					'description' => $this->titleAndDesc['description'] . '<script>alert("asd")</script>',
				]
			),
			self::EXCEPTION,
			LogicException::class,
		];
		
		yield 'data with invalid before purification and empty after' => [
			'raw' => array_merge(
				$this->validUpdateData,
				[
					'title'       => '<script>alert("asd")</script>',
					'description' => '<script>alert("asd")</script>',
				]
			),
			self::EXCEPTION,
			EmptyValueException::class,
		];
	}
}