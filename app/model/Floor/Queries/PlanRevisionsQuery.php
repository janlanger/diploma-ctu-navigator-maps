<?php

namespace Maps\Model\Floor\Queries;


use Maps\Model\Floor\Floor;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

/**
 * Plan revision of one floor
 *
 * @package Maps\Model\Floor\Queries
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class PlanRevisionsQuery extends QueryObjectBase{
    /** @var int|\Maps\Model\Floor\Floor  */
    private $floor;

    /**
     * @param Floor|int $floor
     */
    public function __construct($floor) {
        $this->floor = $floor;
    }


    /** {@inheritdoc} */
    protected function doCreateQuery(IQueryable $repository)
    {
        $qb = $repository->createQueryBuilder("p")
            ->select("p, u.name")
            ->join("p.user","u")
            ->where("p.floor = :floor");

        $qb->setParameter("floor",$this->floor);

        return $qb;
    }
}