<?php

declare(strict_types = 1);

namespace App\EventSubscriber;

use App\Entity\Contracts\Uuidable;
use App\Entity\Event\UuidableCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UuidableEntitySubscriber implements EventSubscriberInterface
{
	/**
	 * @return array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			UuidableCreatedEvent::class => ['generateUuid', 999],
		];
	}
	
	/**
	 * @param  UuidableCreatedEvent  $event
	 */
	public function generateUuid(UuidableCreatedEvent $event): void
	{
		$entity = $event->getEntity();
		
		if ($entity->getId() === null && $entity instanceof Uuidable) {
			
			$entity->generateUuid();
		}
	}
}