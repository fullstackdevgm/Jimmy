<?php

namespace JimmyBase\Entity;

class VisitedTour implements VisitedTourInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $tourId;
    
     /**
     * @var int
     */
    protected $userId;


    /**
     * @var datetime
     */
    protected $created;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param int $id
     * @return VisitedTourInterface
     */
    public function setId($id)
    {
        $this->id = (int) $id;
        return $this;
    }

    /**
     * Get tourId
     *
     * @return int
     */
    public function getTourId()
    {
        return $this->tourId;
    }

    /**
     * Set tourName.
     *
     * @param int $tourId
     * @return VisitedTourInterface
     */
    public function setTourId($tourId)
    {
        $this->tourId = $tourId;
        return $this;
    } 
    
    /**
     * Get UserId
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set tourName.
     *
     * @param int $userId
     * @return VisitedTourInterface
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    } 

	/**
     * Get created.
     *
     * @return datetime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set created.
     *
     * @param datetime $created
     * @return UserInterface
     */
    public function setCreated()
    {   $created =  date('Y/m/d H:i:s');
        $this->created = $created;
        return $this;
    }

}
