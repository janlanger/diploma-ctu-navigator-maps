<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 21.4.13
 * Time: 15:15
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Metadata\Revision;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class ActiveFloorConnections extends QueryObjectBase {

    private $revision;

    function __construct(Revision $revision) {
        $this->revision = $revision;
    }


    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository) {
        return $repository->createQueryBuilder("f")
                ->select("f, r1, r2, n1, n2")
                ->innerJoin("f.revision_one", "r1")
                ->innerJoin("f.revision_two", "r2")
                ->innerJoin("f.node_one", 'n1')
                ->innerJoin("f.node_two", "n2")
                ->where("r2.published = true")
                ->andWhere("r1 = ?1")
                ->setParameter(1, $this->revision);
    }
}