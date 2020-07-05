<?php

declare(strict_types = 1);

namespace App\EventSubscriber;

use App\Entity\Contracts\TimeStampable;
use App\Entity\Event\TimeStampableCreatedEvent;
use App\Entity\Event\TimeStampableUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class TimeStampableEntitySubscriber implements EventSubscriberInterface
{
	/**
	 * @return array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			TimeStampableCreatedEvent::class => ['setCreationStamp', 999],
			TimeStampableUpdatedEvent::class => ['setUpdatedStamp', 999],
		];
	}
	
	/**
	 * @param  TimeStampableCreatedEvent  $event
	 */
	public function setCreationStamp(TimeStampableCreatedEvent $event): void
	{
		$entity = $event->getEntity();
		
		if ($entity->getCreatedAt() === null && $entity->hasTimestamps() && $entity instanceof TimeStampable) {
			
			$entity->setCreationTimestamps();
		}
	}
	
	/**
	 * @param  TimeStampableUpdatedEvent  $event
	 */
	public function setUpdatedStamp(TimeStampableUpdatedEvent $event): void
	{
		$entity = $event->getEntity();
		
		if ($entity->getUpdatedAt() === null && $entity->hasTimestamps() && $entity instanceof TimeStampable) {
			
			$entity->setUpdatedTimestamps();
		}
	}
}