<?php

namespace Chat\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class MessageApiController extends AbstractRestfulController
{

    protected $messageService;

    protected $identifierName = 'msg_id';

    public function getList(){
        $widget_id = $this->params('widget_id');


        $service   = $this->getMesssageService();
        $messages  = $service->getChatMessageMapper()->fetchWidgetMessage($widget_id);
        if(!$messages)
             return new JsonModel(array('success'=>false,'message'=>'Message not found'));

         //foreach ($messages as $key => $value) {
             # code...
       //  }


     return new JsonModel($messages);
    }


    public function get($msg_id){
        $service    = $this->getMesssageService();

        if(!$msg_id)
            return new JsonModel(array('success'=>false,'message'=>'Message id is required'));

        $message = $service->getMapper()->findByIdToArray($msg_id);

        if(!$message)
             return new JsonModel(array('success'=>false,'message'=>'Message not found'));


     return new JsonModel($message);
    }

    public function create($data){
        $request    = $this->getRequest();

        $message_service      = $this->getServiceLocator()->get('message_service');
        $current_user_id      = $this->ZfcUserAuthentication()->getIdentity()->getId();

        $message_post_array   = $request->getPost()->toArray();

        $message['user_id']        = $current_user_id;
        $message['message']        = $data['message'];
        $message['widget_id']      = $data['widget_id'];


       if($message_service->save($message))
            return new JsonModel(array("success"=>true,'message' => 'Message created successfully!'));
       else
            return new JsonModel(array("success"=>true,'message' => 'A problem occurred while creating message!'));

    }

    public function delete($message_id){

        if (!$message_id)
            return  false;



        if($this->getMesssageService()->getMapper()->delete($message_id)){
            return new JsonModel(array('success' => true,'message'=>"Message deleted successfully"));
        } else {
            return new JsonModel(array('success' => true,'message'=>"A problem occurred while deleting the message"));
        }

    }


    public function update($client_id,$data){

        $client_service   = $this->getClientService();

        $client =  $client_service->getClientMapper()->findById($client_id);


        if(!$client)
            return new JsonModel(array("success"=>false,'message' => 'Client could not be found!'));

        if($data['action']=='update-name'){

            $client->setName($data['name']);
            $client = $client_service->save($client);

            if($client)
              return new JsonModel(array("success" => true,'message' => 'Client name updated!','client_id'=>$client->getId()));
            else
              return new JsonModel(array("success" => false,'message' => 'A problem occurred while updating client name!'));

        }


    }

    public function getMesssageService()
    {
        if (!$this->messageService) {
            $this->messageService = $this->getServiceLocator()->get('message_service');
        }
        return $this->messageService;
    }

    public function setMessageService($messageService)
    {
        $this->messageService = $messageService;
        return $this;
    }



}

