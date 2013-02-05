<?php

namespace Maps\Model\Persistence;



/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseFormProvider
 *
 * @author Honza
 */
class BaseFormProvider extends \Nette\Object {

    private $dao;

    public function __construct(\Maps\Model\Dao $dao) {
        $this->dao = $dao;
    }

    public function update($entity, $values) {
        $this->setData($entity, $values);
        $this->dao->save($entity);
        return $entity;
    }

    protected function setData($entity, $values) {
        foreach ($values as $key => $value) {
            $method = "set" . ucfirst($key);
            $entity->$method($value);
        }
    }
    
    /**
     * 
     * @param string $entityName
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getEntityRepository($entityName) {
        return $this->dao->getEntityManager()->getRepository($entityName);
    }
    
    /**
     * 
     * @return \Maps\Model\Dao
     */
    public function getDao() {
        return $this->dao;
    }



}

?>
