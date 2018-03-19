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

class ClientApiController extends AbstractRestfulController 
{
	/**
     * @var UserService
     */
    protected $clientService;

	/**
     * @var UserService
     */
    protected $reportService;


   
    protected $identifierName = 'client_id';



	public function getList(){
           
        $service    = $this->getClientService();

		$slug = $this->getEvent()->getRouteMatch();
		$matchedRoute = $slug->getMatchedRouteName();

        $current_user_id = $this->ZfcUserAuthentication()->getIdentity()->getId();

        $opParams = $this->getRequest()->getQuery('list');

        if($this->ZfcUserAuthentication()->getIdentity()->getType()=='coworker')
            $current_user_id = $this->getServiceLocator()->get('jimmybase_user_mapper')->getMeta($current_user_id,'parent');
            
        if($opParams=='recent')
            $clientList = $service->getClientMapper()->fetchAllByAgency($current_user_id,10)->toArray();
        else    
            $clientList = $service->getClientMapper()->fetchAllByAgency($current_user_id)->toArray();

        foreach ($clientList as  &$client) {
            $client['id']      = (int)$client['client_id'];
            $client['user_id'] = (int)$client['client_id'];
            $client['reports'] = count($this->getReportService()->getMapper()->findByUserId($client['client_id']));
        }
    
     return new JsonModel($clientList);
    }


    public function get($client_id){
        $service    = $this->getClientService();
            
        $client = $service->getClientMapper()->findById($client_id);

        if(!$this->AclPlugin()->canAccessClient($client))
           return new JsonModel(array('success'=>false,'message'=>'Invalid Client!'));
        
        $client = $service->getClientMapper()->findByIdToArray($client_id);

     return new JsonModel($client);
    }
    
  
    public function create($data) {

        $client_service   = $this->getServiceLocator()->get('jimmybase_client_service');
		
	$client_accounts_mapper = $this->getServiceLocator()
                                       ->get('jimmybase_clientaccounts_mapper');
        $loggedUser = $this->ZfcUserAuthentication()->getIdentity();
        $current_user_id = $loggedUser->getId();
        
        if($this->ZfcUserAuthentication()->getIdentity()->getType()=='coworker') {
             $current_user_id = $this->getServiceLocator()
                                     ->get('jimmybase_user_mapper')
                                     ->getMeta($current_user_id,'parent');
        }
	//$session = new SessionContainer('Client_Auth');

	$client['name'] = $data['name'];
        $client['parent'] = $current_user_id;        
        $client['account']['channel'] = $data['channel'];
        $client['account']['name'] = $data['account_name'];
        $client['account']['email'] = $data['email'];
	$client['account']['account_id'] = $data['account'];
        $client['account']['user_token_id'] = $data['token_id'];
        if($data['client_id']) {
            $client['client_id'] = $data['client_id'];
        }
	//$client['account']['api_auth_info'] = serialize($session->offsetGet('access_token'));
      
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
                 $activityLogService =  $this->getServiceLocator()->get('jimmybase_activity_log_service');
             $activityLogService->addActivityLog($loggedUser,"Added client ", $data['name'], "#/clients/".$client->getId());

                  return new JsonModel(array("success"=>true,
                                             'message' => 'Client added successfully!',
                                             'client_id'=>$client->getId()));     
            } else { 
              return new JsonModel(array("success"=>true,
                                         'message' => 'A problem occurred while added client!'));     
            }
        }
    }
    
    public function delete($client_id){

        if (!$client_id) 
            return  false;

        # To check if record exists
        $clientDetails = $this->getClientService()->getClientMapper()->findById($client_id);

        
        if(!$clientDetails){
            return new JsonModel(array('success' => false,'message'=>"Client doesn't exits"));
        }

        if(!$this->AclPlugin()->canDeleteClient($clientDetails)){
            return new JsonModel(array('success' => false,'message'=>"You are not authorized to delete this client"));
        }

        $client_accounts_mapper = $this->getServiceLocator()->get('jimmybase_clientaccounts_mapper');

        $client_accounts = $client_accounts_mapper->findByClientId($client_id);
        
        if($client_accounts->count()){
            return new JsonModel(array('success' => false,'message'=>"This account contains some source accounts. Remove them first to delete this client."));
        }

    
        if($this->getClientService()->getClientMapper()->delete($clientDetails->getId())){
           // if($this->getReportService()->getMapper()->delete($clientDetails->getId(),"user_id=".$clientDetails->getId())){
             //   return new JsonModel(array('success' => true,'message'=>"Client deleted successfully"));
            //}
            return new JsonModel(array('success' => true,'message'=>"Client deleted successfully"));
        } else {
            return new JsonModel(array('success' => true,'message'=>"A problem occurred while deleting the client"));
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


   public function getReportService()
    {
        if (!$this->reportService) {
            $this->reportService = $this->getServiceLocator()->get('jimmybase_reports_service');
        }
        return $this->reportService;
    }

    public function setReportService(ReportService $reportService)
    {
        $this->reportService = $reportService;
        return $this;
    }

   

}



