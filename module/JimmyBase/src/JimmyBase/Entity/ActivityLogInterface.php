<?php

namespace JimmyBase\Entity;

interface ActivityLogInterface
{
      
    /**
     * Get ID.
     *
     * @return integer
     */
    public function getId();

    /**
     * Set id.
     *
     * @param int $id
     * @return ActivityLogInterface
     */
    public function setId($id);

    /**
     * Get UserId.
     *
     * @return integer
     */
    public function getUserId();

    
    /**
     * Set userId.
     *
     * @param int $userId
     * @return ActivityLogInterface
     */
    public function setUserId($userId);
    
    /**
     * Get parentId.
     *
     * @return integer
    */
    public function getParentId();

    
    /**
     * Set userId.
     *
     * @param int $parentId
     * @return ActivityLogInterface
     */
    public function setParentId($parentId);  
	
    /**
     * Get message.
     *
     * @return string
     */
    public function getMessage();

    /**
     * Set message.
     *
     * @param string $message
     * @return ActivityLogInterface
     */
    public function setMessage($message);
    
    
    /**
     * Get relatedItem
     * @return string
     */
    public function getRelatedItem();

    /**
     * Set relatedLink.
     *
     * @param string $relatedItem
     * @return ActivityLogInterface
     */
    public function setRelatedItem($relatedItem);
	
    
    /**
     * Get relatedLink
     * @return string
     */
    public function getRelatedLink();

    /**
     * Set relatedLink.
     *
     * @param string $relatedLink
     * @return ActivityLogInterface
     */
    public function setRelatedLink($relatedLink);
	
	
    /**
     * Get type.
     *
     * @return string
     */
    public function getCreated();

    /**
     * Set type.
     *
     * @param string $created
     * @return ActivityLogInterface
     */
    public function setCreated($created);

 

}

