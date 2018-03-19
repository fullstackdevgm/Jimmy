<?php

namespace JimmyBase\Entity;

class Widget implements WidgetInterface
{
    /**
     * @var int
     */
    protected $id;
   
    /**
     * @var int
     */
    protected $report_id;

    /**
     * @var string
     */
    protected $title;
   
    /**
     * @var string
     */
    protected $type;
    
    /**
     * @var string
     */
    protected $client_account_id;
    
	/**
     * @var string
     */
    protected $sub_type;
	
	/**
     * @var array
     */
    protected $fields = array();

	/**
     * @var string
     */
    protected $comments;
  
    /**
     * @var date
     */
    protected $created;
  	
	/**
     * @var date
     */
    protected $updated;

    /**
     * @var int
     */
    protected $order;
    
    /**
     * @var int
     */
    protected $status;
    
    
    /**
     * @var int
    */
    protected $insight;

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
     * @return WidgetInterface
     */
    public function setId($id)
    {
        $this->id = (int) $id;
        return $this;
    }
   
   
    public function getReportId()
    {
        return $this->report_id;
    }

    /**
     * Set report_id.
     *
     * @param int $report_id
     * @return WidgetInterface
     */
    public function setReportId($report_id)
    {
        $this->report_id = (int) $report_id;
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
     * @return WidgetInterface
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
     * @return WidgetInterface
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
    
    /**
     * Get client_account_id.
     *
     * @return string
     */
    public function getClientAccountId()
    {
        return $this->client_account_id;
    }

    /**
     * Set client_account_id.
     *
     * @param string $client_account_id
     * @return WidgetInterface
     */
    public function setClientAccountId($client_account_id)
    {
        $this->client_account_id = $client_account_id;
        return $this;
    }
    
    /**
     * Get sub_type.
     *
     * @return string
     */
    public function getSubType()
    {
        return $this->sub_type;
    }

    /**
     * Set type.
     *
     * @param string $type
     * @return WidgetInterface
     */
    public function setSubType($sub_type)
    {
        $this->sub_type = $sub_type;
        return $this;
    }   
	
    /**
     * Get sub_type.
     *
     * @return string
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Set type.
     *
     * @param string $type
     * @return WidgetInterface
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
        return $this;
    }   
	
    /**
     * Set comments.
     *
     * @param string $comments
     * @return WidgetInterface
     */
    public function setComments($comments)
    {
        $this->comments =  $comments;
        return $this;
    }
   
    
    /**
     * Get comments.
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set created.
     *
     * @param string $created
     * @return WidgetInterface
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
     * @return WidgetInterface
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
        return $this;
    }
	
	
	 /**
     * Get sub_type.
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
     * @return WidgetInterface
     */
    public function setStatus($status)
    {
        $this->status = (int) $status;
        return $this;
    }     
	
    /**
     * Get order.
     *
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set order.
     *
     * @param int $order
     * @return WidgetInterface
     */
    public function setOrder($order)
    {
        $this->order = (int) $order;
        return $this;
    }
    
    
     /**
     * Get insight
     *
     * @return string
     */
    public function getInsight()
    {
        return $this->insight;
    }

    /**
     * Set status.
     *
     * @param string $insight
     * @return WidgetInterface
     */
    public function setInsight($insight)
    {
        $this->insight = $insight;
        return $this;
    }
   
}
