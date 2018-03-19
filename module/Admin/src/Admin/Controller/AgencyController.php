<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\ModelInterface;
use Zend\Validator\InArray;
use Zend\Form\Form;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Stdlib\Parameters;

use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Session\Controller as SessionContainer;
use Zend\Cache\StorageFactory;

use Zend\View\Renderer\PhpRenderer;


use JimmyBase\Service\Client as ClientService;
use JimmyBase\Form as JimmyBaseForm;
use JimmyBase\Validator as JimmyBaseValidator;


class AgencyController extends AbstractActionController
{
	/**
     * @var UserService
     */
    protected $agencyService;

	/**
     * @var UserService
     */
    protected $reportService;

   
    /**
     * @var Form
     */
    protected $agencyRegisterForm;
 
   /**
     * @var Form
     */
    protected $reportForm;
 
 
    /**
     * @var UserControllerOptionsInterface
     */
    protected $options;

    /**
     * @var UserControllerOptionsInterface
     */
    protected $cache;
  
  
    public function indexAction(){
	   $agencyList = $this->getAgencyService()->getMapper()->fetchAll();
	   $view       = new ViewModel(array('agencies' => $agencyList,'user_mapper' => $this->getServiceLocator()->get('jimmybase_user_mapper')));
	 return $view;
	}
	
	public function addAction(){
        $request 		= $this->getRequest();
		$service 		= $this->getAgencyService();
		$form    		= $this->getAgencyRegisterForm();
		
		if($request->isPost()){	
		
			 $agencyInfo     = $request->getPost()->toArray();
			 $agencyInfo     = array( 
								   'email' 					=> $request->getPost('email'), 
								   'name'   				=> $request->getPost('name'),
								   'password' 				=> 'password', 
								   'state'					=> 1,
								   'type'					=> 'agency',
								   'logo'					=> $this->params()->fromFiles('logo')
								 );
								  
			 $form->setInputFilter(new JimmyBaseForm\RegisterFilter(
						 new JimmyBaseValidator\NoRecordExists(array(
                            'mapper' => $this->getServiceLocator()->get('jimmybase_agency_mapper'),
                            'key'    => 'email'
                        )),null
						
             ));
						   
		    
			$agency   = $service->save($agencyInfo);
			
			if($agency){
				$this->flashMessenger()->setNamespace('success')->addMessage('An Agency has been added!');
		   	 	return $this->redirect()->toUrl($this->url()->fromRoute('admin/agency'));
		    }
		   	
			$this->flashMessenger()->setNamespace('error')->addMessage('Sorry ! Something went wrong when creating agency!');

		}
		
		
		return array(
			'registerForm' => $form
		);	
    }
	
	
	public function saveAction(){
			
		$request   = $this->getRequest();
		$response  = $this->getResponse();
		$viewmodel = new ViewModel();
     
		$service = $this->getAgencyService();
		
		$agency  = $service->getMapper()->findById($request->getPost('id'));
		
		//print_r($agency);

		if($request->isPost()){			
			$userInfo     = array( 'id' 					=> $request->getPost('id'), 
								   'email' 					=> $request->getPost('email'), 
								   'name'   				=> $request->getPost('name'),
								   'state'					=> $request->getPost('state'),
								   'type'					=> $agency->getType(),
								   'password'				=> $agency->getPassword(),
								   'logo'					=> $this->params()->fromFiles('logo')
								  );
		    $agency   = $service->save($userInfo);
		}
		
		

		if($agency){
		   $viewmodel->setTerminal(true)
					 ->setTemplate('agency-account')
					 ->setVariables(array(
							  'agency' 			=> $service->getMapper()->findById($request->getPost('id')),							  							  							  							  'user_mapper'		=> $this->getServiceLocator()->get('jimmybase_user_mapper')

					  ));
					  
  	       $htmlOutput = $this->getServiceLocator()
                     		  ->get('viewrenderer')
                              ->render($viewmodel);
							  
			$response->setContent(\Zend\Json\Json::encode(array('success'=>true,'html'=>$htmlOutput)));
		} else {
			$response->setContent(\Zend\Json\Json::encode(array('success'=>false)));
		}
		
	    return $response;

		return array(
					'registerForm' => $this->getClientRegisterForm(),
					'redirect'     => $redirect,
					'client'	   => $clientDetails
		);	
	   
	}	
	
	
	public function viewAction(){
		$basePath = $this->getRequest()->getBaseUrl();

		$this->getServiceLocator()->get('viewhelpermanager')->get('HeadScript')
															->appendFile( $basePath.'/js/jQuery/jquery-1.9.1.min.js')
															->appendFile($basePath.'/js/ajaxfileupload.js')
															->appendFile($basePath.'/js/jquery.dataTables.js')
															->offsetSetFile(100, $basePath.'/js/application.js');

		$request 		= $this->getRequest();
		$agency_service = $this->getAgencyService();
		$form    		= $this->getAgencyRegisterForm();
		
			
		$id = (int) $this->params()->fromRoute('id', 0);
		
		if (!$id) 
			return $this->redirect()->toRoute('admin/client');
	
		$agencyDetails = $agency_service->getMapper()->findById($id);
		$clientList    = $this->getClientService()->getClientMapper()->fetchAllByAgency($id);
		$metrics_service = $this->getServiceLocator()->get('jimmybase_metrics_service');
	    
	
		$viewModel = new ViewModel(array(
			'agency'	  	  => $agencyDetails,		
			'clients'  	   	  => $clientList,		
			'registerForm' 	  => $form,
			'metrics_service' => $metrics_service,
			'user_mapper' 	  => $this->getServiceLocator()->get('jimmybase_user_mapper')
		));
		
		
		return  $viewModel;
	}	


