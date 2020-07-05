<?php

declare(strict_types = 1);

namespace App\Entity\Event;

use App\Entity\Contracts\Uuidable;
use Symfony\Contracts\EventDispatcher\Event;

class UuidableCreatedEvent extends Event
{
	/** @var string */
	const NAME = 'uuidable.created';
	
	/**
	 * @var Uuidable
	 */
	private Uuidable $entity;
	
	/**
	 * UuidableCreatedEvent constructor.
	 * @param  Uuidable  $entity
	 */
	public function __construct(Uuidable $entity)
	{
		$this->entity = $entity;
	}
	
	/**
	 * @return null|Uuidable
	 */
	public function getEntity(): ?Uuidable
	{
		return $this->entity;
	}
}