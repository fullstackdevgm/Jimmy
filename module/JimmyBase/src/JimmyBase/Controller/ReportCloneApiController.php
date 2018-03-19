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
use Zend\View\Model\ViewModel;
use Zend\View\Model\ModelInterface;
use Zend\Session\Container as SessionContainer;

use Zend\View\Model\JsonModel;



class ReportCloneApiController extends AbstractRestfulController
{
    const PACKAGE_PAY_AS_YOU_GO = 14;    
	
    public function create($data){
		try{
                        
			if(!$this->AclPlugin()->canCreateReport()) {                           
                            return new JsonModel(array("success"=>false,'message'=>'Cannot clone the report. The reports limit has been reached. Please upgrade your package.'));		
                        }
                       
                        $user = $user 	   = $this->zfcUserAuthentication()->getIdentity();	

		 	$report_service  = $this->getServiceLocator()->get('jimmybase_reports_service');
			$user_service    = $this->getServiceLocator()->get('jimmybase_user_service');
			$user_mapper     = $this->getServiceLocator()->get('jimmybase_user_mapper');

			$report_mapper    = $this->getServiceLocator()->get('jimmybase_reports_mapper');
			$braintree_service = $this->getServiceLocator()->get('jimmybase_bt_payment_service');
			
			$report_id 		 = $data['id'];
			$report   	     = $report_service->getMapper()->findById($report_id);
			$report_name     = $data['title'];
			$report_old_name = $report->getTitle();
                        
                        if($user->getType()=='coworker') {
                            $parent_id = $user_mapper->getMeta($user->getId(),'parent');
                            $user = $user_mapper->findById($parent_id);
                        }
                        $packageId = $user_mapper->getMeta($user->getId(),'package');
			
                        if(!$this->AclPlugin()->canUseOptions($report))                            
			 	return new JsonModel(array('success'=>false,'message'=>'Cannot clone the report. The reports limit has been reached. Please upgrade your package.'));

			$old_report_title = $report->getTitle();

			if($report && $report_name) {
					
				 $report_copy = array('user_id'=>$report->getUserId(),'title'=>$report_name);
				 $report  = $report_service->copy($report->setTitle($report_name));		 
				 
				 if($report->getId()){
					 $widget_service  = $this->getServiceLocator()->get('jimmybase_widget_service');
					 $widgets         = $widget_service->getMapper()->findByReportId($report_id);
						
					 if($widgets){
						 
						foreach($widgets as $widget){
							$widget->setReportId($report->getId());
							$widget_service->copy($widget);
						}
						

					}

				 }
			}
                        $templates = $report_mapper->findByAgency($user->getId());
                        if ($packageId == self::PACKAGE_PAY_AS_YOU_GO) {
                            $braintree_service->updateSubscription($user, $templates->count());
                        } 
                        
                        
                        return  new JsonModel(array('success'=>true,'message'=>$old_report_title.'  cloned  to '.$report->getTitle()));
	   } catch(\Exception $e){
	   	 return new JsonModel(array('success'=>false,'message'=>$e->getMessage()));
	   }
	  
   }
	
}
