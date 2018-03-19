<?php

namespace JimmyBase\Entity;


class ActivityLog implements ActivityLogInterface
{
    /**
     * @var int
     */
    protected $id;
	
    /**
     * @var int
     */
    protected $userId;

    /**
     * @var int
     */
    protected $parentId;
	
    /**
     * @var string
     */
    protected $message;
    
    /**
     * @var string
     */
    protected $relatedItem;
  
    /**
     * @var string
     */
    protected $relatedLink;
  
    /**
     *
     * @var datetime
     */
    protected $created;
    
  
    
    /**
     * Get ID.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param int $id
     * @return ActivityLogInterface
     */
    public function setId($id)
    {
        $this->id = (int) $id;
        return $this;
    }

    /**
     * Get UserId.
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    
    /**
     * Set userId.
     *
     * @param int $userId
     * @return ActivityLogInterface
     */
    public function setUserId($userId)
    {
        $this->userId = (int) $userId;
        return $this;
    }
    
    /**
     * Get parentId.
     *
     * @return integer
    */
    public function getParentId()
    {
        return $this->parentId;
    }

    
    /**
     * Set userId.
     *
     * @param int $parentId
     * @return ActivityLogInterface
     */
    public function setParentId($parentId)
    {
        $this->parentId = (int) $parentId;
        return $this;
    }
   
   
	
    /**
     * Get message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set message.
     *
     * @param string $message
     * @return ActivityLogInterface
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }
    
     /**
     * Get relatedItem
     * @return string
     */
    public function getRelatedItem()
    {
        return $this->relatedItem;
    }

    /**
     * Set relatedLink.
     *
     * @param string $relatedItem
     * @return ActivityLogInterface
     */
    public function setRelatedItem($relatedItem)
    {
        $this->relatedItem = $relatedItem;
        return $this;
    }
	
    
    /**
     * Get relatedLink
     * @return string
     */
    public function getRelatedLink()
    {
        return $this->relatedLink;
    }

    /**
     * Set relatedLink.
     *
     * @param string $relatedLink
     * @return ActivityLogInterface
     */
    public function setRelatedLink($relatedLink)
    {
        $this->relatedLink = $relatedLink;
        return $this;
    }
	
	
    /**
     * Get type.
     *
     * @return string
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set type.
     *
     * @param string $created
     * @return ActivityLogInterface
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

 

}
