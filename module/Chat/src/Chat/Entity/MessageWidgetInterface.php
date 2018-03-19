<?php

namespace Chat\Entity;

interface MessageWidgetInterface
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



    public function getWidgetId();

    /**
     * Set user_id.
     *
     * @param int $user_id
     * @return MessageInterface
     */
    public function setWidgetId($widget_id);


}
