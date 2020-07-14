<?php

declare(strict_types = 1);

namespace App\Entity\Event;

use App\Entity\Contracts\Sluggable;
use Symfony\Contracts\EventDispatcher\Event;

class SluggableEditedEvent extends Event
{
	/** @var string */
	const NAME = 'sluggable.created';
	
	/**
	 * @var Sluggable
	 */
	private Sluggable $entity;
	
	/**
	 * SluggableCreatedEvent constructor.
	 * @param  Sluggable  $entity
	 */
	public function __construct(Sluggable $entity)
	{
		$this->entity = $entity;
	}
	
	/**
	 * @return null|Sluggable
	 */
	public function getEntity(): ?Sluggable
	{
		return $this->entity;
	}
}