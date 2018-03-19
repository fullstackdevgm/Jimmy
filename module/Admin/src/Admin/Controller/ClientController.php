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
use Zend\Session\Container as SessionContainer;
use Zend\Cache\StorageFactory;
use Zend\View\Renderer\PhpRenderer;

use JimmyBase\Service\Client as ClientService;
use JimmyBase\Form as JimmyBaseForm;
use JimmyBase\Validator as JimmyBaseValidator;

class ClientController extends AbstractActionController
{
	/**
     * @var UserService
     */
    protected $clientService;

	/**
     * @var UserService
     */
    protected $reportService;

   
    /**
     * @var Form
     */
    protected $clientRegisterForm;
 
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
		$service    = $this->getClientServices();
		$clientList = $service->getClientMapper()->fetchAllByAgency(0);
		
		$view = new ViewModel(array('clients'=>$clientList));
	 return $view;
	}
	
	public function addAction(){
        $request 		= $this->getRequest();
		$service 		= $this->getClientService();
		$userService    = $this->getUserService();
		$form    		= $this->getClientRegisterForm();
	     
		 
		$agency_id = (int) $this->params()->fromRoute('id', 0);

		if($agency_id)
		   $agency = $this->getAgencyService()->getMapper()->findById($agency_id);
		else 
		   $agency = $this->adminUserAuthentication()->getIdentity();
	
		if($request->isPost()){			
			$clientInfo   = $request->getPost()->toArray();
			
			$userInfo     = array( 
								   'email' 					=> $request->getPost('email'), 
								   'name'   				=> $request->getPost('name'),
								   'adwords_client_id'		=> $request->getPost('adwords_client_id'),
								   'password' 				=> 'password', 
								   'state'					=> 1,
								   'type'					=> 'client',
								   'parent'					=> $request->getPost('parent')
								 );
								  
			 $form->setInputFilter(new JimmyBaseForm\RegisterFilter(

						 new JimmyBaseValidator\NoRecordExists(array(
                            'mapper' => $this->getServiceLocator()->get('jimmybase_client_mapper'),
                            'key'    => 'email'
                        )),
						new JimmyBaseValidator\NoRecordExists(array(
                            'mapper' => $this->getServiceLocator()->get('jimmybase_client_mapper'),
                            'key'    => 'adwords_client_id'
                        ))
               ));
						   
		    
			$client   = $service->save($userInfo);
			
			if($client){
		   	 	return $this->redirect()->toUrl($this->url()->fromRoute('admin/client'));
		    }
		   
		}
		
		
		return array(
			'agency'	   => $agency,
 			'registerForm' => $form
		);	
    }
	
	
	public function saveAction(){
			
		$request   = $this->getRequest();
		$response  = $this->getResponse();
		$viewmodel = new ViewModel();
     
		$service = $this->getClientService();
		
		$client = $service->getClientMapper()->findById($request->getPost('id'));

		if($request->isPost()){			
			$clientInfo   = $request->getPost()->toArray();
			$userInfo     = array( 'id' 					=> $request->getPost('id'), 
								   'email' 					=> $request->getPost('email'), 
								   'name'   				=> $request->getPost('name'),
								   'adwords_client_id'		=> $request->getPost('adwords_client_id'),
								   'state'					=> $request->getPost('state'),
								   'type'					=> $client->getType(),
								   'password'				=> $client->getPassword(),
   								   'parent'					=> $this->adminUserAuthentication()->getIdentity()->getId()

								  );
			 				  
		    $client   = $service->save($userInfo);
		}
		

		if($client){
		   $viewmodel->setTerminal(true)
					 ->setTemplate('account')
					 ->setVariables(array(
							  'client' => $service->getClientMapper()->findById($request->getPost('id'))
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
															->offsetSetFile(100, $basePath.'/js/application.js');

		$request = $this->getRequest();
		$client_service = $this->getClientService();
		$report_service = $this->getReportService();
		$form    = $this->getClientRegisterForm();
		
			
		$id = (int) $this->params()->fromRoute('id', 0);
		
		if (!$id) 
			return $this->redirect()->toRoute('admin/client');
	
		$clientDetails = $client_service->getClientMapper()->findById($id);
		$reportsList   = $report_service->getReportMapper()->findByClientId($id);
		
		$metrics_service = $this->getServiceLocator()->get('jimmybase_metrics_service');
	    
	
		$viewModel = new ViewModel(array(
			'client'	   => $clientDetails,		
			'registerForm' => $form,
			'reportList'   => $reportsList,
			'metrics_service' => $metrics_service

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
	
	public function createReportAction(){
		
		$request = $this->getRequest();		
		$form    = $this->getReportForm();

		$report_service = $this->getReportService();
		$client_id      = (int) $this->params('id');
		
		//if(!$client_id)
		// return false;
		
		$form->get('client_id')->setValue($client_id);

		
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
		   
		   if($report) {
			 $this->flashMessenger()->setNamespace('success')->addMessage('Report Saved Successfully!');
			 
			 return $this->redirect()->toUrl($this->url()->fromRoute('admin/client/view').'/'.$client_id);
		   
		   } else {
		   	 
			 $this->flashMessenger()->setNamespace('error')->addMessage('Sorry ! An Error Occurred While Saving!');
		   
		   }
		   
		}
		
		
		$metrics_options = $this->getServiceLocator()->get('jimmybase_metrics_service')->getMetricsOptionsAll();
		
		return  array(
			'reportForm'      => $form,
			'client'	   	  => $this->getClientService()->getClientMapper()->findById($client_id),
			'metrics_options' => json_encode($metrics_options) 
		);
	}
	
	public function reportAction(){
		$request = $this->getRequest();		
		$form    = $this->getReportForm();

		$report_service = $this->getReportService();
		$client_id      = (int) $this->params('id');
		
		
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
		
		$viewModel = new ViewModel(array(
			'reportForm'      => $form,
			'client'	   	  => $this->getClientService()->getClientMapper()->findById($client_id),
			'metrics_options' => json_encode($metrics_options) 
		));
		
	
	}
	
	public function changePwdAction(){
		$request   = $this->getRequest();
		$response  = $this->getResponse();
		$viewmodel = new ViewModel();
     
		$service = $this->getClientService();
		
		$client = $service->getClientMapper()->findById($request->getPost('id'));

		if($request->isPost()){			
			$clientInfo   = $request->getPost()->toArray();
			$userInfo     = array( 'id'=>$client->getId(), 'password' => $request->getPost('password'));
			 				  
		    $client   = $service->changePassword($userInfo);
		}

		if($client){
			$response->setContent(\Zend\Json\Json::encode(array('success'=>true)));
		} else {
			$response->setContent(\Zend\Json\Json::encode(array('success'=>false)));
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
	
	public function viewReportAction1(){
		
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
		/*echo '<pre>';
		print_r($args);
		exit;*/
		$result = $this->getServiceLocator()->get('jimmybasecampaign_service')->setClientId($client->getAdwordsClientId())
																		   ->getClientCriteriaReport($args);
		//echo '<pre>';
		header('Content-Type:text/xml');
		print_r($result);exit;
		$xml = simplexml_load_string($result);
		
		
		foreach($xml->table->columns->column as $column){
			$columns[] = array('name' =>(string)$column->attributes()->name,'display'=>(string)$column->attributes()->display);
		}
		// '<pre>';
		foreach($xml->table->row as $row){
			
			foreach($columns as $column){
				  	 $d = (array)$row->attributes()->{$column['name']};
					 $rowData[$column['name']] = $d[0];
					 //$rowData[$column['name']] = (string)$row->attributes()->{$column['name']};
			}
			
			$rows[] = $rowData;
		}
		
		//exit;
		$data['columns']  		= $columns;
		$data['rows']  	  		= $rows;
		
		
		$viewModel = new ViewModel(array(
			'postParams'	  => $postParams,
			'args'	      	  => $args,		
			'report'	      => $report,		
			'adwordsData'     => $data,
			'metrics_service' => $this->getServiceLocator()->get('jimmybase_metrics_service')
		));
		
		
		
		return  $viewModel;
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
			'metrics_service' => $this->getServiceLocator()->get('jimmybase_metrics_service')
		));
		
		
		return  $viewModel;
	}
	
	
   public function graphAction(){
		$request  = $this->getRequest();
  	    $response = $this->getResponse();
		$form     = $this->getClientRegisterForm();
		
		$client_service = $this->getClientService();
		$report_service = $this->getReportService();
		$viewModel 	    = new ViewModel();
		
        $viewModel->setTerminal($request->isXmlHttpRequest());
			
		$id = (int) $this->params()->fromRoute('id', 0);
		
		if (!$id){ 
			$response->setContent(\Zend\Json\Json::encode(array('success'=>false,'error'=>'ReportId not provided')));
			return $response;
		}
		
		$report	   = $report_service->getReportMapper()->findById($id);
		$client_id = $report->getClientId();
		
		
		
		if(!$client_id) {
			throw new \Exception('ClientId not provided');
		}
		
		$client = $this->getClientService()
					   ->getClientMapper()
					   ->findById($client_id);
			
		$postParams = null;
					   
		if($request->isPost()){
			$postParams = $request->getPost()->toArray();			
		}
		
		$type = 'GRAPH';
		
		$args = $this->AdWordsArguments()->prepareParams($report,$type,$postParams);
		
		$result = $this->getServiceLocator()->get('jimmybase_campaign_service')->setClientId($client->getAdwordsClientId())
																		   ->getClientCriteriaReport($args,$type);

		$xml = simplexml_load_string($result);
		
		
		foreach($xml->table->columns->column as $column){
			$columns[] = array('name' =>(string)$column->attributes()->name,'display'=>(string)$column->attributes()->display);
		}
		
		foreach($xml->table->row as $row){
			
			foreach($columns as $column){
				  	 $d = (array)$row->attributes()->{$column['name']};
					 $rowData[$column['name']] = $d[0];
			}
			
			$rows[] = $rowData;
		}
		
		$data['columns']  		= $columns;
		$data['rows']  	  		= $rows;

		$graph = $this->ReportRenderer()
		              ->renderGraph($data,$report,$args);
		
		
		$response->setContent(\Zend\Json\Json::encode(array('success'=>true,'script'=>$graph)));
	  return $response;
	}	
	
	public function rawDataAction(){
		$request  = $this->getRequest();
  	    $response = $this->getResponse();
		$form     = $this->getClientRegisterForm();
		
		$client_service = $this->getClientService();
		$report_service = $this->getReportService();
		$viewModel 	    = new ViewModel();
		
        $viewModel->setTerminal($request->isXmlHttpRequest());
			
		$id = (int) $this->params()->fromRoute('id', 0);
		
		if (!$id){ 
			$response->setContent(\Zend\Json\Json::encode(array('success'=>false,'error'=>'ReportId not provided')));
			return $response;
		}
		
		$report	   = $report_service->getReportMapper()->findById($id);
		$client_id = $report->getClientId();
		
		
		
		if(!$client_id) {
			throw new \Exception('ClientId not provided');
		}
		
		$client = $this->getClientService()
					   ->getClientMapper()
					   ->findById($client_id);
			
		$postParams = null;
					   
		if($request->isPost()){
			$postParams = $request->getPost()->toArray();			
		}
		
		
		$type = 'RAW';
		
		$args = $this->AdWordsArguments()->prepareParams($report,$type,$postParams);
		//echo '<pre>';
		//print_r($args);
		//exit;
			
		
		$result = $this->getServiceLocator()->get('jimmybase_campaign_service')->setClientId($client->getAdwordsClientId())
																	       ->getClientCriteriaReport($args,$type);

		$xml = simplexml_load_string($result);
		
		
		foreach($xml->table->columns->column as $column){
			$columns[] = array('name' =>(string)$column->attributes()->name,'display'=>(string)$column->attributes()->display);
		}
		
		foreach($xml->table->row as $row){
			
			foreach($columns as $column){
				  	 $d = (array)$row->attributes()->{$column['name']};
					 $rowData[$column['name']] = $d[0];
			}
			
			$rows[] = $rowData;
		}
		
		$data['columns']  		= $columns;
		$data['rows']  	  		= $rows;

		$rawData = $this->ReportRenderer()
		                ->renderRawData($data,$report,$args);
		
		
		$response->setContent(\Zend\Json\Json::encode(array('success'=>true,'html'=>$rawData)));
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
            $this->reportService = $this->getServiceLocator()->get('jimmybase_report_service');
        }
        return $this->reportService;
    }

    public function setReportService(ReportService $reportService)
    {
        $this->reportService = $reportService;
        return $this;
    }

    public function getClientRegisterForm()
    {
        if (!$this->clientRegisterForm) {
            $this->setClientRegisterForm($this->getServiceLocator()->get('admin_client_register_form'));
        }
        return $this->clientRegisterForm;
    }
	
   public function setClientRegisterForm(Form $clientRegisterForm)
    {
        $this->clientRegisterForm = $clientRegisterForm;
    }
	
	
    public function getReportForm()
    {
        if (!$this->reportForm) {
            $this->setReportForm($this->getServiceLocator()->get('admin_report_form'));
        }
        return $this->reportForm;
    }
	
   public function setReportForm(Form $reportForm)
    {
        $this->reportForm = $reportForm;
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



