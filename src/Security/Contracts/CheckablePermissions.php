<?php

declare(strict_types = 1);

namespace App\Security\Contracts;

use App\Entity\Contracts\Authorable;
use App\Entity\User;
use App\Entity\UserProfile;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Implement this interface on voters together with the matching trait for easy checking of owner or admin status to allow modification.
 */
interface CheckablePermissions
{
	/**
	 * @return null|User|UserInterface|string
	 */
	public function getUser(): ?UserInterface;
	
	/**
	 * @return $this|CheckablePermissions
	 */
	public function setUser(): self;
	
	/**
	 * @return null|Security
	 */
	public function getSecurity(): ?Security;
	
	/**
	 * @param  Security  $security
	 * @return $this|CheckablePermissions
	 */
	public function setSecurity(Security $security): self;
	
	/**
	 * @return bool
	 */
	public function hasUser(): bool;
	
	/**
	 * @return bool
	 */
	public function hasSecurity(): bool;
	
	/**
	 * @return bool
	 */
	public function isUserAdmin(): bool;
	
	/**
	 * @param  Authorable|User|UserProfile|mixed  $subject
	 * @return bool
	 */
	public function isOwner($subject): bool;
}