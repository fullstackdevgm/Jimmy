<?php

namespace JimmyBase\Entity;

interface PackageInterface
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
     * @return PackageInterface
     */
    public function setId($id);

	/**
     * Get title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set title.
     *
     * @param string $title
     * @return PackageInterface
     */
    public function setTitle($title);

    /**
     * Get type. 'standard','custom'
     *
     * @return datetime
     */
    public function getType();

    /**
     * Set type.
     *
     * @param datetime $type
     * @return PackageInterface
     */
    public function setType($type);
	
    /**
     * Get is_free_trial. 0 =  Paid, 1 = Free
     *
     * @return datetime
     */
    public function getIsFreeTrial();

    /**
     * Set is_free_trial.
     *
     * @param datetime $is_free_trial
     * @return PackageInterface
     */
    public function setIsFreeTrial($is_free_trial);
 	
	/**
     * Get show_in_frontend. 0 =  Don't Show, 1 = Show
     *
     * @return int
     */
   // public function getShowInFrontend();

    /**
     * Set show_in_frontend.
     *
     * @param datetime $show_in_frontend
     * @return PackageInterface
     */
   // public function setShowInFrontend($show_in_frontend);

    /**
     * Get templates_allowed.
     *
     * @return string
     */
    public function getTemplatesAllowed();

    /**
     * Set templates_allowed.
     *
     * @param string $templates_allowed
     * @return PackageInterface
     */
    public function setTemplatesAllowed($templates_allowed);

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set description.
     *
     * @param string $description
     * @return PackageInterface
     */
    public function setDescription($description);

    /**
     * Get price.
     *
     * @return decimal
     */
    public function getPrice();

    /**
     * Set price.
     *
     * @param decimal $price
     * @return PackageInterface
     */
    public function setPrice($price);

    /**
     * Get status.
     *
     * @return enum status
     */
    public function getStatus();

    /**
     * Set status.
     *
     * @param enum $status
     * @return PackageInterface
     */
    public function setStatus($status);

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
     * @return PackageInterface
     */
    public function setCreated($state);
	/**
     * Get updated.
     *
     * @return datetime
     */
    public function getUpdated();

    /**
     * Set updated
     *
     * @param datetime $updated
     * @return PackageInterface
     */
    public function setUpdated($type);

}
