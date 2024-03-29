<?php

namespace Maps\Model\Metadata;


use Maps\Model\BaseEntity;
use Maps\Model\User\User;

/**
 * Changset entity
 *
 * @author Jan Langer <langeja1@fit.cvut.cz>
 * @package Maps\Model\Metadata
 * @Entity
 * @table("metadata_changesets")
 */
class Changeset extends BaseEntity {
    const STATE_NEW = 'new';
    const STATE_APPROVED = 'approved';
    const STATE_REJECTED = 'rejected';
    const STATE_WITHDRAWN = 'withdrawn';

    /**
     * @var User
     * @ManyToOne(targetEntity="Maps\Model\User\User")
     * @JoinColumn(name="submitted_by", referencedColumnName="id", nullable=false)
     */
    private $submitted_by;
    /**
     * @var Revision
     * @ManyToOne(targetEntity="Revision")
     * @JoinColumn(name="against_revision", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $against_revision;
    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    private $submitted_date;

    /**
     * @var string
     * @Column(type="string", length=20)
     */
    private $state;

    /**
     * @var User
     * @ManyToOne(targetEntity="Maps\Model\User\User")
     */
    private $processed_by;
    /**
     * @var \DateTime
     * @Column(type="datetime", nullable=true)
     */
    private $processed_date;
    /**
     * @var Revision
     * @ManyToOne(targetEntity="Revision")
     */
    private $in_revision;
    /**
     * @var string
     * @Column(type="string", length=80, nullable=true)
     */
    private $comment;

    /**
     * @var string
     * @column(type="string", length=80, nullable=true)
     */
    private $admin_comment;


    /**
     * @OneToMany(targetEntity="NodeChange", mappedBy="changeset")
     * @var Node[]
     */
    private $nodes;

    /**
     * @OneToMany(targetEntity="PathChange", mappedBy="changeset")
     * @var Path[]
     */
    private $paths;

    public function __construct() {
        $this->submitted_date = new \DateTime();
    }


    /**
     * @param \Maps\Model\Metadata\Revision $against_revision
     */
    public function setAgainstRevision($against_revision)
    {
        $this->against_revision = $against_revision;
    }

    /**
     * @return \Maps\Model\Metadata\Revision
     */
    public function getAgainstRevision()
    {
        return $this->against_revision;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param \Maps\Model\Metadata\Revision $in_revision
     */
    public function setInRevision($in_revision)
    {
        $this->in_revision = $in_revision;
    }

    /**
     * @return \Maps\Model\Metadata\Revision
     */
    public function getInRevision()
    {
        return $this->in_revision;
    }

    /**
     * @param \Maps\Model\User\User $processed_by
     */
    public function setProcessedBy($processed_by)
    {
        $this->processed_by = $processed_by;
    }

    /**
     * @return \Maps\Model\User\User
     */
    public function getProcessedBy()
    {
        return $this->processed_by;
    }

    /**
     * @param \DateTime $processed_date
     */
    public function setProcessedDate($processed_date)
    {
        $this->processed_date = $processed_date;
    }

    /**
     * @return \DateTime
     */
    public function getProcessedDate()
    {
        return $this->processed_date;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param \Maps\Model\User\User $submitted_by
     */
    public function setSubmittedBy($submitted_by)
    {
        $this->submitted_by = $submitted_by;
    }

    /**
     * @return \Maps\Model\User\User
     */
    public function getSubmittedBy()
    {
        return $this->submitted_by;
    }

    /**
     * @param \DateTime $submitted_date
     */
    public function setSubmittedDate($submitted_date)
    {
        $this->submitted_date = $submitted_date;
    }

    /**
     * @return \DateTime
     */
    public function getSubmittedDate()
    {
        return $this->submitted_date;
    }


    /**
     * @return Node[]
     */
    public function getNodes() {
        return $this->nodes;
    }

    /**
     * @return Path[]
     */
    public function getPaths() {
        return $this->paths;
    }

    /**
     * @param string $admin_comment
     */
    public function setAdminComment($admin_comment) {
        $this->admin_comment = $admin_comment;
    }

    /**
     * @return string
     */
    public function getAdminComment() {
        return $this->admin_comment;
    }





}