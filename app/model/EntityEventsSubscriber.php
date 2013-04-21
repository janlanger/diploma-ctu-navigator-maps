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
use Doctrine\ORM\NoResultException;
use Maps\Model\Floor\Plan;
use Maps\Model\Metadata\FloorConnection;
use Maps\Model\Metadata\NodeProperties;
use Maps\Model\Metadata\Path;
use Maps\Model\Metadata\Queries\FloorExchangePaths;
use Maps\Model\Metadata\Revision;

class EntityEventsSubscriber implements  EventSubscriber {

    public function prePersist(LifecycleEventArgs $args) {
        $entity = $args->getEntity();
        if($entity instanceof Plan) {
            $q = $args->getEntityManager()->createQuery("SELECT p.revision FROM Maps\\Model\\Floor\\Plan p WHERE p.floor=:floor ORDER by p.revision DESC");
            $q->setMaxResults(1);
            $q->setParameter('floor',$entity->floor);
            try {
                $lastRevision = $q->getSingleScalarResult();
                $entity->revision =  $lastRevision+1;
            } catch (NoResultException $e) {
                $entity->setRevision(1);
            }
        }

        if ($entity instanceof Revision) {
            $q = $args->getEntityManager()->createQuery("SELECT p.revision FROM Maps\\Model\\Metadata\\Revision p WHERE p.floor=:floor ORDER by p.revision DESC");
            $q->setMaxResults(1);
            $q->setParameter('floor', $entity->floor);
            try {
                $lastRevision = $q->getSingleScalarResult();
                $entity->revision = $lastRevision + 1;
            } catch (NoResultException $e) {
                $entity->setRevision(1);
            }
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