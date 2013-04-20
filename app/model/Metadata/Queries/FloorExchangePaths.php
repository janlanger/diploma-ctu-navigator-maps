<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 13.4.13
 * Time: 15:44
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Floor\Floor;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class FloorExchangePaths extends QueryObjectBase{

    private $nodeIds = [];
    private $revision;


    function __construct($nodeIds, $revision) {
        $this->nodeIds = $nodeIds;
        $this->revision = $revision;
    }


    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository) {
        $namespace = "Maps\\Model\\Metadata\\";

        $x = $repository->createQuery("SELECT p FROM {$namespace}Path p ".
                "JOIN p.properties p2 ".
                " WHERE p2.isFloorExchange = true AND p.revision = :revision AND ".
                "((p2.endNode IN (:ids) AND p2.startNode NOT IN (:ids) AND EXISTS ".
                    "(SELECT r.id FROM {$namespace}Revision r JOIN {$namespace}Node n WITH (n.revision=r.id) ".
                        "WHERE r.published = true AND n.properties = p2.startNode)) OR ".
                "(p2.startNode IN (:ids) AND p2.endNode NOT IN (:ids) AND EXISTS " .
                    "(SELECT r2.id FROM {$namespace}Revision r2 JOIN {$namespace}Node n2 WITH (n2.revision=r2.id) " .
                    "WHERE r2.published = true AND n2.properties = p2.endNode)))")
                ->setParameter("ids", $this->nodeIds)
                ->setParameter("revision", $this->revision);
        return $x;
    }
}