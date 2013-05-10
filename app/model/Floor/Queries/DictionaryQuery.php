<?php

namespace Maps\Model\Floor\Queries;


use Doctrine\ORM\Query\Expr;
use Maps\Model\Building\Building;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

/**
 * Fetches pair id => floor name/number of a building(s)
 *
 * @author Jan Langer <langeja1@fit.cvut.cz>
 * @package Maps\Model\Floor\Queries
 */
class DictionaryQuery extends QueryObjectBase {

    /** @var int|Building|null  */
    private $building;

    /**
     * @param Building|int|null $building
     */
    function __construct($building = NULL) {
        $this->building = $building;
    }


    /** {@inheritdoc} */
    protected function doCreateQuery(IQueryable $repository)
    {
       /* $q = $repository->createQuery("SELECT f.id, COALESCE(f.name, CONCAT(f.floor_number, ' NP')) as name FROM ".__NAMESPACE__.'\\Floor f WHERE f.building = ?1')
        ->setParameter(1, $this->building);
        return $q;*/
        $q = $repository->createQueryBuilder("f")->select("f.id");
        $q->addSelect(new Expr\Func('COALESCE', ['f.name','f.floor_number'])." as name");
        if($this->building) {
            $q->where("f.building = ?1")
                ->setParameter(1, $this->building);
        }
        return $q;
    }
}