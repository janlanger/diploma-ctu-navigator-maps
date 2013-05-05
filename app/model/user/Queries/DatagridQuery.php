<?php
namespace Maps\Model\ACL;
use Maps\Model\Persistence\IQueryable;

/**
 * Roles datagrid
 *
 * @package Maps\Model\ACL
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class DatagridQuery extends \Maps\Model\Persistence\QueryObjectBase {

    /** {@inheritdoc} */
    protected function doCreateQuery(IQueryable $repository) {
        $qb = $repository->createQueryBuilder("r")->select("r, r1.name")
            ->leftJoin("r.parent","r1");
        return $qb;
    }
}
