<?php

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

/**
 * Properties of specified node ids
 *
 * @package Maps\Model\Metadata\Queries
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class NodePropertiesQuery extends QueryObjectBase {
    /** @var int[] */
    private $in = [];

    /**
     * @param int[] $in
     */
    function __construct($in)
    {
        $this->in = $in;
    }


    /** {@inheritdoc} */
    protected function doCreateQuery(IQueryable $repository)
    {
        return $repository->createQueryBuilder("p")->select("p")
                ->where("p.id IN (:in)")
                ->setParameter("in", $this->in);
    }
}