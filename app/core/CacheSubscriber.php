<?php

namespace Maps\Core;

use Doctrine\ORM\Event;
use Doctrine\ORM\Events;
use Maps\Model\BaseEntity;
use Nette\Caching\Cache;
use Nette\Environment;

/**
 * Doctrine cache subscriber. Implements ability to automatically delete cache items based by tagging
 * them with depended entity names.
 *
 * eg. tag some cached content with entity name -> it will be automatically deleted when this entity changes.
 *
 * @author Jan Langer
 */
class CacheSubscriber implements \Doctrine\Common\EventSubscriber {
    /** @var \Nette\Caching\IStorage */
    private $cacheStorage;


    /**
     * @param \Nette\Caching\IStorage $storage Nette cache storage
     */
    public function __construct(\Nette\Caching\IStorage $storage) {
        $this->cacheStorage = $storage;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents() {
        return array(Events::onFlush);
    }


    /**
     * @param Event\OnFlushEventArgs $args
     */
    public function onFlush(Event\OnFlushEventArgs $args) {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $tags = array();

        /** @var $entity BaseEntity */
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $tags[] = get_class($entity);
            $tags = array_merge($entity->getCacheKeys(), $tags);
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $tags[] = get_class($entity);
            $tags = array_merge($entity->getCacheKeys(), $tags);
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $tags[] = get_class($entity);
            $tags = array_merge($entity->getCacheKeys(), $tags);
        }
        $cache = new Cache($this->cacheStorage);
        $cache->clean(array(
            Cache::TAGS => array_unique($tags)
        ));
    }
}