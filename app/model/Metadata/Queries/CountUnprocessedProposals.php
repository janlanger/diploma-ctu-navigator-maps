<?php
namespace Maps\Model\Metadata\Queries;


use Maps\Model\Floor\Floor;
use Maps\Model\Metadata\Changeset;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

/**
 * Unprocessed proposals for provided floor
 *
 * @package Maps\Model\Metadata\Queries
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class CountUnprocessedProposals extends QueryObjectBase {
    /** @var Floor */
    private $floor;

    /**
     * @param Floor $floor
     */
    public function __construct($floor) {
        $this->floor = $floor;
    }



    /** {@inheritdoc} */
    protected function doCreateQuery(IQueryable $repository) {
        return $repository->createQueryBuilder("c")->select("count(c.id)")
            ->join("c.against_revision", "r")
            ->where("r.floor = :floor")
            ->andWhere("c.state = :state")
            ->setParameter("floor", $this->floor)
                ->setParameter("state", Changeset::STATE_NEW);
    }
}