<?php

declare(strict_types = 1);

namespace App\Entity\Event;

use App\Entity\Contracts\Authorable;
use Symfony\Contracts\EventDispatcher\Event;

class AuthorableCreatedEvent extends Event
{
	/** @var string */
	const NAME = 'authorable.created';
	
	/**
	 * @var Authorable
	 */
	private Authorable $entity;
	
	/**
	 * AuthorableCreatedEvent constructor.
	 * @param  Authorable  $entity
	 */
	public function __construct(Authorable $entity)
	{
		$this->entity = $entity;
	}
	
	/**
	 * @return null|Authorable
	 */
	public function getEntity(): ?Authorable
	{
		return $this->entity;
	}
}