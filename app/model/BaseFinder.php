<?php

namespace Maps\Model;

use Nette\Utils\Paginator;

/**
 * Finder class for old model configuration
 *
 * @package Maps\Model
 * @deprecated
 */
abstract class BaseFinder extends \Nette\Object {

    /** @var \Doctrine\ORM\QueryBuilder */
    protected $qb;
    /** @var string */
    protected $alias = "e";



    public function __construct($service) {
        $this->qb = $service->getEntityManager()->getRepository($service->getEntityName())->createQueryBuilder($this->alias);
    }

    protected function reindexAssoc($result, $key) {
        $arr=array();
        foreach ($result as $row) {
            if (array_key_exists(is_object($row)?$row->$key:$row[$key], $arr)) {
               // throw new \InvalidStateException("Key value ".is_object($row)?$row->$key:$row[$key]." is duplicit in fetched associative array. Try to use different associative key");
            }
            $arr[is_object($row)?$row->$key:$row[$key]] = $row;
        }
        return $arr;
    }

    /**
     * @return BaseEntity
     */
    public function getSingleResult() {
        try {
            return $this->qb->getQuery()->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return NULL;
        }
    }

    /**
     * @return array
     */
    public function getResult() {
        return $this->qb->getQuery()->getResult();
    }

    public function getAssocResult($key) {
        return $this->reindexAssoc($this->getResult(), $key);
    }

    public function getCount() {
        $qb = clone $this->qb;
        return $qb->select("count($this->alias) fullcount")->getQuery()->getSingleScalarResult();
    }

    public function getArrayResult() {
        return $this->qb->getQuery()->getArrayResult();
    }

    /**
     * @param \Nette\Utils\Paginator $paginator
     * @return array
     */
    public function getPaginatedResult(Paginator $paginator) {
        return $this->qb->getQuery()
                ->setFirstResult($paginator->getOffset())
                ->setMaxResults($paginator->getItemsPerPage())
                ->getResult();
    }

    /**
     * @param $limit
     * @return array
     */
    public function getLimitedResult($limit) {
        return $this->qb->getQuery()->setMaxResults($limit)->getResult();
    }

    /**
     * @param $key
     * @param $value
     * @return array
     */
    public function fetchPairs($key, $value) {
        $qb = clone $this->qb;
        $qb->select('partial ' . $this->alias . '.{' . $key . ', ' . $value . '}');
        $res = $qb->getQuery()->getScalarResult();

        $arr = array();

        foreach ($res as $item) {
            $arr[$item[$this->alias . '_' . $key]] = $item[$this->alias . '_' . $value];
        }

        return $arr;
    }

    /**
     * @param int $id
     * @return BaseFinder
     */
    public function whereId($id) {
        $this->qb->andWhere("$this->alias.id = :id");
        $this->qb->setParameter("id", $id);
        return $this;
    }

}