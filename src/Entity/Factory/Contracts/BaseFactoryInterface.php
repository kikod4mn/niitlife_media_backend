<?php

declare(strict_types = 1);

namespace App\Entity\Factory\Contracts;

use App\Entity\Factory\Exception\ArrayKeyNotSetException;

/**
 * Defines the necessary methods for a Factory to use BaseFactoryTrait.
 */
interface BaseFactoryInterface
{
	/**
	 * @param  string|array  $data
	 * @return mixed
	 */
	public static function make($data);
	
	/**
	 * @param  string|array  $data
	 * @param  mixed         $entity
	 * @return mixed
	 */
	public static function update($data, $entity);
	
	/**
	 * @param  array  $data
	 * @param  mixed  $entity
	 * @return mixed
	 */
	public function edit(array $data, $entity);
	
	/**
	 * @param  array  $data
	 * @return mixed
	 */
	public function create(array $data);
	
	/**
	 * @param  array  $data
	 * @throws ArrayKeyNotSetException
	 */
	public function validArrayKeys(array $data): void;
}