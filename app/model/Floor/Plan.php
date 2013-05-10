<?php
namespace Maps\Model\Floor;


use Maps\Model\BaseEntity;
use Maps\Model\User\User;

/**
 * Floor plan revision
 *
 * @author Jan Langer <langeja1@fit.cvut.cz>
 * @package Maps\Model\Floor
 *
 * @Entity
 * @Table(name="floor_plans",
 * indexes={
 * @Index(columns={"published"})
 * },
 * uniqueConstraints={
 * @UniqueConstraint(columns={"floor_id", "revision"})
 *  })
 */
class Plan extends BaseEntity {
    /**
     * @var Floor
     * @ManyToOne(targetEntity="Maps\Model\Floor\Floor", inversedBy="nodes")
     * @JoinColumn(name="floor_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $floor;

    /**
     * @var int
     * @Column(type="integer")
     */
    private $revision = 1;
    /**
     * @var boolean
     * @Column(type="boolean")
     */
    private $published = FALSE;

    /**
     * @var boolean
     * @Column(type="boolean")
     */
    private $inPublishQueue = FALSE;

    /**
     * @ManyToOne(targetEntity="Maps\Model\User\User")
     * @var User
     */
    private $user;

    /**
     * @var string
     * @Column(type="string",length=200)
     */
    private $sourceFile;

    /**
     * @var int
     * @Column(type="integer", nullable=true)
     */
    private $sourceFilePage = 1;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    private $added_date;

    /**
     * @var \DateTime
     * @Column(type="datetime", nullable=true)
     */
    private $published_date;


    /**
     * @var string
     * @Column(type="string",length=100, nullable=true)
     */
    private $reference_topLeft;
    /**
     * @var string
     * @Column(type="string",length=100, nullable=true)
     */
    private $reference_topRight;
    /**
     * @var string
     * @Column(type="string",length=100, nullable=true)
     */
    private $reference_bottomRight;

    /**
     * @var string
     * @Column(type="string",length=100, nullable=true)
     */
    private $bounding_SW;
    /**
     * @var string
     * @Column(type="string",length=100, nullable=true)
     */
    private $bounding_NE;
    /**
     * @var int
     * @Column(type="integer", nullable=true)
     */
    private $maxZoom;
    /**
     * @var int
     * @Column(type="integer", nullable=true)
     */
    private $minZoom;

    public function __construct() {
        $this->added_date = new \DateTime();
    }

    /**
     * @param Floor $floor
     */
    public function setFloor($floor) {
        $this->floor = $floor;
    }

    /**
     * @return Floor
     */
    public function getFloor() {
        return $this->floor;
    }

    /**
     * @param string $plan
     */
    public function setPlan($plan) {
        $this->sourceFile = $plan;
    }

    /**
     * @return string
     */
    public function getPlan() {
        return $this->sourceFile;
    }

    /**
     * @param User $user
     */
    public function setUser($user) {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param \DateTime $added_date
     */
    public function setAddedDate($added_date) {
        $this->added_date = $added_date;
    }

    /**
     * @return \DateTime
     */
    public function getAddedDate() {
        return $this->added_date;
    }

    /**
     * @param string $bounding_NE
     */
    public function setBoundingNE($bounding_NE) {
        $this->bounding_NE = $bounding_NE;
    }

    /**
     * @return string
     */
    public function getBoundingNE() {
        return $this->bounding_NE;
    }

    /**
     * @param string $bounding_SW
     */
    public function setBoundingSW($bounding_SW) {
        $this->bounding_SW = $bounding_SW;
    }

    /**
     * @return string
     */
    public function getBoundingSW() {
        return $this->bounding_SW;
    }

    /**
     * @param boolean $inPublishQueue
     */
    public function setInPublishQueue($inPublishQueue) {
        $this->inPublishQueue = $inPublishQueue;
    }

    /**
     * @return boolean
     */
    public function getInPublishQueue() {
        return $this->inPublishQueue;
    }

    /**
     * @param int $maxZoom
     */
    public function setMaxZoom($maxZoom) {
        $this->maxZoom = $maxZoom;
    }

    /**
     * @return int
     */
    public function getMaxZoom() {
        return $this->maxZoom;
    }

    /**
     * @param int $minZoom
     */
    public function setMinZoom($minZoom) {
        $this->minZoom = $minZoom;
    }

    /**
     * @return int
     */
    public function getMinZoom() {
        return $this->minZoom;
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
     * @param string $reference_bottomRight
     */
    public function setReferenceBottomRight($reference_bottomRight) {
        $this->reference_bottomRight = $reference_bottomRight;
    }

    /**
     * @return string
     */
    public function getReferenceBottomRight() {
        return $this->reference_bottomRight;
    }

    /**
     * @param string $reference_topLeft
     */
    public function setReferenceTopLeft($reference_topLeft) {
        $this->reference_topLeft = $reference_topLeft;
    }

    /**
     * @return string
     */
    public function getReferenceTopLeft() {
        return $this->reference_topLeft;
    }

    /**
     * @param string $reference_topRight
     */
    public function setReferenceTopRight($reference_topRight) {
        $this->reference_topRight = $reference_topRight;
    }

    /**
     * @return string
     */
    public function getReferenceTopRight() {
        return $this->reference_topRight;
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
     * @param string $sourceFile
     */
    public function setSourceFile($sourceFile) {
        $this->sourceFile = $sourceFile;
    }

    /**
     * @return string
     */
    public function getSourceFile() {
        return $this->sourceFile;
    }

    /**
     * @param int $sourceFilePage
     */
    public function setSourceFilePage($sourceFilePage) {
        $this->sourceFilePage = $sourceFilePage;
    }

    /**
     * @return int
     */
    public function getSourceFilePage() {
        return $this->sourceFilePage;
    }

}