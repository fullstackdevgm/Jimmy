<?php

namespace JimmyBase\Entity;

class UserPayments implements UserPaymentsInterface
{
    /**
     * @var int
     */
    protected $id;
   
    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var string
     */
    protected $status;
   
    /**
     * @var string
     */
    protected $currency;
    
	/**
     * @var string
     */
    protected $date;
	
	/**
     * @var string
     */
    protected $processor;
	
	/**
     * @var string
     */
    protected $trans_id;
	
	/**
     * @var array
     */
    protected $comments;

  
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
   
   
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set user_id.
     *
     * @param int $user_id
     * @return WidgetInterface
     */
    public function setUserId($user_id)
    {
        $this->user_id = (int) $user_id;
        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set amount.
     *
     * @param string $amount
     * @return WidgetInterface
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }
   
    
    /**
     * Get type.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set type.
     *
     * @param string $type
     * @return WidgetInterface
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }
    
    /**
     * Get sub_type.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set type.
     *
     * @param string $type
     * @return WidgetInterface
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }   
	
    /**
     * Get sub_type.
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }


 	/**
     * Set type.
     *
     * @param string $type
     * @return WidgetInterface
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }   
	
    /**
     * Set type.	
     *
     * @param string $type
     * @return WidgetInterface
     */
    public function setProcessor($processor)
    {
        $this->processor = $processor;
        return $this;
    }    /**
     * Get sub_type.
     *
     * @return string
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * Set type.
     *
     * @param string $type
     * @return WidgetInterface
     */
    public function setTransId($trans_id)
    {
        $this->trans_id = $trans_id;
        return $this;
    }    /**
     * Get sub_type.
     *
     * @return string
     */
    public function getTransId()
    {
        return $this->trans_id;
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

}
