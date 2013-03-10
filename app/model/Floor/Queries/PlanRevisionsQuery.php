<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 28.2.13
 * Time: 19:35
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Floor;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class PlanRevisionsQuery extends QueryObjectBase{
    private $floor;

    public function __construct($floor) {
        $this->floor = $floor;
    }


    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository)
    {
        $qb = $repository->createQueryBuilder("p")
            ->select("p, u.name")
            ->join("p.user","u")
            ->where("p.floor = :floor");

        $qb->setParameter("floor",$this->floor);

        return $qb;
    }
}