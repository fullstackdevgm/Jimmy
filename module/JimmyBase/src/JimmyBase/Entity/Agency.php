<?php

namespace JimmyBase\Entity;


class Agency implements AgencyInterface
{
    /**
     * @var int
     */
    protected $id;
	
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $email;
	
	/**
     * @var string
     */
    protected $password;
  
    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $displayName;
   
    /**
     * @var int
     */
    protected $state;
	
    /**
     * @var int
     */
    protected $type;

    /**
     * @var int
     */
    protected $created;
    
   /**
     * @var int
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
     * @return ClientInterface
     */
    public function setId($id)
    {
        $this->id = (int) $id;
        return $this;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set username.
     *
     * @param string $username
     * @return ClientInterface
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
   
     /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email.
     *
     * @param string $email
     * @return ClientInterface
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }
   
    
    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password.
     *
     * @param string $password
     * @return ClientInterface
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }
	
	
    /**
     * Get displayName.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Set displayName.
     *
     * @param string $displayName
     * @return UserInterface
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
        return $this;
    }
	
	/**
     * Get username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username.
     *
     * @param string $username
     * @return UserInterface
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }
    /**
     * Get state.
     *
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set state.
     *
     * @param int $state
     * @return ClientInterface
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }
	
	/**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type.
     *
     * @param string $type
     * @return UserInterface
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set type.
     *
     * @param string $type
     * @return UserInterface
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getUpdated()
    {
        return $this->created;
    }

    /**
     * Set type.
     *
     * @param string $type
     * @return UserInterface
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
        return $this;
    }


}
