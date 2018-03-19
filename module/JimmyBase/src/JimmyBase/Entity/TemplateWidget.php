<?php

namespace JimmyBase\Entity;

class TemplateWidget implements TemplateWidgetInterface
{
    /**
     * @var int
     */
    protected $id;
   
    /**
     * @var int
     */
    protected $template_id;

    /**
     * @var string
     */
    protected $title;
   
    /**
     * @var string
     */
    protected $type;
    /**
     * @var array
     */
    protected $fields = array(); 
    
    /**
     * @var string
     */
    protected $comments;
    
    /**
     * @var string
     */
    protected $channel;
    
    /**
     * @var date
     */
    protected $created;
  	
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
     * @return TemplateWidgetInterface
     */
    public function setId($id)
    {
        $this->id = (int) $id;
        return $this;
    }
   
   
    public function getTemplateId()
    {
        return $this->template_id;
    }

    /**
     * Set report_id.
     *
     * @param int $report_id
     * @return TemplateWidgetInterface
     */
    public function setTemplateId($template_id)
    {
        $this->template_id = (int) $template_id;
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
     * @return TemplateWidgetInterface
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
     * @return TemplateWidgetInterface
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * @return TemplateWidgetInterface
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
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
     * Set comments.
     *
     * @param string $comments
     * @return TemplateWidgetInterface
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
        return $this;
    }
    
    
    /**
     * 
     * @return string;
     */
    public function getChannel()
    {
        return $this->channel;
        
    }
    
   /**
    * 
    * @param type $channel
    * @return TemplateWidgetInterface
    */ 
    public function setChannel($channel)
    {
        $this->channel = $channel;
        return $this;
    }
  
    /**
     * Set created.
     *
     * @param string $created
     * @return TemplateWidgetInterface
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

     
}
