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

class ProfileApiController extends AbstractRestfulController
{
	/**
     * @var UserService
     */
  protected $clientService;






	public function getList(){
      session_write_close();

      $service    = $this->getClientService();
      $client_account_id = (int) $this->params('client_account_id');

      $client_account_mapper    = $this->getServiceLocator()->get('jimmybase_clientaccounts_mapper');
      $user_token_mapper = $this->getServiceLocator()->get('jimmybase_usertoken_mapper');

      if (!$client_account_id) return false;

      $client_account = $client_account_mapper->findById($client_account_id);

      if (!$client_account || !$client_account->getUserTokenId())
        return false;
      
      $token = $user_token_mapper->findById($client_account->getUserTokenId());
      if( !$client_account->getAccountId() or !$token->getToken())
        return false;


      $type      ='All';

      $profiles_array = $this->getServiceLocator()
                ->get('jimmybase_clientapi_service')
                ->setChannel(ClientAccounts::GOOGLE_ANALYTICS)
                ->setApiAccessToken(unserialize($token->getToken()))
                ->fetchWebProfiles($client_account->getAccountId());


      if($profiles_array){
          foreach ($profiles_array as  $key=>$val) {
            $profilesList[] = array('id'=>$val['web_property_id'].':'.$val['profile_id'],'name'=>$val['name'].'-'.$val['profile_id'],'currency'=>$val['currency']);
          }
      }

     return new JsonModel($profilesList);
    }

    public function get($client_id){
        $service    = $this->getClientService();


        $clientList = $service->getClientMapper()->findByIdToArray($client_id);

     return new JsonModel($clientList);
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



