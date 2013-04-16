<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 16.4.13
 * Time: 11:38
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Floor;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class ActivePlanOfAnyFloor extends QueryObjectBase {

    private $floor;

    function __construct($floor) {
        $this->floor = $floor;
    }

    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository) {
        return $repository->createQueryBuilder("p")->select("p")
                ->where("p.floor IN (:floor)")
                ->andWhere("p.published = true")
                ->setParameter("floor", $this->floor);
    }
}