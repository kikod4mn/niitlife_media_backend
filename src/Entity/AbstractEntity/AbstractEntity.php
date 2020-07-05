<?php

declare(strict_types = 1);

namespace App\Entity\AbstractEntity;

use App\Entity\Concerns\UuidableTrait;
use App\Entity\Contracts\Uuidable;

abstract class AbstractEntity implements Uuidable, \Stringable, \JsonSerializable
{
	use UuidableTrait;
	
	/**
	 * Default name for created at timestamp field.
	 * @var string
	 */
	const CREATED_AT = 'createdAt';
	
	/**
	 * Default name for updated at timestamp field.
	 * @var string
	 */
	const UPDATED_AT = 'updatedAt';
	
	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return (string) $this->getId();
	}
}