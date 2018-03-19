<?php

namespace Chat\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator\ClassMethods;
use ZfcBase\EventManager\EventProvider;

use Chat\Mapper\MessageInterface as MessageMapperInterface;

class Message extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     * @var MessageMapperInterface
     */
    protected $messageMapper;

    /**
     * @var MessageMapperInterface
     */
    protected $messageChatMapper;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;


    public function save(array $data)
    {

    	if($data['id']){
    		$message = $this->getMapper()->findById($data['id']);
    	} else {
    		$message = new  \Chat\Entity\Message();
        }

		$message->setUserId($data['user_id']);
		$message->setMessage($data['message']);
		$message->setDate($data['date']);
		$now = date('Y-m-d h:i:s');

		if(!$message->getId()){
			$message->setDate($now);
        }

        $this->getEventManager()->trigger(__FUNCTION__, $this, array('message' => $message, 'form' => $form));

		if(!$message->getId()){
        	if($this->getMapper()->insert($message)){
                /*
                //Sender
                $sender = new \Chat\Entity\MessageUser();
                $sender->setUserId($data['user_id']);
                $sender->setMsgId($message->getId());
                $sender->setDate($now);
                $this->getMapper()->insert($sender,'message_users');
                //Recipient
                $recipient = new \Chat\Entity\MessageUser();
                $recipient->setUserId($data['recipient_id']);
                $recipient->setMsgId($message->getId());
                $recipient->setDate($now);
                $this->getMapper()->insert($recipient,'message_users');
                */

                //Widget
                $widget = new \Chat\Entity\MessageWidget();
                $widget->setWidgetId($data['widget_id']);
                $widget->setMsgId($message->getId());
                $this->getMapper()->insert($widget,'message_widget');
            }
        }
		else
		    $this->getMapper()->update($message);

        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('message' => $message, 'form' => $form));

        return $widget;
    }



    /**
     * getMessageMapper
     *
     * @return MessageMapperInterface
     */
    public function getMapper()
    {
        if (null === $this->messageMapper) {
            $this->messageMapper = $this->getServiceManager()->get('message_mapper');
        }

        return $this->messageMapper;
    }

    /**
     * setUserMapper
     *
     * @param UserMapperInterface $userMapper
     * @return User
     */
    public function setMapper(MessageMapperInterface $messageMapper)
    {
        $this->messageMapper = $messageMapper;
        return $this;
    }


    /**
     * getMessageMapper
     *
     * @return MessageMapperInterface
     */
    public function getChatMessageMapper()
    {
        if (null === $this->messageChatMapper) {
            $this->messageChatMapper = $this->getServiceManager()->get('message_chat_mapper');
        }

        return $this->messageChatMapper;
    }

    /**
     * setUserMapper
     *
     * @param UserMapperInterface $userMapper
     * @return User
     */
    public function setChatMessageMapper(MessageMapperInterface $messageChatMapper)
    {
        $this->messageChatMapper = $messageChatMapper;
        return $this;
    }


    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }
}
