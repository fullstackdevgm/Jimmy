<?php

namespace Chat\Entity;

class MessageWidget implements MessageWidgetInterface
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
    protected $widget_id;

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


    public function getWidgetId()
    {
        return $this->widget_id;
    }

    /**
     * Set widget_idwidget_id.
     *
     * @param int $widget_id
     * @return MessageInterface
     */
    public function setWidgetId($widget_id)
    {
        $this->widget_id = (int) $widget_id;
        return $this;
    }

}
