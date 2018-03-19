<?php

namespace JimmyBase\Entity;

class Tour implements TourInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $tourName;

    /**
     * @var datetime
     */
    protected $created ;
    
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
     * @return TourInterface
     */
    public function setId($id)
    {
        $this->id = intVal($id);
        return $this;
    }

    /**
     * Get tourName
     *
     * @return string
     */
    public function getTourName()
    {
        return $this->username;
    }

    /**
     * Set tourName.
     *
     * @param string $tourName
     * @return TourInterface
     */
    public function setTourName($tourName)
    {
        $this->tourName = $tourName;
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
    {
        $created =  date('Y/m/d H:i:s') ;
        $this->created = $created;
        return $this;
    }

}
