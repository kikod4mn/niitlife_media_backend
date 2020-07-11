<?php

declare(strict_types = 1);

namespace App\Service\EntityService\Contracts;

/**
 * Use this interface to mark all services that extend AbstractService for creation and editing of entities.
 */
interface AbstractServiceInterface
{
	/**
	 * @return array
	 */
	public static function getProps(): array;
}