<?php

namespace JimmyBase\Entity;


interface UserTokenInterface
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
     * @return UserTokenInterface
     */
    public function setId($id);
    
    /**
     * Get parentId.
     *
     * @return int
     */
    
    public function getParentId();
   
    /**
     * Set ParentId.
     *
     * @param int $parentId
     * @return UserTokenInterface
     */
    public function setParentId($parentId);
  
     
    /**
     * Get name.
     * 
     * @return string
     */
    public function getName();
    

    /**
     * Set Channel.
     * 
     * @param string $channel
     * @return UserTokenInterface
     * 
     */
    public function setName($channel);
  
    
    /**
     * Get channel.
     * 
     * @return string
     */
    public function getChannel();
    

    /**
     * Set Channel.
     * 
     * @param string $channel
     * @return UserTokenInterface
     * 
     */
    public function setChannel($channel);
  
    /**
     * Get Token
     * 
     * @return string
     */
    public function getToken();
    
    /**
     * Set Channel
     * 
     * @param string $token
     * @return UserTokenInterfce
     */
    public function setToken($token);
      
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
     * @return UserTokenInterface
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
     * @return UserTokenInterface
     */
    public function setUpdated($updated);
    
}
