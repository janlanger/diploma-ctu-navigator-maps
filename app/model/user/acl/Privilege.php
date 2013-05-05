<?php
namespace Maps\Model\Acl;
use Maps\Model\BaseEntity;
/**
 * Description of Privilege
 * @Entity
 * @Table(name="acl_privileges",
 *      uniqueConstraints={@UniqueConstraint(name="name_uq",columns={"name"})}
 * )
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class Privilege extends BaseEntity {
    /**
     * @Column(type="string", length=50)
     * @var string
     */
    private $name;
    
    /**
     * @oneToMany(targetEntity="Acl", mappedBy="privilege", cascade={"persist"})
     * @var Acl[]
     */
    private $acl;
    
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }


}
