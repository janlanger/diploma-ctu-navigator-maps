<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 1.5.13
 * Time: 20:03
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class OtherAffectedFloors extends QueryObjectBase{

    private $revisions = [];

    function __construct($revisions) {
        $this->revisions = $revisions;
    }


    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository) {
        return $repository->createQueryBuilder("fc")
                ->select("f.id")
                ->join("fc.revision_one", "r1")
                ->join("r1.floor", "f")
                ->where("fc.revision_two IN (?1)")
                ->setParameter(1, $this->revisions);
    }
}