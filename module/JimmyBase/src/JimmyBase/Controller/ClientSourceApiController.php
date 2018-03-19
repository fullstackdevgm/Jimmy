<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace JimmyBase\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Validator\InArray;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Stdlib\Parameters;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Session\Container as SessionContainer;

use JimmyBase\Service\Client as ClientService;

class ClientSourceApiController extends AbstractRestfulController
{
    protected $clientService;

    protected $cache;

    protected $identifierName = 'client_source_id';




	public function getList(){
        $service        = $this->getClientService();
        $client_id      = $this->params('client_id');
		$matchedRoute 	= $this->getEvent()->getRouteMatch()->getMatchedRouteName();

        $current_user_id = $this->ZfcUserAuthentication()->getIdentity()->getId();

        if($matchedRoute=='client/client-accounts'){
        	return $this->clientAccounts();
        }


        if($client_id){
            $client_account_mapper = $this->getServiceLocator()->get('jimmybase_clientaccounts_mapper');
            $client_sources = $client_account_mapper->findByClientId($client_id);
            $user_token_mapper = $this->getServiceLocator()->get('jimmybase_usertoken_mapper');
            // $tmp_client_sources = $client_sources;
            $client_sources->buffer();
            foreach ($client_sources as $client) {
                $user_token_id = $client->getUserTokenId();
                $channel = $client->getChannel();
                $token = $user_token_mapper->findById($user_token_id);
                if ($token) {
                    $strToken = $token->getToken();
                    if (!strpos($strToken, 'refresh_token') && ($channel == 'googleadwords' || $channel == 'googleanalytics')) {
                        $client->setUserTokenId(null);
                        $client_account_mapper->update($client);
                    }
                }
                else {
                    if ($channel == 'googleadwords' || $channel == 'googleanalytics') {
                        $client->setUserTokenId(null);
                        $client_account_mapper->update($client);
                    }
                }
            }
            // exit();
        }

     return new JsonModel($client_sources);
    }

    public function get($client_id){
        $service    = $this->getClientService();


        $clientList = $service->getClientMapper()->findByIdToArray($client_id);

     return new JsonModel($clientList);
    }



    public function delete($client_source_id){

		$request   = $this->getRequest();
		$response  = $this->getResponse();

		$client_accounts_mapper = $this->getServiceLocator()->get('jimmybase_clientaccounts_mapper');
		$widget_mapper 			= $this->getServiceLocator()->get('jimmybase_widget_mapper');



		$client_account    = $client_accounts_mapper->findById($client_source_id);

		$clientDetails = $this->getClientService()->getClientMapper()->findById($client_account->getClientId());

		if(!$client_account)
			return new JsonModel(array('success' => false,'message'=>'Sorry! This client source account doesnot exist.'));


		// If can delete client , the user can also delete the client account
		if(!$this->AclPlugin()->canDeleteClient($clientDetails)){
			return new JsonModel(array('success' => false,'message'=>'Sorry! You cannot delete this client source account.'));
		}

		$widgets = $widget_mapper->findByClientAccountId($client_source_id)->count();

		if($widgets){
			if(!$confirmDelete){
				return new JsonModel(array('success' => false,'message'=>"Cannot remove the source account.You have several widgets linked to this client source account."));
			}
		}

		if($widgets){
			if(!$widget_mapper->deleteByClientAccount($client_source_id)){
				  return new JsonModel(array('success' => false, 'message' => "A problem occurred while removing the linked widgets."));
			}
		}

		if($client_accounts_mapper->delete($client_source_id)){
		   $op = array('success' => true,  'message' =>" The client source account has been removed successfully.");
		} else {
		   $op = array('success' => false, 'message' => "A problem occurred while removing the client source account.");
		}

		return new JsonModel($op);
    }



    public function clientAccounts(){
		$session = new SessionContainer('Client_Auth');
		$client_accounts = array();
        $client_accounts = $session->offsetGet('client_accounts');
		$session->offsetSet('client_accounts','');

        if(!is_array($client_accounts)){
                return new JsonModel(array());
        }
		return new JsonModel($client_accounts);
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
    

    
    public function create($data) {

        $client_service   = $this->getServiceLocator()->get('jimmybase_client_service');
		
	        
        $current_user_id = $this->ZfcUserAuthentication()->getIdentity()->getId();
        
        if($this->ZfcUserAuthentication()->getIdentity()->getType()=='coworker') {
             $current_user_id = $this->getServiceLocator()
                                     ->get('jimmybase_user_mapper')
                                     ->getMeta($current_user_id,'parent');
        }
	$session = new SessionContainer('Client_Auth');

	$client['name'] = $data['name'];
        $client['parent'] = $current_user_id;        
        $client['account']['channel'] = $data['channel'];
        $client['account']['name'] = $data['account_name'];
        $client['account']['email'] = $data['email'];
	$client['account']['account_id'] = $data['account'];
       
        if($data['client_id']) {
            $client['client_id'] = $data['client_id'];
        }
	$client['account']['api_auth_info'] = serialize($session->offsetGet('access_token'));
      
        if($data['action']=='add-source') {
            $client_account = $client_service->addAccount($client);
            if($client_account) {
               
              return new JsonModel(array("success" => true,
                                         'message' => 'Client source added successfully!'));     
            } else { 
              return new JsonModel(array("success"=>true,
                                         'message' => 'A problem occurred while added client source!'));  
            }
        } else {
            $client = $client_service->save($client);	
            if($client) {
                  return new JsonModel(array("success"=>true,
                                             'message' => 'Client added successfully!',
                                             'client_id'=>$client->getId()));     
            } else { 
              return new JsonModel(array("success"=>true,
                                         'message' => 'A problem occurred while added client!'));     
            }
        }
    }


	public function getCache()
    {
        if (!$this->cache) {
            $this->setCache($this->getServiceLocator()->get('cache'));
        }
        return $this->cache;
    }

	public function setCache($cache)
    {
        $this->cache = $cache;
    }

}



