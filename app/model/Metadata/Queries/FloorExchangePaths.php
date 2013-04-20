<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 13.4.13
 * Time: 15:44
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Floor\Floor;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class FloorExchangePaths extends QueryObjectBase{

    private $nodeIds = [];
    private $revision;


    function __construct($nodeIds, $revision) {
        $this->nodeIds = $nodeIds;
        $this->revision = $revision;
    }


    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository) {
        $namespace = "Maps\\Model\\Metadata\\";

        $x = $repository->createQuery("SELECT f FROM {$namespace}FloorConnection f ".

                " WHERE (f.revision_one = :revision AND f.node_one IN (:nodes) AND EXISTS ".
                    "(SELECT r.id FROM {$namespace}Revision r WHERE r.id = f.revision_two AND r.published = true))".
                        " OR ".
                "(f.revision_two = :revision AND f.node_two IN (:nodes) AND EXISTS " .
                "(SELECT r2.id FROM {$namespace}Revision r2 WHERE r2.id = f.revision_one AND r2.published = true))")
                ->setParameter("nodes", $this->nodeIds)
                ->setParameter("revision", $this->revision);
        return $x;
    }
}