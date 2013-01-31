<?php
namespace SeriesCMS\Components\TreeView;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

use Doctrine,
	Doctrine\ORM\Query\Expr;

/**
 * Query Builder based data source
 * @author Michael Moravec
 * @author Štěpán Svoboda
 */
class QueryBuilder extends \Nette\Object
{
	const MAP_PROPERTIES = 1;
	const MAP_OBJECTS = 2;

	/**
	 * @var Doctrine\ORM\QueryBuilder Query builder instance
	 */
	private $qb;

	/**

	 * @var integer
	 */
	public $parentColumn;

	/**
	 * @var array Fetched data
	 */
	private $data = array();

	/**
	 * @var int Total data count
	 */
	public $idColumn;

	/**
	 * Store given query builder instance
	 * @param QueryBuilder $qb
	 * @return QueryBuilder
	 */
	public function __construct(Doctrine\ORM\QueryBuilder $qb)
	{
		$this->qb = $qb;
	}
        
	/**
	 * Get iterator over data source items
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->fetch());
	}

	/**
	 * Fetches and returns the result data.
	 * @return array
	 */
	public function fetch()
	{
            
		$this->data = ($this->qb->getQuery()->getScalarResult());
                                

		

		return $this->walkArray();
	}
        
        private function walkArray($parent = NULL) {
            $data = array();
            foreach($this->data as $key => $value) {
                if($value[$this->parentColumn] == $parent) {
                    $data[$key] = $value;
                    unset($this->data[$key]);
                    if(count($this->data)) {
                        $data[$key]['children'] = $this->walkArray($value[$this->idColumn]);
                    }
                }
            }
            return $data;
        }
        
}

?>
