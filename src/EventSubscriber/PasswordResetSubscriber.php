<?php

declare(strict_types = 1);

namespace App\EventSubscriber;

use App\Entity\Event\PasswordResetEvent;
use App\Support\Str;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PasswordResetSubscriber implements EventSubscriberInterface
{
	/**
	 * @var UserPasswordEncoderInterface
	 */
	private UserPasswordEncoderInterface $passwordEncoder;
	
	/**
	 * PasswordResetSubscriber constructor.
	 * @param  UserPasswordEncoderInterface  $passwordEncoder
	 */
	public function __construct(UserPasswordEncoderInterface $passwordEncoder)
	{
		$this->passwordEncoder = $passwordEncoder;
	}
	
	/**
	 * @return array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			PasswordResetEvent::class => ['setToken', 999],
		];
	}
	
	/**
	 * @param  PasswordResetEvent  $event
	 * @throws Exception
	 */
	public function setToken(PasswordResetEvent $event): void
	{
		$user  = $event->getEntity();
		$token = $event->getToken();
		
		// Set the token and randomize the current password for security.
		$user->setPasswordResetToken($token);
		$user->setPassword(
			$this->getPasswordEncoder()->encodePassword(
				$user,
				Str::random(128)
			)
		);
	}
	
	/**
	 * @return UserPasswordEncoderInterface
	 */
	public function getPasswordEncoder(): UserPasswordEncoderInterface
	{
		return $this->passwordEncoder;
	}
}