<?php

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Metadata\Revision;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

/**
 * Floor connections to this floor revision from other floors
 *
 * @package Maps\Model\Metadata\Queries
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class ActiveFloorConnections extends QueryObjectBase {
    /** @var \Maps\Model\Metadata\Revision  */
    private $revision;

    /**
     * @param Revision $revision
     */
    function __construct(Revision $revision) {
        $this->revision = $revision;
    }


    /** {@inheritdoc} */
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