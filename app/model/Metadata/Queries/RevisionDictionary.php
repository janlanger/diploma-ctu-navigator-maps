<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 5.4.13
 * Time: 20:19
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class RevisionDictionary extends QueryObjectBase {

    private $floor;

    function __construct($floor) {
        $this->floor = $floor;
    }


    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository) {
        return $repository->createQueryBuilder("r")->where("r.floor = ?1")->setParameter(1, $this->floor);
    }
}