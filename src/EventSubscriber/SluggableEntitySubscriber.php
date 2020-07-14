<?php

declare(strict_types = 1);

namespace App\EventSubscriber;

use App\Entity\Contracts\Sluggable;
use App\Entity\Event\SluggableCreatedEvent;
use App\Entity\Event\SluggableEditedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SluggableEntitySubscriber implements EventSubscriberInterface
{
	/**
	 * @return array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			SluggableCreatedEvent::class => ['setCreateSlug', 999],
			SluggableEditedEvent::class  => ['setUpdateSlug', 999],
		];
	}
	
	/**
	 * @param  SluggableCreatedEvent  $event
	 */
	public function setCreateSlug(SluggableCreatedEvent $event): void
	{
		$entity = $event->getEntity();
		
		if ($entity->getSlug() === null && $entity instanceof Sluggable) {
			
			$entity->setSlug();
		}
	}
	
	public function setUpdateSlug(SluggableEditedEvent $event): void
	{
		$entity = $event->getEntity();
		
		if ($entity instanceof Sluggable) {
			
			$entity->setSlug();
		}
	}
}