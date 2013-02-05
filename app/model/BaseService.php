<?php
namespace Maps\Model;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseService
 *
 * @author Honza
 */
abstract class BaseService extends \Nette\Object {


	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;

	/** @var string */
	private $entityName;




	public function __construct(\Doctrine\ORM\EntityManager $entityManager, $entityName)
	{
		$this->entityManager = $entityManager;
		$this->entityName = $entityName;
	}



	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}

        public function getService($name) {
            return \Nette\Environment::getService($name);
        }



	/**
	 * @return string
	 */
	public function getEntityName()
	{
		return $this->entityName;
	}



	/**
	 * Find entity
	 * @param int id
	 * @return BaseEntity
	 */
	public function find($id)
	{
		return $this->entityManager->getRepository($this->entityName)->find($id);
	}



	/**
	 * Find all entities
	 * @return array
	 */
	public function findAll()
	{
		return $this->entityManager->getRepository($this->entityName)->findAll();
	}



	/**
	 * Create blank entity
	 */
	public function createBlank()
	{
		$class = $this->entityName;
		return new $class;
	}



	/**
	 * Create entity and flush
	 * @param array values
	 * @return BaseEntity
	 */
	public function create($values)
	{
		$entity = $this->createBlank();
		$this->update($entity, $values);
		return $entity;
	}



	/**
	 * Update entity and flush
	 * @param BaseEntity entity
	 * @param array values
	 */
	public function update($entity, $values)
	{
		$this->setData($entity, $values);
		$this->save($entity);
		return $entity;
	}



	/**
	 * Persist entity and flush
	 * @param BaseEntity $entity
	 * @return BaseEntity
	 */
	public function save($entity)
	{
		$this->entityManager->persist($entity);
		$this->entityManager->flush();
		return $entity;
	}



	/**
	 * Delete entity and flush
	 * @param BaseEntity entity
	 */
	public function delete($entity)
	{
		try{
			$this->entityManager->remove($entity);
			$this->entityManager->flush();
			return $entity;
		} catch(\PDOException $e) {
			throw new \ModelException($e->getMessage(), $e->getCode(), $e);
		}
	}



	/**
	 * @param BaseEntity $entity
	 * @param array $values
	 */
	protected function setData($entity, $values)
	{
		foreach ($values as $key => $value) {
                    if(!\Nette\Utils\Strings::startsWith(strtolower($key),"form_internal_")) {
			$method = "set" . ucfirst($key);
			$entity->$method($value);
                    }
		}
	}


}
