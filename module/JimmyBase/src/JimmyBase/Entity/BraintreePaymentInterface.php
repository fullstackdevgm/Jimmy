<?php

namespace JimmyBase\Entity;


interface BraintreePaymentInterface
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
     * @return BraintreePaymentInterface
     */
    public function setId($id);
    
    /**
     * Get parentId.
     *
     * @return int
     */    
    public function getUserId();
   
    /**
     * Set UsertId.
     *
     * @param int $userId
     * @return BraintreePaymentInterface
     */
    public function setUserId($userId);
    
    /**
     * 
     * @return string
     */
    public function getCustomerId();
    
    
    /**
     * 
     * @param string $customerId
     * @return \JimmyBase\Entity\BraintreePayment
     */
    public function setCustomerId($customerId);
    
     /**
     * 
     * @return string
     */
    public function getSubscriptionId();
    
    /**
     * 
     * @param string $subscriptionId
     * @return \JimmyBase\Entity\BraintreePayment
     */
    public function setSubscriptionId($subscriptionId);
    
     /**
     * 
     * @return string
     */
    public function getStatus();
   
    
    /**
     * 
     * @param string $status
     * @return \JimmyBase\Entity\BraintreePayment
     */
    public function setStatus($status);
   

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
   
}
