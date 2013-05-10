<?php

namespace Maps\Model\Metadata;


use Doctrine\Common\Collections\ArrayCollection;
use Maps\Model\BaseEntity;
use Maps\Model\Floor\Floor;
use Maps\Model\User\User;

/**
 * Class Revision
 * @package Maps\Model\Metadata
 * @author Jan Langer <langeja1@fit.cvut.cz>
 *
 * @Entity
 * @table(name="metadata_revision",
 * uniqueConstraints={
 *      @UniqueConstraint(columns={"floor_id", "revision"})
 * })
 *
 * @property ArrayCollection $nodes
 * @property ArrayCollection $paths
 */
class Revision extends BaseEntity {

    /**
     * @ManyToOne(targetEntity="Maps\Model\Floor\Floor")
     * @JoinColumn(name="floor_id",referencedColumnName="id",onDelete="CASCADE")
     * @var Floor
     */
    private $floor;
    /**
     * @ManyToOne(targetEntity="Maps\Model\User\User")
     * @JoinColumn(name="user_id",referencedColumnName="id",onDelete="CASCADE")
     * @var User
     */
    private $user;
    /**
     * @Column(type="integer")
     * @var int
     */
    private $revision = 1;

    /**
     * @var bool
     * @Column(type="boolean")
     */
    private $published = FALSE;
    /**
     * @var \DateTime
     * @Column(type="datetime", nullable=true)
     */
    private $published_date;

    /**
     * @OneToMany(targetEntity="Node", mappedBy="revision")
     * @var Node[]
     */
    private $nodes;
    /**
     * @OneToMany(targetEntity="Path", mappedBy="revision")
     * @var Path[]
     */
    private $paths;

    function __construct()
    {
        $this->nodes = new ArrayCollection();
        $this->paths = new ArrayCollection();
    }

    /**
     * @param \Maps\Model\Floor\Floor $floor
     */
    public function setFloor($floor) {
        $this->floor = $floor;
    }

    /**
     * @return \Maps\Model\Floor\Floor
     */
    public function getFloor() {
        return $this->floor;
    }

    /**
     * @param Node[] $nodes
     */
    public function setNodes($nodes) {
        $this->nodes = $nodes;
    }

    /**
     * @return Node[]
     */
    public function getNodes() {
        return $this->nodes;
    }

    /**
     * @param Path[] $paths
     */
    public function setPaths($paths) {
        $this->paths = $paths;
    }

    /**
     * @return Path[]
     */
    public function getPaths() {
        return $this->paths;
    }

    /**
     * @param boolean $published
     */
    public function setPublished($published) {
        $this->published = $published;
    }

    /**
     * @return boolean
     */
    public function getPublished() {
        return $this->published;
    }

    /**
     * @param \DateTime $published_date
     */
    public function setPublishedDate($published_date) {
        $this->published_date = $published_date;
    }

    /**
     * @return \DateTime
     */
    public function getPublishedDate() {
        return $this->published_date;
    }

    /**
     * @param int $revision
     */
    public function setRevision($revision) {
        $this->revision = $revision;
    }

    /**
     * @return int
     */
    public function getRevision() {
        return $this->revision;
    }

    /**
     * @param \Maps\Model\User\User $user
     */
    public function setUser($user) {
        $this->user = $user;
    }

    /**
     * @return \Maps\Model\User\User
     */
    public function getUser() {
        return $this->user;
    }





}