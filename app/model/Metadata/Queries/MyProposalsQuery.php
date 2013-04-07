<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 7.4.13
 * Time: 21:28
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class MyProposalsQuery extends QueryObjectBase {

    private $user;

    function __construct($userId) {
        $this->user = $userId;
    }


    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository) {
        return $repository->createQueryBuilder("c")->select("c, r2.revision AS r2_revision, u2.name AS u2_name, b, f")
                ->join("c.against_revision", 'r')
                ->leftJoin("c.in_revision", 'r2')
                ->leftJoin("c.processed_by", 'u2')
                ->join('r.floor', 'f')
                ->join('f.building', 'b')
                ->where("c.submitted_by = ?1")
                ->setParameter(1, $this->user);
    }
}