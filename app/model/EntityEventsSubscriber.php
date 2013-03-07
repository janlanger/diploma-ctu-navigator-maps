<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 7.3.13
 * Time: 10:47
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model;


use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Maps\Model\Floor\Plan;

class EntityEventsSubscriber implements  EventSubscriber {

    public function prePersist(LifecycleEventArgs $args) {
        $entity = $args->getEntity();
        if($entity instanceof Plan) {
            $q = $args->getEntityManager()->createQuery("SELECT p.revision FROM Maps\\Model\\Floor\\Plan p WHERE p.floor=:floor ORDER by p.revision DESC");
            $q->setMaxResults(1);
            $q->setParameter('floor',$entity->floor);
            $lastRevision = $q->getSingleResult();
            $newRev = 1;
            if(is_array($lastRevision)) {
                $newRev = $lastRevision['revision']+1;
            }
            $entity->revision = $newRev;
            $entity->revision = $newRev;
        }
    }
    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    function getSubscribedEvents() {
        return [Events::prePersist];
    }
}