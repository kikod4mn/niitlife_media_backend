<?php

declare(strict_types = 1);

namespace App\Entity\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class EmailResetEvent extends Event
{
	/** @var string */
	const NAME = 'email.reset';
	
	/**
	 * @var User
	 */
	private User    $entity;
	
	private ?string $token;
	
	/**
	 * PasswordResetEvent constructor.
	 * @param  User         $entity
	 * @param  null|string  $token
	 */
	public function __construct(User $entity, ?string $token = null)
	{
		$this->entity = $entity;
		$this->token  = $token;
	}
	
	/**
	 * @return null|User
	 */
	public function getEntity(): ?User
	{
		return $this->entity;
	}
	
	public function getToken(): ?string
	{
		return $this->token;
	}
}