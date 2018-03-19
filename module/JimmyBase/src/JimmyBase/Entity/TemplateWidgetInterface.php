<?php

namespace JimmyBase\Entity;

interface TemplateWidgetInterface
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
     * @return TemplateWidgetInterface
     */
    public function setId($id);
	
    /**
     * Get template_id.
     *
     * @return int
     */
    public function getTemplateId();

    /**
     * Set id.
     *
     * @param int $id
     * @return TemplateWidgetInterface
     */
    public function setTemplateId($template_id);

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set title.
     *
     * @param string $title
     * @return TemplateWidgetInterface
     */
    public function setTitle($title);
	
    /**
     * Get type.
     *
     * @return string
     */
    public function getType();

    /**
     * Set type.
     *
     * @param string $type
     * @return TemplateWidgetInterface
     */
    public function setType($type);
   
    /**
     * Get fields.
     *
     * @return array 
     */
    public function getFields();

    /**
     * Set fields.
     *
     * @param array $fields
     * @return TemplateWidgetInterface
     */
    public function setFields($fields);
    
     /**
     * Get comments.
     *
     * @return string
     */
    public function getComments();
   
    /**
     * Set comments.
     *
     * @param string $comments
     * @return TemplateWidgetInterface
     */
    public function setComments($comments);
   
        
    /**
     * Get Channel.
     * 
     * return string
     */ 
    public function getChannel();
    
    /**
     * 
     * @param string $channel
     * @return TemplateWidgetInterface
     */
    public function setChannel($channel);
    
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
     * @return TemplateWidgetInterface
     */
    public function setCreated($created);



}
