<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 16.7.13
 * Time: 20:51
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class NodeByRoom extends QueryObjectBase {

    private $room;

    public function __construct($room) {
        $this->room = $room;
    }

    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository) {
        return $repository->createQueryBuilder("n")
                ->join("n.properties", "p")
                ->where("p.room = :room")
                ->setParameter("room", $this->room)
                ->setMaxResults(1);
    }
}