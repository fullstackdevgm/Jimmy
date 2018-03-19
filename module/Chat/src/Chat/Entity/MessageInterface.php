<?php

namespace Chat\Entity;

interface MessageInterface
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


    public function getUserId();

    /**
     * Set user_id.
     *
     * @param int $user_id
     * @return MessageInterface
     */
    public function setUserId($user_id);

    /**
     * Get message.
     *
     * @return string
     */
    public function getMessage();

    /**
     * Set title.
     *
     * @param string $message
     * @return MessageInterface
     */
    public function setMessage($message);

	 /**
     * Get date.
     *
     * @return datetime
     */
    public function getDate();

	/**
     * Get updated.
     *
     * @return string
     */
    public function setDate($date);

}
