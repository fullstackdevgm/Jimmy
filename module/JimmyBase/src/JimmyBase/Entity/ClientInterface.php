<?php

namespace JimmyBase\Entity;

interface ClientInterface
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
     * @return ClientInterface
     */
    public function setId($id);
	
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
     * @return ClientInterface
     */
    public function setName($name);

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail();
    
    /**
     * Get accounts.
     *
     * @return string
     */
    public function getAccounts();

    /**
     * Set email.
     *
     * @param string $email
     * @return ClientInterface
     */
    public function setEmail($email);


    public function getAdwordsClientId();


    public function setAdwordsClientId($adwords_client_id);
    
	/**
     * Get parent.
     *
     * @return int
     */
    public function getParent();

    /**
     * Set parent.
     *
     * @param int $parent
     * @return ClientInterface
     */
    public function setParent($parent);

	
	/**
     * Get logo.
     *
     * @return int
     */
    public function getLogo();

    /**
     * Set type.
     *
     * @param int $logo
     * @return ClientInterface
     */
    public function setLogo($logo);

    /**
     * Get created.
     *
     * @return date
     */
    public function getCreated();

    /**
     * Set created.
     *
     * @param date $created
     * @return ClientInterface
     */
    public function setCreated($created);


    /**
     * Get updated.
     *
     * @return date
     */
    public function getUpdated();

    /**
     * Set updated.
     *
     * @param date $updated
     * @return ClientInterface
     */
    public function setUpdated($updated);
   

}
