<?php

namespace JimmyBase\Entity;

class UserToken implements UserTokenInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $parentId;

    /**
     * @var name
     */
    protected $name;  
    

    /**
     * @var string
     */
    protected $channel;

    /**
     * @var string
     */
    protected $token;  
       

    /**
     * @var datetime
     */
    protected $created;


    /**
     * @var datetime
     */
    protected $updated;


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
     * @return UserInterface
     */
    public function setId($id)
    {
        $this->id = (int) $id;
        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set username.
     *
     * @param int $parentId
     * @return UserTokenInterface
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
        return $this;
    }    
    
    /**
     * Get name.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     * 
     * @param string $name
     * @return UserTokenInterface
     * 
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * Get channel.
     * 
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set Channel.
     * 
     * @param string $channel
     * @return UserTokenInterface
     * 
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
        return $this;
    }
    
    /**
     * Get Token
     * 
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
    
    /**
     * Set Channel
     * 
     * @param string $token
     * @return UserTokenInterfce
     */
    public function setToken($token)
    {
        $this->token = $token;
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
     * @return UserTokenInterface
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }


	/**
     * Get updated.
     *
     * @return datetime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set updated.
     *
     * @param datetime $updated
     * @return UserTokenInterface
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
        return $this;
    }



}
