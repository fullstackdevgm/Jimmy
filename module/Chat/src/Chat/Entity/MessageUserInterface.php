<?php

namespace Chat\Entity;

interface MessageUserInterface
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
     * @return MessageInterface
     */
    public function setId($id);


    public function getMsgId();

    /**
     * Set user_id.
     *
     * @param int $user_id
     * @return MessageInterface
     */
    public function setMsgId($msg_id);



    public function getUserId();

    /**
     * Set user_id.
     *
     * @param int $user_id
     * @return MessageInterface
     */
    public function setUserId($user_id);


	 /**
     * Get date.
     *
     * @return datetime
     */
    public function getDate();

	/**
     * Set date.
     *
     * @return string
     */
    public function setDate($date);

}
