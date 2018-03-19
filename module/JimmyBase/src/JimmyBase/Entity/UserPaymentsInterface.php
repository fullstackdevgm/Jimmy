<?php

namespace JimmyBase\Entity;

interface UserPaymentsInterface
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
    public function getUserId();

    /**
     * Set id.
     *
     * @param int $id
     * @return WidgetInterface
     */
    public function setUserId($user_id);

    /**
     * Get title.
     *
     * @return string
     */
    public function getAmount();

    /**
     * Set title.
     *
     * @param string $title
     * @return WidgetInterface
     */
    public function setAmount($amount);
	
    /**
     * Get type.
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set type.
     *
     * @param string $type
     * @return WidgetInterface
     */
    public function setStatus($status);
    
    /**
     * Get sub_type.
     *
     * @return string
     */
    public function getCurrency();

    /**
     * Set type.
     *
     * @param string $type
     * @return WidgetInterface
     */
    public function setCurrency($currency);    
	
	/**
     * Get fields.
     *
     * @return array 
     */
    public function getDate();

    /**
     * Set fields.
     *
     * @param array $fields
     * @return WidgetInterface
     */
    public function setDate($date);
	
	
	/**
     * Get fields.
     *
     * @return array 
     */
    public function getProcessor();

    /**
     * Set fields.
     *
     * @param array $fields
     * @return WidgetInterface
     */
    public function setProcessor($processor);
	
	/**
     * Get fields.
     *
     * @return array 
     */
    public function getTransId();

    /**
     * Set fields.
     *
     * @param array $fields
     * @return WidgetInterface
     */
    public function setTransId($trans_id);
	
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
		
	

}
