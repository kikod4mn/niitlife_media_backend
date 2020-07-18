<?php

declare(strict_types = 1);

namespace App\Service\EntityService\AbstractService;

use App\Service\EntityService\Exception\ArrayKeyNotSetException;
use App\Service\EntityService\Exception\ClassConstantNotDefinedException;
use App\Service\EntityService\Exception\EmptyValueException;
use App\Service\EntityService\Exception\InvalidArrayKeysException;
use App\Service\EntityService\Exception\MethodNotFoundException;
use App\Support\Validate;
use LogicException;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validation;
use Throwable;

abstract class AbstractService
{
	/**
	 * The entity being created is saved in this variable.
	 * @var mixed
	 */
	private static $entity;
	
	/**
	 * Define fields for factory to fill.
	 * Optionally define additional methods and validation constraints for Symfony Validator.
	 * @var array
	 */
	private static array $props = [];
	
	/**
	 * Names of class properties to deny insertion during editing.
	 * Only allowed to fill on initial creation.
	 * Example the users username is only allowed to be set on user registration.
	 * @var array
	 */
	protected static array $editingDenied = [];
	
	/**
	 * Names of class properties to skip insertion during creation if fields are not present.
	 * @var array
	 */
	protected static array $optionalFields = [];
	
	/**
	 * @return array
	 */
	abstract public static function getProps(): array;
	
	/**
	 * @return array
	 */
	public static function getEditingDenied(): array
	{
		return static::$editingDenied;
	}
	
	/**
	 * @return array
	 */
	public static function getOptionalFields(): array
	{
		return static::$optionalFields;
	}
	
	/**
	 * @return mixed
	 */
	public static function getEntity()
	{
		return static::$entity;
	}
	
	/**
	 * @param $data
	 * @return mixed
	 * @throws ArrayKeyNotSetException
	 * @throws ClassConstantNotDefinedException
	 * @throws EmptyValueException
	 * @throws InvalidArrayKeysException
	 * @throws MethodNotFoundException
	 */
	public static function create($data)
	{
		if (! defined('static::ENTITY') || ! class_exists(static::ENTITY)) {
			
			throw new ClassConstantNotDefinedException(
				'Constant "ENTITY" must be defined with the full class name of the entity being created.'
			);
		}
		
		static::boot(static::ENTITY, static::getProps());
		
		return static::make(
			static::dataToArray($data)
		);
	}
	
	/**
	 * @param $data
	 * @param $entity
	 * @return mixed
	 * @throws MethodNotFoundException|EmptyValueException|InvalidArrayKeysException
	 */
	public static function update($data, $entity)
	{
		static::boot($entity, static::getProps());
		
		return static::edit(
			static::dataToArray($data)
		);
	}
	
	/**
	 * @param  string|mixed  $entity
	 * @param  array         $props
	 */
	protected static function boot($entity, array $props)
	{
		static::$entity = static::setEntity($entity);
		static::$props  = $props;
	}
	
	protected static function validate(): void
	{
		$validation = Validation::createValidatorBuilder()
		                        ->enableAnnotationMapping()
		                        ->getValidator()
		;
		
		$violations = $validation->validate(static::getEntity());
		
		if (count($violations) > 0) {
			
			throw new ValidatorException((string) $violations);
		}
	}
	
	/**
	 * Create a new instance out of a FQN class or return an already present instance.
	 * @param $entity
	 * @return mixed
	 */
	protected static function setEntity($entity)
	{
		if (is_string($entity)) {
			
			return new $entity();
		}
		
		return $entity;
	}
	
	/**
	 * @param $data
	 * @return array
	 * @throws InvalidArrayKeysException
	 */
	protected static function dataToArray($data): array
	{
		if (is_string($data) && ! Validate::blank($data)) {
			
			return static::fromJson($data);
		}
		
		if (is_array($data) && ! Validate::blank($data)) {
			
			return static::fromArray($data);
		}
		
		throw new LogicException('Error in dataToArray in Factory. Data type not supported.');
	}
	
