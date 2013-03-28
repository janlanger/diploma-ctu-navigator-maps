<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 28.3.13
 * Time: 22:23
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class ProposalsGridQuery extends QueryObjectBase {

    private $floor;

    function __construct($floor) {
        $this->floor = $floor;
    }


    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository) {
        return $repository->createQueryBuilder("c")->select("c, u.name AS author, r2.revision, u2.name")
                ->innerJoin("c.submitted_by", "u")
                ->join("c.against_revision", 'r')
                ->leftJoin("c.in_revision", 'r2')
                ->leftJoin("c.processed_by", 'u2')
                ->where("r.floor = ?1")
                ->setParameter(1, $this->floor);
    }
}