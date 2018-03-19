<?php

namespace Chat\Entity;

class Message implements MessageInterface
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
     * @var string
     */
    protected $message;


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
     * Get message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set title.
     *
     * @param string $message
     * @return MessageInterface
     */
    public function setMessage($message)
    {
        $this->message = $message;
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