	/**
	 * @param  string  $data
	 * @return array
	 * @throws InvalidArrayKeysException
	 */
	protected static function fromJson(string $data): array
	{
		return static::fromArray((array) json_decode($data));
	}
	
	/**
	 * @param  array  $data
	 * @return array
	 * @throws InvalidArrayKeysException
	 */
	protected static function fromArray(array $data): array
	{
		return static::stringKeys($data);
	}
	
	/**
	 * @param  array  $data
	 * @return mixed
	 * @throws ArrayKeyNotSetException
	 * @throws EmptyValueException
	 * @throws MethodNotFoundException
	 */
	protected static function make(array $data)
	{
		foreach (static::$props as $prop => $params) {
			
			if (
				in_array($prop, static::getOptionalFields())
				&& ! array_key_exists($prop, $data)
			) {
				
				continue;
			}
			
			$value = static::value($data, $prop);
			
			$method = static::method($prop);
			
			static::$entity->$method(
				static::callbacks($params, $value)
			);
		}
		
		static::validate();
		
		return static::$entity;
	}
	
	/**
	 * @param  array  $data
	 * @return mixed
	 * @throws MethodNotFoundException|EmptyValueException
	 */
	protected static function edit(array $data)
	{
		foreach (static::$props as $prop => $params) {
			
			if (in_array($prop, static::getEditingDenied())) {
				
				continue;
			}
			
			try {
				
				$value = static::value($data, $prop);
				
			} catch (Throwable $e) {
				
				continue;
			}
			
			$method = static::method($prop);
			
			static::$entity->$method(
				static::callbacks($params, $value)
			);
		}
		
		static::validate();
		
		return static::$entity;
	}
	
	/**
	 * @param  string  $prop
	 * @return string
	 * @throws MethodNotFoundException
	 */
	protected static function method(string $prop): string
	{
		$method = "set{$prop}";
		
		if (! method_exists(static::$entity, $method)) {
			
			throw new MethodNotFoundException(
				sprintf(
					'Method "%s" for prop "%s" not found on entity "%s"',
					$method, $prop, get_class(static::$entity)
				)
			);
		}
		
		return $method;
	}
	
	/**
	 * @param  array   $data
	 * @param  string  $prop
	 * @return mixed
	 * @throws ArrayKeyNotSetException
	 * @throws EmptyValueException
	 */
	protected static function value(array $data, string $prop)
	{
		if (! array_key_exists($prop, $data)) {
			
			throw new ArrayKeyNotSetException(
				sprintf(
					'Array key "%s" not set on inbound data for a new "%s"',
					$prop,
					get_class(static::$entity)
				)
			);
		}
		
		if (Validate::blank($data[$prop])) {
			
			throw new EmptyValueException(
				sprintf(
					'Array key "%s" has a blank value which is not accepted for entity "%s".',
					$prop,
					get_class(static::$entity)
				)
			);
		}
		
		return $data[$prop];
	}
	
	/**
	 * @param  array  $params
	 * @param         $value
	 * @return mixed
	 * @throws EmptyValueException
	 */
	protected static function callbacks(array $params, $value)
	{
		if (array_key_exists('callbacks', $params) && ! Validate::blank($params['callbacks'])) {
			
			foreach ($params['callbacks'] as $additionalMethod) {
				
				$value = call_user_func($additionalMethod, $value);
			}
		}
		
		if (Validate::blank($value)) {
			
			throw new EmptyValueException();
		}
		
		return $value;
	}
	
	/**
	 * @param  array  $data
	 * @return array
	 * @throws InvalidArrayKeysException
	 */
	protected static function stringKeys(array $data): array
	{
		if (count(array_filter(array_keys($data), 'is_string')) > 0) {
			
			return $data;
		}
		
		throw new InvalidArrayKeysException('Array must be associative and use strings as keys.');
	}
}