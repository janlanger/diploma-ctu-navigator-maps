<?php

namespace Maps\Model;


/**
 * Base Entity parent class
 * @MappedSuperClass
 * @author Jan Langer <langeja1@fit.cvut.cz>
 * @property-read int $id
 */
abstract class BaseEntity extends \Nette\Object {

    /**
     * @Id @Column(type="integer")
     * @generatedValue(strategy="IDENTITY")
     * @var int
     */
    protected $id;
    
    public function __construct() {
    }

    /**
     * @return int
     */
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
