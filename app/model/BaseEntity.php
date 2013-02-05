<?php

namespace Maps\Model;


/**
 * Description of BaseEntity
 * @MappedSuperClass
 * @author Honza
 * @property-read int $id
 */
abstract class BaseEntity extends \Nette\Object {

    /**
     * @Id @Column(type="integer")
     * @generatedValue(strategy="IDENTITY")
     */
    protected $id;
    
    public function __construct() {
    }

    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCacheKeys() {
        if($this->id != NULL) {
            return array(get_class($this) . "#" . $this->id);
        }
        return array();
    }

}
