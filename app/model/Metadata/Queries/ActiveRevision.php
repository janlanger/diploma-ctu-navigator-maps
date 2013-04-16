<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 13.3.13
 * Time: 21:47
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Floor\Floor;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class ActiveRevision extends QueryObjectBase {

    private $floor;

    function __construct($floor)
    {
        if(is_scalar($floor)) {
            $floor = [$floor];
        }
        $this->floor = $floor;
    }


    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository)
    {
        return $repository->createQueryBuilder("r")->select("r")
            ->where("r.floor IN (:floor)")
            ->andWhere("r.published = true")
            ->setParameter("floor", $this->floor);
    }
}