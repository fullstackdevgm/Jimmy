<?php

namespace JimmyBase\Entity;

interface UserInterface
{
    /**
     * Get id.
     *
     * @return int
     */
    public function getId();

    /**
     * Set id.
     *
     * @param int $id
     * @return UserInterface
     */
    public function setId($id);

    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername();

    /**
     * Set username.
     *
     * @param string $username
     * @return UserInterface
     */
    public function setUsername($username);

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email.
     *
     * @param string $email
     * @return UserInterface
     */
    public function setEmail($email);

    /**
     * Get displayName.
     *
     * @return string
     */
    public function getDisplayName();

    /**
     * Set displayName.
     *
     * @param string $displayName
     * @return UserInterface
     */
    public function setDisplayName($displayName);
	
	
	/**
     * Get name.
     *
     * @return string
     */
    public function getName();

    /**
     * Set name.
     *
     * @param string $name
     * @return UserInterface
     */
    public function setName($name);

    /**
     * Get password.
     *
     * @return string password
     */
    public function getPassword();

    /**
     * Set password.
     *
     * @param string $password
     * @return UserInterface
     */
    public function setPassword($password);

    /**
     * Get state.
     *
     * @return int
     */
    public function getState();

    /**
     * Set state.
     *
     * @param int $state
     * @return UserInterface
     */
    public function setState($state);
	
	/**
     * Get type.
     *
     * @return string
     */
    public function getType();

    /**
     * Set type.
     *
     * @param string $type
     * @return UserInterface
     */
    public function setType($type);
	
	/**
     * Get created.
     *
     * @return datetime
     */
    public function getCreated();

    /**
     * Set created.
     *
     * @param datetime $created
     * @return UserInterface
     */
    public function setCreated($created);
	
	/**
     * Get updated.
     *
     * @return datetime
     */
    public function getUpdated();

    /**
     * Set updated.
     *
     * @param datetime $updated
     * @return UserInterface
     */
    public function setUpdated($updated);
	
	/**
     * Get type.
     *
     * @return string
     */
    public function getValue();

    /**
     * Set type.
     *
     * @param string $type
     * @return UserInterface
     */
    public function setValue($value);

}
