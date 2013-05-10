<?php

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Floor\Floor;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

/**
 * Proposal grid datasource
 *
 * @package Maps\Model\Metadata\Queries
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class ProposalsGridQuery extends QueryObjectBase {
    /** @var \Maps\Model\Floor\Floor  */
    private $floor;

    /**
     * @param Floor $floor
     */
    function __construct($floor) {
        $this->floor = $floor;
    }


    /** {@inheritdoc} */
    protected function doCreateQuery(IQueryable $repository) {
        return $repository->createQueryBuilder("c")->select("c, u.name AS author, r2.revision AS r2_revision, u2.name AS u2_name")
                ->innerJoin("c.submitted_by", "u")
                ->join("c.against_revision", 'r')
                ->leftJoin("c.in_revision", 'r2')
                ->leftJoin("c.processed_by", 'u2')
                ->where("r.floor = ?1")
                ->setParameter(1, $this->floor);
    }
}