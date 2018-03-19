<?php

namespace JimmyBase\Entity;


class BraintreePayment implements BraintreePaymentInterface
{
    /**
     * @var int
     */
    protected $id;
	
    /**
     * @var int
     */
    protected $useId;

    /**
     * @var string
     */
    protected $customerId;
    
    /**
     * @var string
     */
    protected $subscriptionId;
    
    /**
     * @var string
     */
    protected $status;

    /**
     * @var date
     */
    protected $created;
    
   
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
     * 
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }
    
    /**
     * 
     * @param int $userId
     * @return \JimmyBase\Entity\BraintreePayment
     */
    public function setUserId($userId)
    {
        $this->userId = (int) $userId;
        return $this;
    }
    
    
    /**
     * 
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }
    
    
    /**
     * 
     * @param string $customerId
     * @return \JimmyBase\Entity\BraintreePayment
     */
    public function setCustomerId($customerId)
    {
        $this->customerId =  $customerId;
        return $this;
    }
    
    
     /**
     * 
     * @return string
     */
    public function getSubscriptionId()
    {
        return $this->subscriptionId;
    }
    
    
    /**
     * 
     * @param string $subscriptionId
     * @return \JimmyBase\Entity\BraintreePayment
     */
    public function setSubscriptionId($subscriptionId)
    {
        $this->subscriptionId =  $subscriptionId;
        return $this;
    }
    
    
    
     /**
     * 
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    
    /**
     * 
     * @param string $status
     * @return \JimmyBase\Entity\BraintreePayment
     */
    public function setStatus($status)
    {
        $this->status =  $status;
        return $this;
    }
    
    
    
   
    

    /**
     * Get created.
     *
     * @return date
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set created.
     *
     * @param date $created
     * @return ClientInterface
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }


   
    
}
