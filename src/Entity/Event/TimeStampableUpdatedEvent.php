<?php

declare(strict_types = 1);

namespace App\Entity\Event;

use App\Entity\Contracts\TimeStampable;
use Symfony\Contracts\EventDispatcher\Event;

class TimeStampableUpdatedEvent extends Event
{
	/** @var string */
	const NAME = 'timestampable.updated';
	
	/**
	 * @var TimeStampable
	 */
	private TimeStampable $entity;
	
	/**
	 * TimeStampableCreatedEvent constructor.
	 * @param  TimeStampable  $entity
	 */
	public function __construct(TimeStampable $entity)
	{
		$this->entity = $entity;
	}
	
	/**
	 * @return null|TimeStampable
	 */
	public function getEntity(): ?TimeStampable
	{
		return $this->entity;
	}
}