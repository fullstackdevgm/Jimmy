<?php

namespace JimmyBase\Entity;

interface WidgetInterface
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
     * @return WidgetInterface
     */
    public function setId($id);
	
	/**
     * Get user_id.
     *
     * @return int
     */
    public function getReportId();

    /**
     * Set id.
     *
     * @param int $id
     * @return WidgetInterface
     */
    public function setReportId($report_id);

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
     * @return WidgetInterface
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
     * @return WidgetInterface
     */
    public function setType($type);
    
    /**
     * Get client_account_id.
     *
     * @return string
     */
    public function getClientAccountId();

    /**
     * Set client_account_id.
     *
     * @param string $client_account_id
     * @return WidgetInterface
     */
    public function setClientAccountId($client_account_id);
    
    /**
     * Get sub_type.
     *
     * @return string
     */
    public function getSubType();

    /**
     * Set type.
     *
     * @param string $type
     * @return WidgetInterface
     */
    public function setSubType($sub_type);    
	
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
     * @return WidgetInterface
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
     * @return WidgetInterface
     */
    public function setComments($comments);
		
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
     * @return WidgetInterface
     */
    public function setCreated($created);


	/**
     * Get updated.
     *
     * @return date
     */
    public function getUpdated();

    /**
     * Set updated.
     *
     * @param date $updated
     * @return WidgetInterface
     */
    public function setUpdated($updated);

    /**
     * Get state.
     *
     * @return int
     */
    public function getStatus();

    /**
     * Set status.
     *
     * @param int $status
     * @return WidgetInterface
     */
    public function setStatus($status);

    /**
     * Get order.
     *
     * @return int
     */
    public function getOrder();
	
    /**
     * Set order.
     *
     * @param int $order
     * @return WidgetInterface
     */
    public function setOrder($order);
    
     /**
     * Get insight
     *
     * @return string
     */
    public function getInsight();
            
    /**
     * Set status.
     *
     * @param string $insight
     * @return WidgetInterface
     */
    public function setInsight($insight);
}
