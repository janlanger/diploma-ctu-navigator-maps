<?php

namespace Maps\Core;

use Doctrine\ORM\Events, Doctrine\ORM\Event;
use Nette\Environment;
use Nette\Caching\Cache;

/**
 * Cache subscriber
 *
 * @author Jan Langer
 */
class CacheSubscriber implements \Doctrine\Common\EventSubscriber
{
    /** @var \Nette\Caching\IStorage */
    private $cacheStorage;


    public function __construct(\Nette\Caching\IStorage $storage) {
        $this->cacheStorage = $storage;
    }

    public function getSubscribedEvents()
	{
		return array(Events::onFlush);
	}



	public function onFlush(Event\OnFlushEventArgs $args)
	{
		$em = $args->getEntityManager();
		$uow = $em->getUnitOfWork();

		$tags = array();

                foreach ($uow->getScheduledEntityInsertions() as $entity) {
                        $tags[] = get_class($entity);
                        $tags = array_merge($entity->getCacheKeys(),$tags);
                }

		foreach ($uow->getScheduledEntityDeletions() as $entity) {
			$tags[] = get_class($entity);
                        $tags = array_merge($entity->getCacheKeys(),$tags);
		}

		foreach ($uow->getScheduledEntityUpdates() as $entity) {
			$tags[] = get_class($entity);
			$tags = array_merge($entity->getCacheKeys(),$tags);
		}
        $cache = new Cache($this->cacheStorage);
		$cache->clean(array(
			Cache::TAGS => array_unique($tags)
		));
	}
}