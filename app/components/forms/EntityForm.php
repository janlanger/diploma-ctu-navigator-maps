<?php

namespace Maps\Components\Forms;

use Maps\Model\BaseEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;

/**
 * Description of EntityForm
 *
 * @author Jan -Quinix- Langer
 */
class EntityForm extends Form {

    private $entity;
    private $entityService;
    private $successFlashMessage = 'Data byla úspěšně uložena.';
    private $redirect;
    
    public $onBind = array();
    public $onHandle = array();
    public $onComplete = array();

    public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);
        $this->onSuccess[] = \callback($this, 'handler');
    }

    public function bindEntity($entity) {
        $this->entity = $entity;

        foreach ($this->getComponents() as $name => $input) {
            $value = NULL;
            if (method_exists($entity, "get$name")) {
                $method = "get$name";
            } elseif (method_exists($entity, "is$name")) {
                $method = "is$name";
            } elseif(count(explode("__",$name))>1) {
                $parts = explode("__", $name);
                if(method_exists($entity,"get".$parts[0])) {
                    $method = NULL;
                    $call = "get".$parts[0];
                    $object = $entity->$call();
                    if(method_exists($object, "get".$parts[1])) {
                        $method = "get".$parts[1];
                    }
                    elseif(method_exists($object, "is".$parts[1])) {
                        $method = "is".$parts[1];
                    }
                    
                    if($method != NULL) {
                        $value = $object->$method();
                    }
                    else {
                        continue;
                    }
                }
                        
            } else {
                continue;
            }
            if($value === NULL)
                $value = $entity->$method();

            if ($value instanceof BaseEntity) {
                $value = $value->getId();
            } elseif ($value instanceof ArrayCollection || $value instanceof PersistentCollection) {
                $value = array_map(function (BaseEntity $entity) {
                                    return $entity->getId();
                                }, $value->toArray());
            }

            $input->setDefaultValue($value);
        }
        if($this->onBind) {
            $this->onBind($entity);
        }
    }

    public function getEntity() {
        return $this->entity;
    }

    /**
     *
     * @return \SeriesCMS\Model\BaseService
     */
    public function getEntityService() {
        return $this->entityService;
    }

    public function setEntityService($entityService) {
        $this->entityService = $entityService;
    }

    public function handler($form) {
        if(!$form->isValid()) {
            return;
        }
        try {
            $values = $form->getValues();
            $this->onHandle($this->getEntity(), $values);
            $this->getEntityService()->update($this->getEntity(), $values);

            $presenter = $this->getPresenter();
            if ($this->successFlashMessage) {
                $presenter->flashMessage($this->successFlashMessage, \Maps\Presenter\BasePresenter::FLASH_SUCCESS);
            }
            $this->onComplete($this->entity);
            if ($this->redirect) {
                call_user_func_array(array($presenter, "redirect"), $this->redirect);
            }
        } catch (\InvalidArgumentException $e) {
            $this->addError($e->getMessage());
        }
    }

    public function setSuccessFlashMessage($successFlashMessage) {
        $this->successFlashMessage = $successFlashMessage;
    }

    public function setRedirect() {
        $this->redirect = func_get_args();
    }

}
