<?php
namespace Maps\Model\Acl;
use Maps\Model\BaseEntity;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * Description of Acl
 * @Entity
 * @Table(name="acl")
 * @author Jan -Quinix- Langer
 */
class Acl extends BaseEntity {
    /**
     * @manyToOne(targetEntity="Role")
     * @JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE", nullable=FALSE)
     */
    private $role;
    /**
     * @manyToOne(targetEntity="Resource", cascade={"persist"})
     * @JoinColumn(name="resource_id", referencedColumnName="id", onDelete="RESTRICT", nullable=FALSE)
     */
    private $resource;
    /**
     * @manyToOne(targetEntity="Privilege", cascade={"persist"})
     * @JoinColumn(name="privilege_id", referencedColumnName="id", onDelete="RESTRICT", nullable=TRUE)
     */
    private $privilege;
    /** @column(type="boolean") */
    private $allowed = false;

    public function __construct() {
        $this->privilege=new ArrayCollection();
        $this->resource=new ArrayCollection();
    }
    
    public function getRole() {
        return $this->role;
    }

    public function getResource() {
        return $this->resource;
    }

    public function getPrivilege() {
        return $this->privilege;
    }

    public function isAllowed() {
        return $this->allowed;
    }
    
    public function setResource($resource) {
        $this->resource = $resource;
    }

    public function setPrivilege($privilege) {
        $this->privilege = $privilege;
    }

    public function setAllowed($allowed) {
        $this->allowed = $allowed;
    }

    public function setRole($role) {
        $this->role = $role;
    }





}
