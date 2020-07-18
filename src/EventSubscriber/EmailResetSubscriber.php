<?php

declare(strict_types = 1);

namespace App\EventSubscriber;

use App\Entity\Event\EmailResetEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EmailResetSubscriber implements EventSubscriberInterface
{
	/**
	 * @return array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			EmailResetEvent::class => ['setToken', 999],
		];
	}
	
	/**
	 * @param  EmailResetEvent  $event
	 */
	public function setToken(EmailResetEvent $event): void
	{
		$user  = $event->getEntity();
		$token = $event->getToken();
		
		$user->setEmailResetToken($token);
		// Set the current email as the old email as backup.
		$user->setOldEmail($user->getEmail());
	}
}