<?php

declare(strict_types = 1);

namespace App\Entity\Contracts;

use App\Entity\User;

interface Authorable
{
	/**
	 * @return null|User
	 */
	public function getAuthor(): ?User;
	
	/**
	 * @param  User  $author
	 * @return $this|Authorable
	 */
	public function setAuthor(User $author): Authorable;
}