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
use Zend\View\Model\ViewModel;
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
use Zend\View\Model\JsonModel;

use JimmyBase\Service\Client as ClientService;
use JimmyBase\Form as JimmyBaseForm;
use JimmyBase\Validator as JimmyBaseValidator;
use JimmyBase\Entity\ClientAccounts;

class ClientController extends AbstractActionController //implements EventManagerAwareInterface
{
	
    protected $clientService;

	
    protected $clientApiService;

	
    protected $reportService;
   

    protected $cache;



    public function uploadLogoAction(){
 		$request 	= $this->getRequest();
		$service 	= $this->getClientService();
		$client     = $request->getPost()->toArray();
		$response   = $this->getResponse();


		if($request->isPost()){			
			if($service->upload($client,$this->params()->fromFiles('file'))){
				$response->setContent(\Zend\Json\Json::encode(array('success'=>true)));
			} else  {
				$response->setContent(\Zend\Json\Json::encode(array('success'=>false)));
			}
		} else {
			$response->setContent(\Zend\Json\Json::encode(array('success'=>false)));
		}

		return $response;
    }
    /**
     * Get all the clients under the selected Client.
     * 
     * @return JsonModel
     */
    public function getClientsAction() 
    {   
        $content = $this->getRequest()->getContent();
        $contentArr = \Zend\Json\Json::decode($content);     
        $userToken = $this->getUserTokenMapper();
        $source = $userToken->findById($contentArr->sourceId);
        
        $token = $source->getToken();
        $channel = $source->getChannel();
        $parent = $source->getParentId();
        
        $clientAcc = $this->getClientApiService()
                          ->setChannel($channel)
                          ->setApiAccessToken(unserialize($token))
                          ->fetchClientsAccounts($parent);     
        
       if($clientAcc) {
           foreach($clientAcc as $ca) {
              if ($ca['name'] == '' ) {
                   $ca['name'] = "NO NAME";
              }
              if ($ca["customerId"]) {
               $accounts[] = array(                   
                                    "name" => $ca['name'],
                                    "id" => $ca["customerId"]
                                  );
              } else if ($ca["id"]) {
                   $accounts[] = array(                   
                                    "name" => $ca['name'],
                                    "id" => $ca["id"]
                                  );
              }
           }
           $return = array("success" => true, "accounts" => $accounts, "message" =>"");
       } else {
           $return = array("success" => false, "message"=>"No clients Found");
       }
        
        return new JsonModel($return);
    }
    
   /**
    * Get unmapped clients.
    * 
    * @return  JsonModel
    * 
    */
   public function getUnmappedClientsAction() {
       $currentUserId = $this->ZfcUserAuthentication()->getIdentity()->getId();        
        if($this->ZfcUserAuthentication()->getIdentity()->getType()=='coworker') {
             $currentUserId = $this->getServiceLocator()
                                     ->get('jimmybase_user_mapper')
                                     ->getMeta($currentUserId,'parent');
        }
        
        $clientService = $this->getClientService();
        $clients = $clientService->getUnmappedClients($currentUserId);
        if ($clients) {
             return new JsonModel (array("success"=>true, "clients"=>$clients));            
        } else {
            return new JsonModel (array("success"=>false, "clients"=>null));
        }
   }
   
   /**
    * Check for all the agencies that need a migration.
    * 
    * @return JsonModel
    */
   public function checkMigrationStatusAction() {
        $currentUserId = $this->ZfcUserAuthentication()->getIdentity()->getId();        
        if($this->ZfcUserAuthentication()->getIdentity()->getType()=='coworker') {
             $currentUserId = $this->getServiceLocator()
                                     ->get('jimmybase_user_mapper')
                                     ->getMeta($currentUserId,'parent');
        }
        $clientService = $this->getClientService();
        $clients = $clientService->getUnmappedClients($currentUserId);
        $migrationMapper = $this->getServiceLocator()
                                ->get('jimmybase_migration_mapper');
        $userMigration = $migrationMapper->findByUserId($currentUserId);
        if ($clients && !$userMigration) {
            return new JsonModel(array("success" => true, "required" => true));
        } else {
            return new JsonModel(array("success" => true, "required" => false));
        }       
        
   }
   
   public function migrationDoneAction() {
        $currentUserId = $this->ZfcUserAuthentication()->getIdentity()->getId();        
        if ($this->ZfcUserAuthentication()->getIdentity()->getType()=='coworker') {
             $currentUserId = $this->getServiceLocator()
                                     ->get('jimmybase_user_mapper')
                                     ->getMeta($currentUserId,'parent');
        }
        $userMigrationMapper = $this->getServiceLocator()
                                ->get('jimmybase_migration_mapper');
        $migrationObj = $userMigrationMapper->findByUserId($currentUserId);
        if (!$migrationObj) {
            $migrationObj = new \JimmyBase\Entity\Migration();
            $migrationObj->setUserId($currentUserId);
            $migrationObj->setCreated(date("Y-m-d H:i:s"));
            $userMigrationMapper->insert($migrationObj);
            
        }
       
        return new JsonModel( array( "success" => true, "message" => "Migration Done"));
        
   }
   
   public function getUserTokenMapper() {
       return $this->getServiceLocator()->get('jimmybase_usertoken_mapper');
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

    public function getClientApiService()
    {
        if (!$this->clientApiService) {
            $this->clientApiService = $this->getServiceLocator()->get('jimmybase_clientapi_service');
        }
        return $this->clientApiService;
    }

    public function setClientApiService(ClientService $clientApiService)
    {
        $this->clientApiService = $clientApiService;
        return $this;
    }
	
	 public function getAgencyService()
    {
        if (!$this->agencyService) {
            $this->agencyService = $this->getServiceLocator()->get('jimmybase_agency_service');
        }
        return $this->agencyService;
    }

    public function setAgencyService(AgencyService $agencyService)
    {
        $this->agencyService = $agencyService;
        return $this;
    }
	
	
	public function getUserService()
    {
        if (!$this->userService) {
            $this->userService = $this->getServiceLocator()->get('zfcuser_user_service');
        }
        return $this->userService;
    }

    public function setUserService(UserService $userService)
    {
        $this->userService = $userService;
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



