<?php

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Floor\Floor;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

/**
 * Revision grid datasource
 *
 * @package Maps\Model\Metadata\Queries
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class RevisionGridQuery extends QueryObjectBase {
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
        return $repository->createQueryBuilder("r")->select("r, u")
                ->innerJoin("r.user", "u")
                ->where("r.floor = ?1")
                ->setParameter(1, $this->floor);
    }
}