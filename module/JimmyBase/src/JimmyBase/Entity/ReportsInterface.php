<?php

namespace JimmyBase\Entity;

interface ReportsInterface
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
     * @return ReportInterface
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
     * @return ReportInterface
     */
    public function setUserId($user_id);

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
     * @return ReportInterface
     */
    public function setTitle($title);
	
	
	/**
     * Get notes.
     *
     * @return string 
     */
    public function getNotes();

    /**
     * Set notes.
     *
     * @param string $notes
     * @return ReportInterface
     */
    public function setNotes($notes);

	
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
     * @return ReportInterface
     */
    public function setStatus($status);


    /**
     * Get is_paid.
     *
     * @return int
     */
    public function getPaid();

    /**
     * Set is_paid.
     *
     * @param string $is_paid
     * @return ReportInterface
     */
    public function setPaid($paid);
	
		
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
     * @return ReportInterface
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
     * @return ReportInterface
     */
    public function setUpdated($updated);

	
}
