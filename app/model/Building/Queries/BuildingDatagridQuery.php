<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 9.4.13
 * Time: 16:01
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Building\Queries;


use Maps\Model\Metadata\Changeset;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class BuildingDatagridQuery extends QueryObjectBase {

    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
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