<?php

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

/**
 * Paths with are connected to these nodes
 *
 * @package Maps\Model\Metadata\Queries
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class PathPropertiesByNodes extends QueryObjectBase
{
    /** @var int[][] */
    private $nodeSpec;

    /**
     * @param int[][] $nodeSpec
     */
    function __construct($nodeSpec)
    {
        $this->nodeSpec = $nodeSpec;
    }


    /** {@inheritdoc} */
    protected function doCreateQuery(IQueryable $repository)
    {
        $q = $repository->createQueryBuilder("p")->select("p");
        $iter = 1;
        foreach($this->nodeSpec as $node) {
            $q->orWhere("(p.startNode = ?$iter AND p.endNode = ?".($iter+1).")")
                ->setParameter($iter, $node[0])
                ->setParameter($iter+1, $node[1]);
            $iter += 2;
        }
        return $q;

    }
}