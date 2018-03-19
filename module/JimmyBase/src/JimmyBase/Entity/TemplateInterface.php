<?php
namespace JimmyBase\Entity;

interface TemplateInterface 
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
     * @return TemplateInterface
     */
    public function setId($id);

    /**
     * Get templateName
     *
     * @return string
     */
    public function getTemplateName();

    /**
     * Set templateName.
     *
     * @param string $templateName
     * @return TemplateInterface
     */
    public function setTemplateName($templateName);
    
    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId();
   
    /**
     * Set userId.
     *
     * @param int $userId
     * @return TemplateInterface
     */
    public function setUserId($userId);
    
    /**
     * Get type
     * 
     * @return string  
     */
    public function getType();     
     
    /**
     * Set type
     * 
     * @param string $type
     * @return TemplateInterface
     */
    public function setType($type);
    
    
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
     * @return UserInterface
     */
    public function setCreated();
}