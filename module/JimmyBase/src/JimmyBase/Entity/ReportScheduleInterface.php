<?php

namespace JimmyBase\Entity;

interface ReportScheduleInterface
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
     * Get report_id.
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
    public function getFrequency();

    /**
     * Set id.
     *
     * @param int $status
     * @return ReportShareInterface
     */
    public function setFrequency($frequency);


    /**
     * Get date.
     *
     * @return datetime
     */
    public function getStartDate();

    /**
     * Set date.
     *
     * @param int $date
     * @return ReportShareInterface
     */
    public function setStartDate($start_date);

    /**
     * Get date.
     *
     * @return datetime
     */
    public function getNextScheduleDate();

    /**
     * Set date.
     *
     * @param int $date
     * @return ReportShareInterface
     */
    public function setNextScheduleDate($date);

    /**
     * Get timezone.
     *
     * @return string
     */
    public function getTimezone();

    /**
     * Set date.
     *
     * @param int $date
     * @return ReportShareInterface
     */
    public function setTimezone($timezone);

    /**
     * Get email.
     *
     * @return datetime
     */
    public function getEmail();

    /**
     * Set email.
     *
     * @param int $email
     * @return ReportShareInterface
     */
    public function setEmail($email);

     /**
     * Get email.
     *
     * @return datetime
     */
    public function getCcme();

    /**
     * Set cc_me.
     *
     * @param int $cc_me
     * @return ReportShareInterface
     */
    public function setCcme($ccme);

    /**
     * Get created.
     *
     * @return datetime
     */
    public function getCreated();

    /**
     * Set created.
     *
     * @param int $created
     * @return ReportShareInterface
     */
    public function setCreated($created);

    /**
     * Get updated.
     *
     * @return datetime
     */
    public function getUpdated();

    /**
     * Set updated.
     *
     * @param int $updated
     * @return ReportShareInterface
     */
    public function setUpdated($updated);


}
