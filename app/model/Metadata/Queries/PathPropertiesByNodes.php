<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 27.3.13
 * Time: 22:11
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class PathPropertiesByNodes extends QueryObjectBase
{

    private $nodeSpec;

    function __construct($nodeSpec)
    {
        $this->nodeSpec = $nodeSpec;
    }


    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
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