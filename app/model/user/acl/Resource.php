<?php
namespace Maps\Model\Acl;
use Maps\Model\BaseEntity;
/**
 * Description of Resource
 * @Entity
 * @Table(name="acl_resources",
 *      uniqueConstraints={@UniqueConstraint(name="name_uq",columns={"name"})}
 * )
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class Resource extends BaseEntity {
    /** @Column(type="string", length=50) */
    private $name;
    
    /**
     * @oneToMany(targetEntity="Acl", mappedBy="resource", cascade={"persist"})
     */
    private $acl;
    
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }


}
