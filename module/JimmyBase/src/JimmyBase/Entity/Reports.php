<?php

namespace JimmyBase\Entity;

class Reports implements ReportsInterface
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
     * @var int
     */
    protected $user_id;

    /**
     * @var int
     */
    protected $parent;
    
	/**
     * @var int
     */
    protected $status;

    /**
     * @var int
     **/
    protected $paid;
	
	/**
     * @var string
     */
    protected $notes;
  
    /**
     * @var date
     */
    protected $created;
  	
	/**
     * @var date
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
     * @return ReportInterface
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
     * @return ReportInterface
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
   
     /**
     * Get user_id.
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set user_id.
     *
     * @param string $user_id
     * @return ReportInterface
     */
    public function setUserId($user_id)
    {
        $this->user_id =  $user_id;
        return $this;
    }

    /**
     * Get parent.
     *
     * @return int
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set parent.
     *
     * @param int $parent
     * @return ReportInterface
     */
    public function setParent($parent)
    {
        $this->parent =  $parent;
        return $this;
    }     
	
	 /**
     * Get report_type.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status.
     *
     * @param string $status
     * @return ReportInterface
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }


    /**
     * Get paid.
     *
     * @return boolean
     */
    public function getPaid()
    {
        return (boolean) $this->paid;
    }

    /**
     * Set paid.
     *
     * @param string $paid
     * @return ReportInterface
     */
    public function setPaid($paid)
    {
        $this->paid = (int) $paid;
        return $this;
    }     
	
	
    /**
     * Set notes.
     *
     * @param string $notes
     * @return ReportInterface
     */
    public function setNotes($notes)
    {
        $this->notes =  $notes;
        return $this;
    }
   
    
    /**
     * Get notes.
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set created.
     *
     * @param string $created
     * @return ReportInterface
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }
	
	 /**
     * Get created.
     *
     * @return string
     */
    public function getCreated()
    {
        return $this->created;
    }
	
 	
	/**
     * Get updated.
     *
     * @return string
     */
    public function getUpdated()
    {
        return $this->updated;
    }
    /**
     * Set updated.
     *
     * @param string $updated
     * @return ReportInterface
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
        return $this;
    }
    
}
