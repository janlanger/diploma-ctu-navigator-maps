<?php
namespace Grido;
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 5.2.13
 * Time: 19:59
 * To change this template use File | Settings | File Templates.
 */
class Doctrine extends \Nette\Object implements IDataSource{
    private $queryBuilder;

    public function __construct(\Doctrine\DBAL\Query\QueryBuilder $qb) {
        $this->queryBuilder = $qb;
    }


    /**
     * @param array $condition
     * @return void
     */
    function filter(array $condition) {
        // TODO: Implement filter() method.
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return void
     */
    function limit($offset, $limit) {
        // TODO: Implement limit() method.
    }

    /**
     * @param array $sorting
     * @return void
     */
    function sort(array $sorting) {
        // TODO: Implement sort() method.
    }

    /**
     * @return array
     */
    function getData() {
        // TODO: Implement getData() method.
    }

    /**
     * @return int
     */
    function getCount() {
        $this->queryBuilder->
    }
}
