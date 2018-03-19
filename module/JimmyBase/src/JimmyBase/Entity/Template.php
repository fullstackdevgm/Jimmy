<?php

namespace JimmyBase\Entity;

class Template implements TemplateInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $templateName;
    
    /**
     * @var int
     */
    protected $userId;
    
    /**
     * @var string
     */
    protected $type;
            
    /**
     * @var datetime
     */
    protected $created ;
    
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
     * @return TemplateInterface
     */
    public function setId($id)
    {
        $this->id = intVal($id);
        return $this;
    }

    /**
     * Get templateName
     *
     * @return string
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }

    /**
     * Set templateName.
     *
     * @param string $templateName
     * @return TemplateInterface
     */
    public function setTemplateName($templateName)
    {
        $this->templateName = $templateName;
        return $this;
    } 
    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     * @return TemplateInterface
     */
    public function setUserId($userId)
    {
        $this->userId = intVal($userId);
        return $this;
    }
    /**
     * Get type
     * 
     * @return string  
     */
    public function getType() {
        return $this->type;
    }
    
    /**
     * Set type
     * 
     * @param string $type
     * @return TemplateInterface
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * @param datetime $created
     * @return UserInterface
     */
    public function setCreated()
    {
        $created =  date('Y/m/d H:i:s') ;
        $this->created = $created;
        return $this;
    }

}
