<?php
namespace Maps\Model\Acl;
use Maps\Model\BaseEntity;
/**
 * Description of Privilege
 * @Entity
 * @Table(name="acl_privileges",
 *      uniqueConstraints={@UniqueConstraint(name="name_uq",columns={"name"})}
 * )
 * @author Jan -Quinix- Langer
 */
class Privilege extends BaseEntity {
    /** @Column(type="string", length=50) */
    private $name;
    
    /**
     * @oneToMany(targetEntity="Acl", mappedBy="privilege", cascade={"persist"})
     */
    private $acl;
    
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }


}