    public function deleteAction(){
		$id = (int) $this->params()->fromRoute('id', 0);

		if (!$id) 
			return $this->redirect()->toRoute('admin/client');
	
		# To check if record exists
		$clientDetails = $this->getClientService()->getClientMapper()->findById($id);
		$reportsList   = $this->getReportService()->getReportMapper()->findByClientId($id);
		
		
		if($this->getClientService()->getClientMapper()->delete($clientDetails->getId())){
			if($this->getReportService()->getReportMapper()->delete($clientDetails->getId())){
				
				$this->flashMessenger()->setNamespace('change-password')->addMessage(true);
				return $this->redirect()->toRoute('admin/client');
			}
		}
		
		$this->flashMessenger()->setNamespace('change-password')->addMessage(true);
		return $this->redirect()->toRoute('admin/client');
	}
	
	
	public function changePwdAction(){
		$request   = $this->getRequest();
		$response  = $this->getResponse();
		$viewmodel = new ViewModel();
     
		$service = $this->getAgencyService();
		
		$agency = $service->getMapper()->findById($request->getPost('id'));

		if($request->isPost()){			
			$agencyInfo   = $request->getPost()->toArray();
			$userInfo     = array( 'id'=>$agency->getId(), 'password' => $request->getPost('password'));
			 				  
		    $agency   = $service->changePassword($userInfo);
		
			if($agency){
				$response->setContent(\Zend\Json\Json::encode(array('success'=>true)));
			} else {
				$response->setContent(\Zend\Json\Json::encode(array('success'=>false)));
			}
		}
		
	    return $response;

	}
	
	
	
