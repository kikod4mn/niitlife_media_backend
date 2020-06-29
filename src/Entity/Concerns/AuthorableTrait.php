<?php

declare(strict_types = 1);

namespace App\Entity\Concerns;

use App\Entity\Contracts\Authorable;
use App\Entity\User;

trait AuthorableTrait
{
	/**
	 * @return null|User
	 */
	public function getAuthor(): ?User
	{
		return $this->author;
	}
	
	/**
	 * @param  User  $author
	 * @return $this|Authorable
	 */
	public function setAuthor(User $author): Authorable
	{
		$this->author = $author;
		
		return $this;
	}
}