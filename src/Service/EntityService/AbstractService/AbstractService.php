<?php

declare(strict_types = 1);

namespace App\Service\EntityService\AbstractService;

use App\Service\EntityService\Exception\ArrayKeyNotSetException;
use App\Service\EntityService\Exception\ClassConstantNotDefinedException;
use App\Service\EntityService\Exception\InvalidArrayKeysException;
use App\Service\EntityService\Exception\MethodNotFoundException;
use App\Support\Validate;
use LogicException;
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
	private static array $editingDenied = [];
	
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
	 * @param $data
	 * @return mixed
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
	 * @return mixed
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
		foreach (static::$props as $prop => $params) {
			
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
		
		return static::$entity;
	}
	
	/**
	 * @param  array  $data
	 * @return mixed
	 * @throws MethodNotFoundException
	 */
	protected static function edit(array $data)
	{
		foreach (static::$props as $prop => $params) {
			
			// If editing is denied on the prop, continue to next loop.
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