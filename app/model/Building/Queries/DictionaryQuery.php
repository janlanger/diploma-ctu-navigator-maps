<?php

namespace Maps\Model\Building\Queries;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

/**
 * Loads pair id => name for all buildings
 *
 * @package Maps\Model\Building\Queries
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class DictionaryQuery extends QueryObjectBase {

    /** {@inheritdoc} */
    protected function doCreateQuery(IQueryable $repository)
    {
        return $repository->createQueryBuilder("b")->select("b.id, b.name");
    }
}