<?php

namespace Maps\Model\Floor\Queries;


use Maps\Model\Floor\Floor;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

/**
 * Unpublish floor pland
 *
 * @author Jan Langer <langeja1@fit.cvut.cz>
 * @package Maps\Model\Floor\Queries
 */
class DeactivatePlansOfFloorQuery extends QueryObjectBase {
    /** @var Floor */
    private $floor;

    /**
     * @param Floor $floor
     */
    function __construct(Floor $floor) {
        $this->floor = $floor;
    }


    /** {@inheritdoc} */
    protected function doCreateQuery(IQueryable $repository) {
        $q = $repository->createQueryBuilder("p");
        $q->update('Maps\\Model\\Floor\\Plan','p')
            ->set("p.published", $q->expr()->literal(FALSE))
            ->where("p.floor = :floor")
            ->andWhere("p.published = :published")
            ->setParameter('floor', $this->floor)
            ->setParameter("published", TRUE);

        return $q;
    }
}