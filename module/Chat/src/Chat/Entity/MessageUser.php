<?php

namespace Chat\Entity;

class MessageUser implements MessageUserInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $msg_id;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var date
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
     * @return MessageInterface
     */
    public function setId($id)
    {
        $this->id = (int) $id;
        return $this;
    }


    public function getMsgId()
    {
        return $this->msg_id;
    }

    /**
     * Set user_id.
     *
     * @param int $user_id
     * @return MessageInterface
     */
    public function setMsgId($msg_id)
    {
        $this->msg_id = (int) $msg_id;
        return $this;
    }


    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set user_id.
     *
     * @param int $user_id
     * @return MessageInterface
     */
    public function setUserId($user_id)
    {
        $this->user_id = (int) $user_id;
        return $this;
    }


	 /**
     * Get date.
     *
     * @return datetime
     */
    public function getDate()
    {
        return $this->date;
    }


	/**
     * Set date.
     *
     * @return string
     */
    public function setDate($date)
    {
       $this->date = $date;
       return $this;
    }


}
