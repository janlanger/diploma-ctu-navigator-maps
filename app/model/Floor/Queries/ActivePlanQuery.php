<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 8.3.13
 * Time: 21:52
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Floor;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class ActivePlanQuery extends QueryObjectBase {

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
            ->where("p.floor = :floor")
            ->andWhere("p.published = true")
            ->setParameter("floor", $this->floor);
    }
}