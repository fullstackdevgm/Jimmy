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

class CampaignApiController extends AbstractRestfulController
{
	/**
     * @var UserService
     */
    protected $clientService;

	/**
     * @var ReportService
     */
    protected $reportService;





	public function getList(){
      session_write_close();

      $service    = $this->getClientService();
      $client_account_id = (int) $this->params('client_account_id');

      $client_account_mapper    = $this->getServiceLocator()->get('jimmybase_clientaccounts_mapper');
      $user_token_mapper = $this->getServiceLocator()->get('jimmybase_usertoken_mapper');

      if(!$client_account_id) return false;

      $client_account = $client_account_mapper->findById($client_account_id);
      

      if( !$client_account )
        return new JsonModel(array(array('success'=>'false','message'=>'Client account does not exists')));
      
       if( !$client_account->getUserTokenId())
        return new JsonModel(array(array('success'=>'false','message'=>'Migration not done')));
       
      $token = $user_token_mapper->findById($client_account->getUserTokenId());
      if( !$client_account->getAccountId() or !$token->getToken())
        return new JsonModel(array(array('success'=>'false','message'=>'Authentication token does not exist')));

      $type      ='All';

      switch ($client_account->getChannel()) {
        case 'googleadwords':
          $campaigns = $this->getServiceLocator()->get('jimmybase_adwords_service')->setClientAccount($client_account)
                                                                                   ->getCampaigns($type);
          if($campaigns){
              foreach ($campaigns as  $key=>$val) {
                $campaignsList[] = array('id'=>$key,'name'=>$val);
              }
          }
          break;
        case 'bingads':
          $campaigns = $this->getServiceLocator()->get('jimmybase_bingads_service')->setClientAccount($client_account)
                                                                                   ->getCampaigns();


          if($campaigns) {
              foreach ($campaigns as  $campaign) {
                $campaignsList[] = array('id'=>$campaign->Id,'name'=>$campaign->Name);
              }
          }

          break;
        default:
          # code...
          break;
      }




     return new JsonModel($campaignsList);
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



