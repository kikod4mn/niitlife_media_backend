<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\Entity\Contracts\Publishable;
use App\Entity\PostComment;
use App\Entity\User;
use App\Security\Concerns\ChecksPermissions;
use App\Security\Contracts\CheckablePermissions;
use App\Security\Contracts\VotableConstants;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class PostCommentVoter extends Voter implements VotableConstants, CheckablePermissions
{
	use ChecksPermissions;
	
	/**
	 * CommentVoter constructor.
	 * @param  Security  $security
	 */
	public function __construct(Security $security)
	{
		$this->setSecurity($security);
	}
	
	/**
	 * @param  string  $attribute
	 * @param  mixed   $subject
	 * @return bool|void
	 */
	public function supports(string $attribute, $subject): bool
	{
		return in_array($attribute, [self::VIEW, self::CREATE, self::EDIT, self::DELETE])
			&& $subject instanceof PostComment;
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
			case self::VIEW:
				return $subject->isPublished();
			case self::CREATE:
				return $this->isUserFullyAuth()
					&& $this->getSecurity()->isGranted(User::ROLE_COMMENTATOR)
					&& ! $this->getSecurity()->isGranted(User::ROLE_MUTED);
			case self::EDIT:
			case self::PUBLISH:
			case self::TRASH:
			case self::RESTORE:
			case self::DELETE:
				return $this->isUserAdmin()
					|| $this->isOwner($subject)
					&& $this->getSecurity()->isGranted(User::ROLE_COMMENTATOR)
					&& ! $this->getSecurity()->isGranted(User::ROLE_MUTED);
		}
		
		throw new LogicException('This code should not run');
	}
}