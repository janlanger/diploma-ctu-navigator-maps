<?php

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Metadata\Revision;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

/**
 * IDs of floors with have connection to this revision
 *
 * @package Maps\Model\Metadata\Queries
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class OtherAffectedFloors extends QueryObjectBase{
    /** @var Revision[] */
    private $revisions = [];

    /**
     * @param Revision[] $revisions
     */
    function __construct($revisions) {
        $this->revisions = $revisions;
    }


    /** {@inheritdoc} */
    protected function doCreateQuery(IQueryable $repository) {
        return $repository->createQueryBuilder("fc")
                ->select("f.id")
                ->join("fc.revision_one", "r1")
                ->join("r1.floor", "f")
                ->where("fc.revision_two IN (?1)")
                ->setParameter(1, $this->revisions);
    }
}