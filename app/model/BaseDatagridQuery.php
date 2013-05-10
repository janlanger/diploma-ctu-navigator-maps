<?php
namespace Maps\Model;
use Maps\Model\Persistence\QueryObjectBase;
use Maps\Model\Persistence\IQueryable;

/**
 * Basic datagrid query.
 * Use when you don't require any join, rename, or aggregation
 *
 * @package Maps\Model
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class BaseDatagridQuery extends QueryObjectBase {

    /** {@inheritdoc} */
    protected function doCreateQuery(IQueryable $repository) {
        $qb = $repository->createQueryBuilder("b")->select();
        return $qb;
    }
}
