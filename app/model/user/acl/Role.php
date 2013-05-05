<?php
namespace Maps\Model\Acl;
use Maps\Model\BaseEntity;
/**
 * Description of Role
 * @Entity
 * @Table(name="acl_roles",
 *      uniqueConstraints={@UniqueConstraint(name="name_uq",columns={"name"})}
 * )
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class Role extends BaseEntity {

    /**
     * @JoinColumn(name="parent_id", referencedColumnName="id")
     * @ManyToOne(targetEntity="Role", cascade={"persist"},  fetch="EAGER")
     * @var Role
     */
    private $parent;
    /** @Column(type="string", length=50) */
    private $name;

    /**
     * @oneToMany(targetEntity="Acl", mappedBy="role", cascade={"persist"})
     */
    private $acl;


    public function __construct() {
    }

    public function getParent() {
        return $this->parent;
    }

    public function getName() {
        return $this->name;
    }
    
    public function setParent($parent) {
        $this->parent = $parent;
    }

    public function setName($name) {
        $this->name = $name;
    }
    
    public function getAcl() {
        return $this->acl;
    }







}
