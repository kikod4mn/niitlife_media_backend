<?php

declare(strict_types = 1);

namespace App\Service\EntityService\AbstractService;

use App\Service\EntityService\Exception\ArrayKeyNotSetException;
use App\Service\EntityService\Exception\ClassConstantNotDefinedException;
use App\Service\EntityService\Exception\InvalidArrayKeysException;
use App\Service\EntityService\Exception\MethodNotFoundException;
use App\Support\Validate;
use LogicException;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

abstract class AbstractService
{
	/**
	 * @var null|ValidatorInterface
	 */
	private static ?ValidatorInterface $validator = null;
	
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
	private static array $editingDenied = [];
	
	/**
	 * @return array
	 */
	abstract public static function getProps(): array;
	
	/**
	 * Use this to hook extra constraints in before individual props are validated.
	 * @param  array  $data
	 * @return null|array
	 */
	abstract public static function rawConstraints(array $data);
	
	/**
	 * @return array
	 */
	public static function getEditingDenied(): array
	{
		return static::$editingDenied;
	}
	
	/**
	 * @param $data
	 * @return string
	 * @throws ArrayKeyNotSetException|ClassConstantNotDefinedException|MethodNotFoundException|InvalidArrayKeysException
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
	 * @return string
	 * @throws MethodNotFoundException|InvalidArrayKeysException
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
	 * @throws ArrayKeyNotSetException|MethodNotFoundException
	 */
	protected static function make(array $data)
	{
		$rawErrors = static::rawConstraints($data);
		
		if ($rawErrors) {
			
			return $rawErrors;
		}
		
		foreach (static::$props as $prop => $params) {
			
			try {
				
				$value = static::value($data, $prop);
			} catch (Throwable $e) {
				
				continue;
			}
			
			$method = static::method($prop);
			
			$violations = static::constraints($params, $value, $prop);
			
			if ($violations) {
				
				return $violations;
			}
			
			// Set the property on the entity if validation passes and additional methods are done.
			static::$entity->$method(
				static::callbacks($params, $value)
			);
		}
		
		return static::$entity;
	}
	
	/**
	 * @param  array  $data
	 * @return mixed
	 * @throws MethodNotFoundException
	 */
	protected static function edit(array $data)
	{
		$rawErrors = static::rawConstraints($data);
		
		if ($rawErrors) {
			
			return $rawErrors;
		}
		
		foreach (static::$props as $prop => $params) {
			
			// If editing is denied on the prop, continue to next loop.
			if (in_array($prop, static::getEditingDenied())) {
				
				continue;
			}
			
			// If no value is set for the property of class being worked,
			// continue to next iteration of foreach.
			// While editing, we do not need to set all the params.
			// Still would only allow a prop to be set that is inside
			// the array of props for the service.
			try {
				
				$value = static::value($data, $prop);
			} catch (Throwable $e) {
				
				continue;
			}
			
			$method = static::method($prop);
			
			$violations = static::constraints($params, $value, $prop);
			
			if ($violations) {
				
				return $violations;
			}
			
			// Set the property on the entity if validation passes and additional methods are done.
			static::$entity->$method(
				static::callbacks($params, $value)
			);
		}
		
		return static::$entity;
	}
	
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
	
	protected static function value(array $data, string $prop)
	{
		if (! array_key_exists($prop, $data)) {
			
			throw new ArrayKeyNotSetException(
				sprintf(
					'Array key "%s" not set on inbound data for a new "%s"',
					$prop, get_class(static::$entity)
				)
			);
		}
		
		return $data[$prop];
	}
	
	protected static function constraints(array $params, $value, string $prop)
	{
		if (array_key_exists('constraints', $params) && ! Validate::blank($params['constraints'])) {
			
			$errors = static::validator()->validate($value, $params['constraints']);
			
			if (count($errors) > 0) {
				$messages = [];
				
				foreach ($errors as $violation) {
					
					if (array_key_exists($prop, $messages)) {
						
						$messages[$prop] .= " {$violation->getMessage()}";
						
					} else {
						
						$messages[$prop] = $violation->getMessage();
					}
				}
				
				return $messages;
			}
		}
		
		return null;
	}
	
	protected static function callbacks(array $params, $value)
	{
		if (array_key_exists('callbacks', $params) && ! Validate::blank($params['callbacks'])) {
			
			foreach ($params['callbacks'] as $additionalMethod) {
				
				$value = call_user_func($additionalMethod, $value);
			}
		}
		
		return $value;
	}
	
	/**
	 * @return ValidatorInterface
	 */
	protected static function validator(): ValidatorInterface
	{
		if (null === static::$validator) {
			
			static::$validator = Validation::createValidator();
		}
		
		return static::$validator;
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