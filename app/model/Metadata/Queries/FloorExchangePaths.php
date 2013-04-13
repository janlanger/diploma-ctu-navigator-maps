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
        $q = $repository->createQueryBuilder("p")
                ->select("p")
                ->innerJoin("p.properties", "p2")
                ->where("(p2.startNode IN (?1) AND p2.endNode NOT IN (?1))")
                ->orWhere("(p2.endNode IN (?1) AND p2.startNode NOT IN (?1))")
                ->andWhere("p2.isFloorExchange = true")
                ->setParameter(1, $this->nodeIds);
                //->setParameter(2, $this->revision);
        return $q;
    }
}