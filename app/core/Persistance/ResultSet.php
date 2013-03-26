<?php


namespace Maps\Model\Persistence;

use Doctrine\ORM;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Pagination\Paginator as ResultPaginator;
use Nette;
use Nette\Utils\Strings;
use Nette\Utils\Paginator as UIPaginator;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ResultSet extends Nette\Object implements \Countable, \IteratorAggregate
{

	/**
	 * @var int
	 */
	private $totalCount;

	/**
	 * @var \Doctrine\ORM\Query
	 */
	private $query;

	/**
	 * @var \Doctrine\ORM\Tools\Pagination\Paginator
	 */
	private $paginatedQuery;



	/**
	 * @param \Doctrine\ORM\QueryBuilder|\Doctrine\ORM\Query $query
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct($query)
	{
		if ($query instanceof ORM\QueryBuilder) {
			$this->query = $query->getQuery();

		} elseif ($query instanceof ORM\AbstractQuery) {
			$this->query = $query;

		} else {
			throw new \InvalidArgumentException("Given argument is not instanceof Query or QueryBuilder.");
		}
	}



	/**
	 * @param string|array $columns
	 *
	 * @throws \InvalidStateException
	 * @return ResultSet
	 */
	public function applySorting($columns)
	{
		if ($this->paginatedQuery !== NULL) {
			throw new \InvalidStateException("Cannot modify result set, that was fetched from storage.");
		}

		$sorting = array();
		foreach (is_array($columns) ? $columns : func_get_args() as $column) {
			$lColumn = Strings::lower($column);
			if (!Strings::endsWith($lColumn, ' desc') && !Strings::endsWith($lColumn, ' asc')) {
				$column .= ' ASC';
			}
			$sorting[] = $column;
		}

		if ($sorting) {
			$dql = $this->query->getDQL();
			$dql .= !$this->query->contains('ORDER BY') ? ' ORDER BY ' : ', ';
			$dql .= implode(', ', $sorting);
			$this->query->setDQL($dql);
		}

		return $this;
	}



	/**
	 * @param int $offset
	 * @param int $limit
	 *
	 * @throws \Nette\InvalidStateException
	 * @return ResultSet
	 */
	public function applyPaging($offset, $limit)
	{
		if ($this->paginatedQuery !== NULL) {
			throw new \Nette\InvalidStateException("Cannot modify result set, that was fetched from storage.");
		}

		$this->query->setFirstResult($offset);
		$this->query->setMaxResults($limit);
		return $this;
	}



	/**
	 * @param \Nette\Utils\Paginator $paginator
	 * @return ResultSet
	 */
	public function applyPaginator(UIPaginator $paginator)
	{
		$this->applyPaging($paginator->getOffset(), $paginator->getLength());
		return $this;
	}



	/**
	 * @return bool
	 */
	public function isEmpty()
	{
		$count = $this->getTotalCount();
		$offset = $this->query->getFirstResult();
		return $count <= $offset;
	}



	/**
	 * @return int
	 */
	public function getTotalCount()
	{
		if ($this->totalCount === NULL) {
			try {
				$this->totalCount = $this->getPaginatedQuery()->count();

			} catch (ORMException $e) {
				throw new \Maps\QueryException($e, $this->query, $e->getMessage());
			}
		}

		return $this->totalCount;
	}



	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		try {
            return $this->getPaginatedQuery()->getIterator();

		} catch (ORMException $e) {
			throw new \Maps\QueryException($e, $this->query, $e->getMessage());
		}
	}



	/**
	 * @return int
	 */
	public function count()
	{
		return $this->getTotalCount();
	}



	/**
	 * @return \Doctrine\ORM\Tools\Pagination\Paginator
	 */
	private function getPaginatedQuery()
	{
		if ($this->paginatedQuery === NULL) {
			$this->paginatedQuery = new ResultPaginator($this->query);
		}

		return $this->paginatedQuery;
	}

}