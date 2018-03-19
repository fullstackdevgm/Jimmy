<?php

namespace JimmyBase\Entity;

interface ReportShareInterface
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
     * @return ReportShareInterface
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
     * @return ReportShareInterface
     */
    public function setUserId($user_id);


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
     * @return ReportShareInterface
     */
    public function setReportId($report_id);

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus();

    /**
     * Set id.
     *
     * @param int $status
     * @return ReportShareInterface
     */
    public function setStatus($status);


    /**
     * Get date.
     *
     * @return datetime
     */
    public function getDate();

    /**
     * Set date.
     *
     * @param int $date
     * @return ReportShareInterface
     */
    public function setDate($date);

	
}
