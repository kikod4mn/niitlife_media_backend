<?php

declare(strict_types = 1);

namespace App\Entity\Factory\Concerns;

use App\Support\Validate;
use InvalidArgumentException;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait BaseFactoryTrait
{
	/**
	 * @var null|ValidatorInterface
	 */
	private ?ValidatorInterface $validator = null;
	
	/**
	 * @param $data
	 * @return mixed
	 */
	protected static function new($data)
	{
		$factory = new self();
		
		if (is_string($data) && ! Validate::blank($data)) {
			
			return $factory->makeFromJson($data);
		}
		
		if (is_array($data) && ! Validate::blank($data)) {
			
			return $factory->makeFromArray($data);
		}
		
		throw new InvalidArgumentException('Data must be either a string or an array of raw post data');
	}
	
	protected static function modify($data, $entity)
	{
		$factory = new self();
		
		if (is_string($data)) {
			
			return $factory->makeFromJson($data, $entity);
		}
		
		if (is_array($data)) {
			
			return $factory->makeFromArray($data, $entity);
		}
		
		throw new InvalidArgumentException('Data must be either a string or an array of raw post data');
	}
	
	/**
	 * @param  string      $data
	 * @param  mixed|null  $entity
	 * @return mixed
	 */
	protected function makeFromJson(string $data, $entity = null)
	{
		return $this->createOrUpdate((array) json_decode($data), $entity);
	}
	
	/**
	 * @param  array       $data
	 * @param  mixed|null  $entity
	 * @return mixed
	 */
	protected function makeFromArray(array $data, $entity = null)
	{
		return $this->createOrUpdate($data, $entity);
	}
	
	/**
	 * @param  array       $data
	 * @param  mixed|null  $entity
	 * @return mixed
	 */
	protected function createOrUpdate(array $data, $entity = null)
	{
		if (null !== $entity) {
			
			return $this->edit($data, $entity);
		}
		
		$this->validArrayKeys($data);
		
		return $this->create($data);
	}
	
	/**
	 * @return ValidatorInterface
	 */
	protected function getValidator(): ValidatorInterface
	{
		if (null === $this->validator) {
			
			$this->validator = Validation::createValidator();
		}
		
		return $this->validator;
	}
}