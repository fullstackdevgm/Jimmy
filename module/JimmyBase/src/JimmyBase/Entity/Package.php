<?php

namespace JimmyBase\Entity;


class Package implements PackageInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var enum
     */
    protected $type;

	/**
     * @var int
     */
    protected $templates_allowed;
  
	/**
     * @var decimal
     */
    protected $price;
  
    /**
     * @var string
     */
    protected $description;

    /**
     * @var enum
     */
    protected $status;
   
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
     * @return PackageInterface
     */
    public function setId($id)
    {
        $this->id = (int) $id;
        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param string $title
     * @return PackageInterface
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
   
     /**
     * Get is_free_trial.
     *
     * @return enum '0'=Paid,'1'=Free
     */
    public function getIsFreeTrial()
    {
        return $this->is_free_trial;
    }

    /**
     * Set is_free_trial.
     *
     * @param enum $is_free_trial
     * @return PackageInterface
     */
    public function setIsFreeTrial($is_free_trial)
    {
        $this->is_free_trial = $is_free_trial;
        return $this;
    }
   
    
	 /**
     * Get type.
     *
     * @return enum 'paid','free'
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type.
     *
     * @param enum $type
     * @return PackageInterface
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
	
	/**
     * Get is_free_trial.
     *
     * @return enum '0'=Paid,'1'=Free
     */
    /*public function getShowInFrontend()
    {
        return $this->show_in_frontend;
    }*/

    /**
     * Set show_in_frontend.
     *
     * @param enum $show_in_frontend
     * @return PackageInterface
     */
    /*public function setShowInFrontend($show_in_frontend)
    {
        $this->show_in_frontend = $show_in_frontend;
        return $this;
    }*/
   
    /**
     * Get templates_allowed.
     *
     * @return string
     */
    public function getTemplatesAllowed()
    {
        return $this->templates_allowed;
    }

    /**
     * Set templates_allowed.
     *
     * @param string $templates_allowed
     * @return PackageInterface
     */
    public function setTemplatesAllowed($templates_allowed)
    {
        $this->templates_allowed = $templates_allowed;
        return $this;
    }

    /**
     * Get price.
     *
     * @return decimal
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set price.
     *
     * @param decimal $price
     * @return PackageInterface
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description.
     *
     * @param string $description
     * @return PackageInterface
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

	/**
     * Get status.
     *
     * @return enum 0=disabled,1=enabled
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status.
     *
     * @param string $status
     * @return PackageInterface
     */
    public function setStatus($status)
    {
        $this->status = $status;
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
     * @param int $created
     * @return PackageInterface
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

	/**
     * Get updated.
     *
     * @return updated
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set updated.
     *
     * @param string $updated
     * @return PackageInterface
     */
    public function setUpdated($updated)
    {   
        $this->updated = $updated;
        return $this;
    }

}
