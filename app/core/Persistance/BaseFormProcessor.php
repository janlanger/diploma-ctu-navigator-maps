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
class BaseFormProcessor extends \Nette\Object {

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
    
    protected function handleUpload(\Nette\Http\FileUpload $file, $dir, $filename) {
        if($file->isOk()) {
            $ext = pathinfo($file->getName(), PATHINFO_EXTENSION);
            $i=0;
            while(file_exists($dir.'/'.$filename.'-'.$i.'.'.$ext)) {
                $i++;
            }
            $path = $dir.'/'.$filename.'-'.$i.'.'.$ext;
            if($file->move($path)) {
                chmod($path, 0666);
                return basename($path);
            }
        } elseif($file->getError() != UPLOAD_ERR_NO_FILE) {
            throw new \Nette\InvalidStateException("Unexpected error.");
        }
        return null;
    }



}

?>
