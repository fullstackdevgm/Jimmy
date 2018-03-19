<?php

namespace JimmyBase\Entity;

class ReportSchedule implements ReportScheduleInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $report_id;

    /**
     * @var int
     */
    protected $frequency;

    /**
     * @var int
     */
    protected $start_date;

    /**
     * @var int
     */
    protected $next_schedule_date;


    /**
     * @var int
     */
    protected $timezone;


    /**
     * @var int
     */
    protected $email;

    /**
     * @var int
     */
    protected $ccme;

    /**
     * @var int
     */
    protected $from_name;

    /**
     * @var int
     */
    protected $from_email;

    /**
     * @var int
     */
    protected $subject;

    /**
     * @var int
     */
    protected $body;



    /**
     * @var int
     */
    protected $created;

    /**
     * @var int
     */
    protected $updated;


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
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * Set status.
     *
     * @param int $status
     * @return ReportInterface
     */
    public function setFrequency($frequency)
    {
        $this->frequency =  $frequency;
        return $this;
    }

     /**
     * Get status.
     *
     * @return in
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * Set status.
     *
     * @param int $status
     * @return ReportInterface
     */
    public function setStartDate($start_date)
    {
        $this->start_date =  $start_date;
        return $this;
    }

    /**
     * Get status.
     *
     * @return in
     */
    public function getNextScheduleDate()
    {
        return $this->next_schedule_date;
    }

    /**
     * Set status.
     *
     * @param int $status
     * @return ReportInterface
     */
    public function setNextScheduleDate($next_schedule_date)
    {
        $this->next_schedule_date =  $next_schedule_date;
        return $this;
    }

    /**
     * Get timezone.
     *
     * @return in
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Set timezone.
     *
     * @param int $timezone
     * @return ReportInterface
     */
    public function setTimezone($timezone)
    {
        $this->timezone =  $timezone;
        return $this;
    }

    /**
     * Get status.
     *
     * @return in
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set status.
     *
     * @param int $status
     * @return ReportInterface
     */
    public function setEmail($email)
    {
        $this->email =  $email;
        return $this;
    }


    /**
     * Get cc_me.
     *
     * @return in
     */
    public function getCcme()
    {
        return (int)$this->ccme;
    }

    /**
     * Set cc_me.
     *
     * @param int $cc_me
     * @return ReportInterface
     */
    public function setCcme($ccme)
    {
        $this->ccme =  (int)$ccme;
        return $this;
    }

    /**
     * Get from_name.
     *
     * @return in
     */
    public function getFromName()
    {
        return $this->from_name;
    }

    /**
     * Set from_name.
     *
     * @param int $from_name
     * @return ReportInterface
     */
    public function setFromName($from_name)
    {
        $this->from_name =  $from_name;
        return $this;
    }

    /**
     * Get from_email.
     *
     * @return in
     */
    public function getFromEmail()
    {
        return $this->from_email;
    }

    /**
     * Set from_email.
     *
     * @param int $from_email
     * @return ReportInterface
     */
    public function setFromEmail($from_email)
    {
        $this->from_email =  $from_email;
        return $this;
    }



    /**
     * Get subject.
     *
     * @return in
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set subject.
     *
     * @param int $subject
     * @return ReportInterface
     */
    public function setSubject($subject)
    {
        $this->subject =  $subject;
        return $this;
    }


    /**
     * Get subject.
     *
     * @return in
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set subject.
     *
     * @param int $subject
     * @return ReportInterface
     */
    public function setBody($body)
    {
        $this->body =  $body;
        return $this;
    }


    /**
     * Get status.
     *
     * @return in
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set created.
     *
     * @param int $status
     * @return ReportInterface
     */
    public function setCreated($created)
    {
        $this->created =  $created;
        return $this;
    }



    /**
     * Get updated.
     *
     * @return date
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set updated.
     *
     * @param date $date
     * @return ReportInterface
     */
    public function setUpdated($updated)
    {
        $this->updated =  $updated;
        return $this;
    }


}
