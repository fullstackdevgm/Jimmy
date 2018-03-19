<?php

namespace JimmyBase\Entity;

class User implements UserInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $displayName;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var int
     */
    protected $state;

    /**
     * @var int
     */
    protected $type;


    /**
     * @var datetime
     */
    protected $created;


    /**
     * @var datetime
     */
    protected $updated;


    const ACTIVE   = 1;

    const INACTIVE = 0;
    
    const CANCELLED = 2;

    const USER     = 'user';

    const AGENCY   = 'agency';

    const COWORKER = 'coworker';
   



	/***** User Meta *****/
    /**
     * @var int
     */
    protected $key;

    /**
     * @var int
     */
    protected $value;


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
     * @return UserInterface
     */
    public function setEmail($email)
    {
        $this->email = $email;
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
     * @return UserInterface
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * @return UserInterface
     */
    public function setPassword($password)
    {
        $this->password = $password;
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
     * @return UserInterface
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
     * @return UserInterface
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
        return $this;
    }


	/**
     * Get key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }


    /**
     * Set key.
     *
     * @param string $key
     * @return UserInterface
     */
    public function setKey($key)
    {

        $this->key = $key;
        return $this;
    }


	/**
     * Get data.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * Set type.
     *
     * @param string $type
     * @return UserInterface
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

}
