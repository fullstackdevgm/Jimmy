<?php

namespace Application\Controller;

use Zend\Form\Form;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Stdlib\Parameters;
use Zend\View\Model\ViewModel;
use Zend\Crypt\Password\Bcrypt;
use ZfcUser\Controller\UserController as ZfcUserController;
use Zend\Session\Container as SessionContainer;
use JimmyBase\Form as JimmyBaseForm;
use JimmyBase\Validator as JimmyBaseValidator;

use Application\Service\User as UserService;
use JimmyBase\Entity\User as UserEntity;

class CoworkerApiController extends AbstractRestfulController
{

     /**
     * @var userService
     */
    protected $userService;

     /**
     * @var userService
     */
    protected $coworkerService;

    protected $identifierName = 'coworker_id';


    public function getList(){

        $coworkersList = $this->getServiceLocator()->get('jimmybase_agency_mapper')->fetchAllCoworkers($this->ZfcUserAuthentication()->getIdentity()->getId())->toArray();

        return new JsonModel($coworkersList);
    }

    public function get($coworker_id){
        $userService    = $this->getUserService();

        $coworker = $userService->getUserMapper()->findByIdToArray($coworker_id);

        return new JsonModel($coworker);

    }

   	public function create($data){

		$userService    = $this->getUserService();

		$current_user = $this->zfcUserAuthentication()->getIdentity();


		if($data){

			if($userService->getUserMapper()->findByEmail($data['email'])){
				$json = array('success'=>false,'message'=>'The user already exists and cannot be added as a coworker. Please try a different email address!');
			} else {

			 $coworkerInfo     = array(
								   'email' 					=> trim($data['email']),
								   'name'   				=> trim($data['name']),
								   'state'   				=> UserEntity::ACTIVE,
								   'type'   				=> UserEntity::COWORKER,
								 );


			$coworker = $userService->save($coworkerInfo);

			if($coworker){

				 $coworker->setKey('parent');
				 $coworker->setValue($current_user->getId());

				 if($userService->saveMeta($coworker)){

			    	$this->getEventManager()->trigger('registerCoworker', $this, array('coworker' => $coworker,'agency' => $current_user));
			    	$json = array('success'=>true,'coworker_id'=>$coworker->getId(),'message'=>'Coworker added successfully!');
				}
		    }  else
				$json = array('success'=>false,'coworker_id'=>$coworker->getId(),'message'=>'A problem occurred while adding the coworker!');
		   }
		}


		return  new JsonModel($json);
    }


	public function delete($coworker_id){

        if (!$coworker_id)
            return  array('success' => false,'message'=>"Coworker  not found!");

			$userService  = $this->getUserService();

			if($userService->deleteCoworker($coworker_id))
			  $json = array('success' => true,'message'=>"Coworker deleted successfully!");
		    else
		   	  $json = array('success' => false,'message'=>"Coworker couldnot be deleted!");

        return new JsonModel($json);
    }


    public function getUserService()
    {
        if (!$this->userService) {
            $this->userService = $this->getServiceLocator()->get('jimmybase_user_service');
        }

        return $this->userService;
    }

    public function setUserService($userService)
    {
        $this->userService = $userService;
        return $this;
    }

}
