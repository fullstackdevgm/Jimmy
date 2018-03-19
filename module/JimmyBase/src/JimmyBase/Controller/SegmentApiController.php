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
use Zend\View\Model\JsonModel;

use JimmyBase\Service\Client as ClientService;
use JimmyBase\Entity\ClientAccounts;

class SegmentApiController extends AbstractRestfulController
{
	/**
     * @var UserService
     */
  protected $clientService;




  public function getList(){
      session_write_close();
      $client_account_id = (int) $this->params('client_account_id');
      $client_account_mapper = $this->getServiceLocator()->get('jimmybase_clientaccounts_mapper');
      if(!$client_account_id) return false;
      
      $client_account = $client_account_mapper->findById($client_account_id);
      $user_token_mapper = $this->getServiceLocator()->get('jimmybase_usertoken_mapper');
      
      if( !$client_account || !$client_account->getUserTokenId() )
        return false;
      
      $token = $user_token_mapper->findById($client_account->getUserTokenId());
      
       
      if( !$client_account->getAccountId() or !$token->getToken())
        return false;

      $segment_array = $this->getServiceLocator()
                ->get('jimmybase_clientapi_service')
                ->setChannel(ClientAccounts::GOOGLE_ANALYTICS)
                ->setApiAccessToken(unserialize($token->getToken()))
                ->fetchSegments($client_account->getAccountId());


      if($segment_array){
          foreach ($segment_array->getItems() as $val) {
            $segmentList[] = array('id'=>$val->id,'name'=>$val->name);
          }
      }
      
      return new JsonModel($segmentList);
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



