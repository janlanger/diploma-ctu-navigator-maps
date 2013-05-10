<?php

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;
use Maps\Model\User\User;

/**
 * All proposals of specified user
 *
 * @package Maps\Model\Metadata\Queries
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class MyProposalsQuery extends QueryObjectBase {
    /** @var int|User */
    private $user;

    /**
     * @param int|User $userId
     */
    function __construct($userId) {
        $this->user = $userId;
    }


    /** {@inheritdoc} */
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