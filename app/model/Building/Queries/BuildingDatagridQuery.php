<?php
namespace Maps\Model\Building\Queries;


use Maps\Model\Metadata\Changeset;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

/**
 * Fetch data for buildings grid
 * Includes count of unprocessed proposals for every building.
 *
 * @package Maps\Model\Building\Queries
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class BuildingDatagridQuery extends QueryObjectBase {

    /** {@inheritdoc} */
    protected function doCreateQuery(IQueryable $repository) {
        $qb2 = $repository->createQueryBuilder()
                ->select("COUNT(c.id)")
                ->from("Maps\\Model\\Metadata\\Changeset", "c")
                ->join("c.against_revision", "r")
                ->join("r.floor", "f")
                ->andWhere("f.building = b.id")
                ->andWhere("c.state = '". Changeset::STATE_NEW."'");
        $qb = $repository->createQueryBuilder("b")->select("b, (".$qb2->getDQL().") AS change_count");


        return $qb;
    }
}