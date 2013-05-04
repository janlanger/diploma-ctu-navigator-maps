<?php

namespace Maps\Components\Forms;

use Maps\Model\BaseEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Maps\Model\Persistence\BaseFormProcessor;

/**
 * Binds form to defined entity and updates it after form is sended.
 *
 * @author Jan Langer <langeja1@fit.cvut.cz>
 * @package Maps\Components\Forms
 */
class EntityForm extends Form {

    /** @var BaseEntity binded entity */
    private $entity;
    /** @var BaseFormProcessor entity update service */
    private $entityService;
    /** @var string flash message to show after successful save */
    private $successFlashMessage = 'Data byla úspěšně uložena.';
    /** @var string redirect detination after successful save */
    private $redirect;

    /** @var array of function(BaseEntity $entity); occurs when entity is binded to form */
    public $onBind = array();

    /** @var array of function(BaseEntity $entity, array $values); occurs when form was submitted and its valid,
     * but before actual entity update */
    public $onHandle = array();

    /** @var array of function(BaseEntity $entity); occurs when entity was saved and form processing is completed */
    public $onComplete = array();

    /**
     * @inheritdoc
     */
    public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);
        $this->onSuccess[] = \callback($this, 'handler');
    }

    /**
     * Binds entity to form
     *
     * Loads entity values for every defined form field in to this fields. Entity has to have getters
     * for requested values, otherwise nothing will be loaded.
     *
     * @param BaseEntity $entity
     */
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

    /**
     * Return binded entity
     *
     * @return BaseEntity
     */
    public function getEntity() {
        return $this->entity;
    }

    /**
     * @return BaseFormProcessor
     */
    public function getEntityService() {
        return $this->entityService;
    }

    /**
     * @param BaseFormProcessor $entityService
     */
    public function setEntityService($entityService) {
        $this->entityService = $entityService;
    }

    /**
     * On success form event handler
     *
     * @param Form
     */
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

    /**
     * @param string $successFlashMessage message to be set as flash message after form is successfully processed
     */
    public function setSuccessFlashMessage($successFlashMessage) {
        $this->successFlashMessage = $successFlashMessage;
    }

    /**
     * @param string $destination redirect destination
     */
    public function setRedirect() {
        $this->redirect = func_get_args();
    }

}
