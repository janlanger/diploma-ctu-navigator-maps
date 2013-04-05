<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 22.3.13
 * Time: 12:18
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Metadata\Changeset;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class CountUnprocessedProposals extends QueryObjectBase {

    private $floor;



    public function __construct($floor) {
        $this->floor = $floor;
    }



    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository) {
        return $repository->createQueryBuilder("c")->select("count(c.id)")
            ->join("c.against_revision", "r")
            ->where("r.floor = :floor")
            ->andWhere("c.state = :state")
            ->setParameter("floor", $this->floor)
                ->setParameter("state", Changeset::STATE_NEW);
    }
}