<?php

declare(strict_types = 1);

namespace App\Entity\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserCreatedEvent extends Event
{
	/** @var string */
	const NAME = 'user.created';
	
	/**
	 * @var User
	 */
	private User $user;
	
	/**
	 * UserCreatedEvent constructor.
	 * @param  User  $user
	 */
	public function __construct(User $user)
	{
		$this->user = $user;
	}
	
	/**
	 * @return null|User
	 */
	public function getUser(): ?User
	{
		return $this->user;
	}
}