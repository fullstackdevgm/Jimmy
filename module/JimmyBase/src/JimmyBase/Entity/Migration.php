<?php

namespace JimmyBase\Entity;


class Migration implements MigrationInterface
{
    /**
     * @var int
     */
    protected $id;
	
    /**
     * @var int
     */
    protected $userId;

    
    /**
     * @var date
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
     * @return MigrationInterface
     */
    public function setId($id)
    {
        $this->id = (int) $id;
    }
    
     /**
     * Get id.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }
    
    /**
     * Set id.
     *
     * @param int $id
     * @return MigrationInterface
     */
    public function setUserId($userId)
    {
        $this->userId = (int) $userId;
    }
    
    /**
     * Get created.
     *
     * @return date
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set created.
     *
     * @param date $created
     * @return MigrationInterface
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    
}
