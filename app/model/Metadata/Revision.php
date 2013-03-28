<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 13.3.13
 * Time: 19:12
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata;


use Doctrine\Common\Collections\ArrayCollection;
use Maps\Model\BaseEntity;

/**
 * Class Revision
 * @package Maps\Model\Metadata
 * @Entity
 * @table(name="metadata_revision")
 *
 * @property ArrayCollection $nodes
 * @property ArrayCollection $paths
 */
class Revision extends BaseEntity {

    /**
     * @ManyToOne(targetEntity="Maps\Model\Floor\Floor")
     * @JoinColumn(name="floor_id",referencedColumnName="id",onDelete="CASCADE")
     */
    private $floor;
    /**
     * @ManyToOne(targetEntity="Maps\Model\User\User")
     * @JoinColumn(name="user_id",referencedColumnName="id",onDelete="CASCADE")
     */
    private $user;
    /**
     * @Column(type="integer")
     */
    private $revision = 1;

    /** @Column(type="boolean") */
    private $published = false;
    /** @Column(type="datetime", nullable=true) */
    private $published_date;

    /**
     * @OneToMany(targetEntity="Node", mappedBy="revision")
     */
    private $nodes;
    /**
     * @OneToMany(targetEntity="Path", mappedBy="revision")
     */
    private $paths;

    function __construct()
    {
        $this->nodes = new ArrayCollection();
        $this->paths = new ArrayCollection();
    }


    public function setFloor($floor)
    {
        $this->floor = $floor;
    }

    public function getFloor()
    {
        return $this->floor;
    }

    public function setNodes($nodes)
    {
        $this->nodes = $nodes;
    }

    public function getNodes()
    {
        return $this->nodes;
    }

    public function setPaths($paths)
    {
        $this->paths = $paths;
    }

    public function getPaths()
    {
        return $this->paths;
    }

    public function setPublished($published)
    {
        $this->published = $published;
    }

    public function getPublished()
    {
        return $this->published;
    }

    public function setPublishedDate($published_date)
    {
        $this->published_date = $published_date;
    }

    public function getPublishedDate()
    {
        return $this->published_date;
    }

    public function setRevision($revision)
    {
        $this->revision = $revision;
    }

    public function getRevision()
    {
        return $this->revision;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }



}