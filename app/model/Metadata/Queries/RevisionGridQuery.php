<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 28.3.13
 * Time: 21:53
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class RevisionGridQuery extends QueryObjectBase {

    private $floor;

    function __construct($floor) {
        $this->floor = $floor;
    }


    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository) {
        return $repository->createQueryBuilder("r")->select("r, u")
                ->innerJoin("r.user", "u")
                ->where("r.floor = ?1")
                ->setParameter(1, $this->floor);
    }
}