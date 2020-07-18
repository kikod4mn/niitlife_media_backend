<?php

declare(strict_types = 1);

namespace App\Controller\Concerns;

use App\Entity\User;

trait UserUniqueCheck
{
	/**
	 * Check an un persisted User object against the DB for username and email uniqueness.
	 * @param  User  $user
	 * @return array
	 */
	protected function uniqueCheck(User $user): array
	{
		$errors   = [];
		$username = $this->uniqueUsername($user->getUsername());
		$email    = $this->uniqueEmail($user->getEmail());
		
		if ($username) {
			array_push($errors, 'Username is already in use. Please choose another.');
		}
		
		if ($email) {
			array_push($errors, 'Email is already in use. Have You forgotten your password?');
		}
		
		return $errors;
	}
	
	/**
	 * @param  string  $email
	 * @return null|User
	 */
	protected function uniqueEmail(string $email): ?User
	{
		return $this->getUserRepository()->findOneBy(['email' => $email]);
	}
	
	/**
	 * @param  string  $username
	 * @return null|User
	 */
	protected function uniqueUsername(string $username): ?User
	{
		return $this->getUserRepository()->findOneBy(['username' => $username]);
	}
}