	public function editReportAction(){
		 $request = $this->getRequest();		
		 $form    = $this->getReportForm();

		 $report_service = $this->getReportService();
		 $report_id = (int) $this->params('id');
		 $report	   = $report_service->getReportMapper()->findById($report_id);

		 $form->get('id')->setValue($report->getId());
		 $form->get('client_id')->setValue($report->getClientId());
		 $form->get('title')->setValue($report->getTitle());
	 	 $form->get('report_type')->setValue($report->getReportType());
	     $form->get('campaigns')->setValue(@explode(',',$report->getCampaigns()));
	     $form->get('metrics_type')->setValue($report->getMetricsType());
	     $form->get('metrics')->setValue(2);
	     $form->get('date_range')->setValue($report->getDateRange());
	     $form->get('kpi')->setValue(@explode(',',$report->getKpi()));
		 
		 if($report->getCompare())
	        $form->get('compare')->setAttribute('checked',1);
		 
		 if($report->getRawData()){
	        $form->get('show_raw_data')->setAttribute('checked',1);
		    $form->get('raw_data')->setValue(@explode(',',$report->getRawData()));
		 }
		 
		 $form->get('notes')->setValue($report->getNotes());
	
		
		if($request->isPost()){
			$data = $request->getPost()->toArray();
			
			$metrics_type 		  = (int)$data['metrics_type'];
			$metrics_type_compare = (int)$data['metrics_type_compare'];
			
			
			$metrics_value_options 		   = $this->getServiceLocator()->get('jimmybase_metrics_service')->getMetricsOptions($metrics_type);
			$metrics_compare_value_options = $this->getServiceLocator()->get('jimmybase_metrics_service')->getMetricsOptions($metrics_type_compare);
					
			$form->get('metrics')->setValueOptions($metrics_value_options);
			$form->get('metrics_compare')->setValueOptions($metrics_compare_value_options);
			
			
			if(!$client_id)
		        $client_id =  $request->getPost('client_id');
 		
		   $report = $report_service->save($request->getPost()->toArray());
		   
		   if($report)
		   	  return $this->redirect()->toUrl($this->url()->fromRoute('admin/client/view/'.$client_id));
		}
		
		
		$metrics_options = $this->getServiceLocator()->get('jimmybase_metrics_service')->getMetricsOptionsAll();
		
		$viewModel = new ViewModel( array(
			'reportForm'      => $form,
			'report'		  => $report,
			'client'	   	  => $this->getClientService()->getClientMapper()->findById($report->getClientId()),
			'metrics_options' => json_encode($metrics_options) 
		));
		
		$viewModel->setTemplate('create-report');

		return  $viewModel;
	}
	
	
	
	
	
	
	public function fetchCampaignsAction(){
  	  $request   = $this->getRequest();
  	  $response  = $this->getResponse();
	  $viewmodel = new ViewModel();
	  
      
	  $client_service = $this->getClientService();
	  $form           = $this->getReportForm();

	  # disable layout if request by Ajax
      $viewmodel->setTerminal($request->isXmlHttpRequest());
     
	  $client_id = (int) $this->params()->fromPost('client_id');
	  $type 	 = $this->params()->fromPost('show');
	  
	  if(!$client_id) return false;
	  
	  $client = $client_service->getClientMapper()->findById($client_id);
	  if(!$client->getAdwordsClientId())
        return false;
		
	  $campaigns = $this->getServiceLocator()->get('jimmybase_campaign_service')->setClientId($client->getAdwordsClientId())->getCampaigns($type);

	  $form->get('campaigns')->setValueOptions($campaigns);
	  
	  if($campaigns)
		$response->setContent(\Zend\Json\Json::encode(array('success'=>true,'data'=>$campaigns)));
	  else
	    $response->setContent(\Zend\Json\Json::encode(array('success'=>false)));
	  	
	  	
	  return $response;
	}
	
	public function viewReportAction(){
		$basePath = $this->getRequest()->getBaseUrl();

		$this->getServiceLocator()->get('viewhelpermanager')->get('HeadScript')
															->appendFile( $basePath.'/js/jQuery/jquery-1.9.1.min.js')
															->appendFile( $basePath.'/js/highcharts/highcharts.js')
															->appendFile( $basePath.'/js/highcharts/themes/green.js')
															->offsetSetFile(100, $basePath.'/js/application.js');
															  

		$request = $this->getRequest();
		$form    = $this->getClientRegisterForm();
		
		$client_service = $this->getClientService();
		$report_service = $this->getReportService();
		
			
		$id = (int) $this->params()->fromRoute('id', 0);
		
		if (!$id) 
			return $this->redirect()->toRoute('admin/client');
	
		$report	   = $report_service->getReportMapper()->findById($id);
		$client_id = $report->getClientId();
		
		
		
		if(!$client_id) 
			throw new \Exception('ClientId not provided');
		
		$client = $this->getClientService()
					   ->getClientMapper()
					   ->findById($client_id);
			
		$postParams = null;
					   
		if($request->isPost()){
			$postParams = $request->getPost()->toArray();			
		}
		
		$args = $this->AdWordsArguments()->prepareParams($report,$postParams);
	
		$viewModel = new ViewModel(array(
			'postParams'	  => $postParams,
			'args'	      	  => $args,		
			'report'	      => $report,	
			'client'		  => $client,	
			'metrics_service' => $this->getServiceLocator()->get('admin_metrics_service')
		));
		
		
		return  $viewModel;
	}
	
   public function getAgencyRegisterForm()
   {
        if (!$this->agencyRegisterForm) {
            $this->setAgencyRegisterForm($this->getServiceLocator()->get('jimmybase_agency_form'));
        }
        return $this->agencyRegisterForm;
   }
	
   public function setAgencyRegisterForm(Form $agencyRegisterForm)
    {
        $this->agencyRegisterForm = $agencyRegisterForm;
    }
	
	
   public function getClientService()
    {
        if (!$this->clientService) {
            $this->clientService = $this->getServiceLocator()->get('jimmybase_client_service');
        }
        return $this->clientService;
    }

    public function setClientService(UserService $clientService)
    {
        $this->clientService = $clientService;
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
	
	
	public function setCache($cache)
    {
        $this->cache = $cache;
    }

}



