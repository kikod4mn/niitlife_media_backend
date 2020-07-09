<?php

declare(strict_types = 1);

namespace App\Entity\Factory;

use App\Entity\Factory\Concerns\BaseFactoryTrait;
use App\Entity\Factory\Contracts\BaseFactoryInterface;
use App\Entity\Factory\Exception\ArrayKeyNotSetException;
use App\Entity\Image;
use App\Support\Str;
use App\Support\Validate;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Exception\ValidatorException;

class ImageFactory implements BaseFactoryInterface
{
	use BaseFactoryTrait;
	
	/**
	 * @param  array|string  $data
	 * @return Image
	 */
	public static function make($data): Image
	{
		return self::new($data);
	}
	
	/**
	 * @param  array|string  $data
	 * @param  mixed         $image
	 * @return Image
	 */
	public static function update($data, $image): Image
	{
		self::entityTypeCheck($image);
		
		return self::modify($data, $image);
	}
	
	/**
	 * @param  array  $data
	 * @return Image
	 */
	public function create(array $data): Image
	{
		$image = new Image();
		
		$image = $this->setTitle($data['title'], $image);
		$image = $this->setDescription($data['description'], $image);
		$image = $this->setOriginal($data['original'], $image);
		$image = $this->setHalf($data['half'], $image);
		$image = $this->setThumbnail($data['thumbnail'], $image);
		
		return $image;
	}
	
	public function edit(array $data, $image): Image
	{
		self::entityTypeCheck($image);
		
		if (isset($data['title']) && ! Validate::blank($data['title'])) {
			
			$image = $this->setTitle($data['title'], $image);
		}
		
		return $image;
	}
	
	/**
	 * @param  array  $data
	 * @throws ArrayKeyNotSetException
	 */
	public function validArrayKeys(array $data): void
	{
		$requiredKeys = ['title', 'description', 'original', 'half', 'thumbnail'];
		
		foreach ($requiredKeys as $key) {
			
			if (! array_key_exists($key, $data)) {
				
				throw new ArrayKeyNotSetException(sprintf('Key "%s" not set on raw data!', $key));
			}
		}
	}
	
	/**
	 * @param  string  $title
	 * @param  Image   $image
	 * @return Image
	 */
	public function setTitle(string $title, Image $image): Image
	{
		$title = Str::purify($title);
		
		$error = $this->getValidator()->validate(
			$title,
			[
				new NotBlank(),
				new Length(
					[
						'min'        => 10,
						'minMessage' => 'Image title must be at least {{ limit }} characters long.',
						'max'        => 250,
						'maxMessage' => 'Image title must be not exceed {{ limit }} characters.',
					]
				),
			]
		)
		;
		
		if (count($error) > 0) {
			throw new ValidatorException((string) $error);
		}
		
		return $image->setTitle($title);
	}
	
	/**
	 * @param  string  $body
	 * @param  Image   $image
	 * @return Image
	 */
	public function setDescription(string $body, Image $image): Image
	{
		$body = Str::cleanse($body);
		
		$error = $this->getValidator()->validate(
			$body,
			[
				new NotBlank(),
				new Length(
					[
						'min'        => 20,
						'minMessage' => 'Image description must be at least {{ limit }} characters long.',
					]
				),
			]
		)
		;
		
		if (count($error) > 0) {
			throw new ValidatorException((string) $error);
		}
		
		return $image->setDescription($body);
	}
	
	/**
	 * @param  string  $original
	 * @param  Image   $image
	 * @return Image
	 */
	public function setOriginal(string $original, Image $image): Image
	{
		$error = $this->getValidator()->validate(
			$original,
			[
				new NotBlank(),
				new Url(),
			]
		)
		;
		
		if (count($error) > 0) {
			throw new ValidatorException((string) $error);
		}
		
		return $image->setOriginal($original);
	}
	
	public function setHalf(string $half, Image $image): Image
	{
		$error = $this->getValidator()->validate(
			$half,
			[
				new NotBlank(),
				new Url(),
			]
		)
		;
		
		if (count($error) > 0) {
			throw new ValidatorException((string) $error);
		}
		
		return $image->setHalf($half);
	}
	
	public function setThumbnail(string $thumbnail, Image $image): Image
	{
		$error = $this->getValidator()->validate(
			$thumbnail,
			[
				new NotBlank(),
				new Url(),
			]
		)
		;
		
		if (count($error) > 0) {
			throw new ValidatorException((string) $error);
		}
		
		return $image->setThumbnail($thumbnail);
	}
	
	/**
	 * @param $entity
	 */
	private static function entityTypeCheck($entity): void
	{
		if (! $entity instanceof Image) {
			
			throw new InvalidArgumentException('When updating, entity must be an instance of Image.');
		}
	}
}