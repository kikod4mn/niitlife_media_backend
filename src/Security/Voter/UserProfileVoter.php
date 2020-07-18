<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\Entity\UserProfile;
use App\Security\Concerns\ChecksPermissions;
use App\Security\Contracts\CheckablePermissions;
use App\Security\Contracts\VotableConstants;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class UserProfileVoter extends Voter implements VotableConstants, CheckablePermissions
{
	use ChecksPermissions;
	
	/**
	 * UserVoter constructor.
	 * @param  Security  $security
	 */
	public function __construct(Security $security)
	{
		$this->setSecurity($security);
		$this->setUser();
	}
	
	/**
	 * @param  string  $attribute
	 * @param  mixed   $subject
	 * @return bool|void
	 */
	public function supports(string $attribute, $subject): bool
	{
		return in_array($attribute, [self::VIEW, self::CREATE, self::EDIT, self::DELETE])
			&& $subject instanceof UserProfile;
	}
	
	/**
	 * @param  string          $attribute
	 * @param  mixed           $subject
	 * @param  TokenInterface  $token
	 * @return bool|void
	 */
	public function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
	{
		switch ($attribute) {
			// Creation happens in event subscriber, no manual creation allowed.
			// Profile is not trashable.
			case self::CREATE:
			case self::TRASH:
			case self::RESTORE:
				return false;
			case self::VIEW:
			case self::EDIT:
			case self::DELETE:
				return $this->isUserAdmin() || $this->isOwner($subject);
		}
		
		throw new LogicException('This code should not run');
	}
}