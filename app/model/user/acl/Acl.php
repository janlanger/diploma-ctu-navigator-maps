<?php
namespace Maps\Model\Acl;
use Maps\Model\BaseEntity;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * Description of Acl
 * @Entity
 * @Table(name="acl")
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class Acl extends BaseEntity {
    /**
     * @manyToOne(targetEntity="Role")
     * @JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE", nullable=FALSE)
     * @var Role
     */
    private $role;
    /**
     * @manyToOne(targetEntity="Resource", cascade={"persist"})
     * @JoinColumn(name="resource_id", referencedColumnName="id", onDelete="RESTRICT", nullable=FALSE)
     * @var Resource
     */
    private $resource;
    /**
     * @manyToOne(targetEntity="Privilege", cascade={"persist"})
     * @JoinColumn(name="privilege_id", referencedColumnName="id", onDelete="RESTRICT", nullable=TRUE)
     * @var Privilege
     */
    private $privilege;
    /**
     * @column(type="boolean")
     * @var bool
     */
    private $allowed = FALSE;

    /**
     * @param bool $allowed
     */
    public function setAllowed($allowed) {
        $this->allowed = $allowed;
    }

    /**
     * @return bool
     */
    public function getAllowed() {
        return $this->allowed;
    }

    /**
     * @param Privilege $privilege
     */
    public function setPrivilege($privilege) {
        $this->privilege = $privilege;
    }

    /**
     * @return Privilege
     */
    public function getPrivilege() {
        return $this->privilege;
    }

    /**
     * @param Resource $resource
     */
    public function setResource($resource) {
        $this->resource = $resource;
    }

    /**
     * @return Resource
     */
    public function getResource() {
        return $this->resource;
    }

    /**
     * @param Role $role
     */
    public function setRole($role) {
        $this->role = $role;
    }

    /**
     * @return Role
     */
    public function getRole() {
        return $this->role;
    }


    

}
