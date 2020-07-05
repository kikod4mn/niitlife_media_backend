<?php

declare(strict_types = 1);

namespace App\EventSubscriber;

use App\Entity\Contracts\Sluggable;
use App\Entity\Event\SluggableCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SluggableEntitySubscriber implements EventSubscriberInterface
{
	/**
	 * @return array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			SluggableCreatedEvent::class => ['setSlug', 999],
		];
	}
	
	/**
	 * @param  SluggableCreatedEvent  $event
	 */
	public function setSlug(SluggableCreatedEvent $event): void
	{
		$entity = $event->getEntity();
		
		if ($entity->getSlug() === null && $entity instanceof Sluggable) {
			
			$entity->setSlug();
		}
	}
}