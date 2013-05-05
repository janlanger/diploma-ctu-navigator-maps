<?php

namespace Maps\Model\Floor\Queries;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

/**
 * Fetches active plan of a floor
 *
 * @author Jan Langer <langeja1@fit.cvut.cz>
 * @package Maps\Model\Floor\Queries
 */
class ActivePlanQuery extends QueryObjectBase {

    /** @var array floor ids */
    private $floors;

    /**
     * @param array|int $floors ids
     */
    function __construct($floors) {
        if(is_scalar($floors)) {
            $this->floors = [$floors];
        } else {
            $this->floors = $floors;
        }

    }


    /** {@inheritdoc} */
    protected function doCreateQuery(IQueryable $repository) {
        return $repository->createQueryBuilder("p")->select("p")
            ->where("p.floor IN (:floor)")
            ->andWhere("p.published = true")
            ->setParameter("floor", $this->floors);
    }
}