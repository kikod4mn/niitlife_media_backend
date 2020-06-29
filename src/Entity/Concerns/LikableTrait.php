<?php

declare(strict_types = 1);

namespace App\Entity\Concerns;

use App\Entity\User;
use Doctrine\Common\Collections\Collection;

trait LikableTrait
{
	/**
	 * @return null|Collection|User[]
	 */
	public function getLikedBy(): Collection
	{
		return $this->likedBy;
	}
	
	/**
	 * Like a post if not already liked.
	 * @param  User  $user
	 */
	public function like(User $user): void
	{
		// Author cannot like own post
		if ($user->getId() === $this->getId()) {
			return;
		}
		
		if (! $this->likedBy->contains($user)) {
			$this->likedBy->add($user);
		}
	}
	
	/**
	 * Dislike a post if previously liked.
	 * @param  User  $user
	 */
	public function unlike(User $user): void
	{
		// Author cannot un-like own post
		if ($user->getId() === $this->getId()) {
			return;
		}
		
		if ($this->likedBy->contains($user)) {
			$this->likedBy->removeElement($user);
		}
	}
	
	/**
	 * Helper to get quick like count for a comment.
	 * @return int
	 */
	public function likesCount(): ?int
	{
		return $this->likedBy->count();
	}
}