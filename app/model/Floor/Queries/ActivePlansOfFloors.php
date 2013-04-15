<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 15.4.13
 * Time: 22:17
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Floor;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class ActivePlansOfFloors extends QueryObjectBase {

    private $floors = [];

    function __construct($floors) {
        $this->floors = $floors;
    }


    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository) {
        return $repository->createQueryBuilder("p")
                ->select("p.id, f.id AS floor")
                ->innerJoin("p.floor", "f")
                ->where("p.published = true")
                ->andWhere("p.floor IN (?1)")
                ->setParameter(1, $this->floors);
    }
}