<?php

namespace JimmyBase\Entity;

class ReportShare implements ReportShareInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $user_id;
    
    /**
     * @var int
     */
    protected $report_id;
    
    /**
     * @var int
     */
    protected $status;
    
    /**
     * @var int
     */
    protected $date;
    
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
     * @return ReportInterface
     */
    public function setId($id)
    {
        $this->id = (int) $id;
        return $this;
    }

    /**
     * Get user_id.
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set user_id.
     *
     * @param string $user_id
     * @return ReportInterface
     */
    public function setUserId($user_id)
    {
        $this->user_id =  $user_id;
        return $this;
    }     
	
    
     /**
     * Get report_id.
     *
     * @return int
     */
    public function getReportId()
    {
        return $this->report_id;
    }

    /**
     * Set report_id.
     *
     * @param in $report_id
     * @return ReportInterface
     */
    public function setReportId($report_id)
    {
        $this->report_id =  $report_id;
        return $this;
    }       
     /**
     * Get status.
     *
     * @return in
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status.
     *
     * @param int $status
     * @return ReportInterface
     */
    public function setStatus($status)
    {
        $this->status =  $status;
        return $this;
    }     
    
     /**
     * Get status.
     *
     * @return in
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set status.
     *
     * @param int $status
     * @return ReportInterface
     */
    public function setDate($date)
    {
        $this->date =  $date;
        return $this;
    }     
	
}
