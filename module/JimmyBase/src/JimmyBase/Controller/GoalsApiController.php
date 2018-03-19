<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace JimmyBase\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\AbstractRestfulController;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ModelInterface;
use Zend\Validator\InArray;
use Zend\Form\Form;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Stdlib\Parameters;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Session\Container as SessionContainer;
use Zend\Cache\StorageFactory;
use Zend\View\Renderer\PhpRenderer;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;


use JimmyBase\Service\Client as ClientService;
use JimmyBase\Form as JimmyBaseForm;
use JimmyBase\Validator as JimmyBaseValidator;
use JimmyBase\Entity\ClientAccounts;

class GoalsApiController extends AbstractRestfulController
{
	/**
     * @var UserService
     */
  protected $clientService;






	public function getList(){
      session_write_close();

      $service    = $this->getClientService();
      $client_account_id = (int) $this->params('client_account_id');
      $profile_id        =  $this->params('profile_id');

      if(!$client_account_id) return false;

      if(!$profile_id) return false;

      $client_account_mapper    = $this->getServiceLocator()->get('jimmybase_clientaccounts_mapper');
      $user_token_mapper = $this->getServiceLocator()->get('jimmybase_usertoken_mapper');

      $client_account = $client_account_mapper->findById($client_account_id);

      if( !$client_account || !$client_account->getUserTokenId() )
        return false;
      
       $token = $user_token_mapper->findById($client_account->getUserTokenId());
      if( !$client_account->getAccountId() or !$token->getToken())
        return false;

      $goals_api = $this->getServiceLocator()->get('jimmybase_goalsapi_service');
      $goals = $goals_api->setClientAccount($client_account)
                   ->setProfileId($profile_id)
                   ->getGoals();

      if($goals){
          foreach ($goals as  $key=>$val) {
            $profilesList[] = array('id'=>$key,'title'=>$val);
          }
      }

     return new JsonModel($profilesList);
    }

    public function get($client_id){
        $service    = $this->getClientService();


        $clientList = $service->getClientMapper()->findByIdToArray($client_id);

     return new JsonModel($clientList);
    }

    public function fetchGoalsAction(){

        $client_service       = $this->getClientService();
        $client_account_mapper    = $this->getServiceLocator()->get('jimmybase_clientaccounts_mapper');
        $form                 = $this->getServiceLocator()->get('jimmybase_reports_form');
        $user_token_mapper = $this->getServiceLocator()->get('jimmybase_usertoken_mapper');
        # disable layout if request by Ajax
          $viewModel->setTerminal(true);

         $client_account_id = (int) $this->params('client_account_id');
         $profile_id      =  $this->params('profile_id');
        if(!$client_account_id) return false;

        $client_account = $client_account_mapper->findById($client_account_id);
       
        if(!$client_account || !$client_account->getUserTokenId())
          return false;
        
        $token = $user_token_mapper->findById($client_account->getUserTokenId());
        if( !$client_account->getAccountId() or !$token->getToken())
            return false;


          $goals_api = $this->getServiceLocator()->get('jimmybase_goalsapi_service');

          $goals = $goals_api->setClientAccount($client_account)
                     ->setProfileId($profile_id)
                     ->getGoals();

        $response->setContent(\Zend\Json\Json::encode($goals));

        return $response;
      }
   public function getClientService()
    {
        if (!$this->clientService) {
            $this->clientService = $this->getServiceLocator()->get('jimmybase_client_service');
        }
        return $this->clientService;
    }

    public function setClientService(ClientService $clientService)
    {
        $this->clientService = $clientService;
        return $this;
    }


}



