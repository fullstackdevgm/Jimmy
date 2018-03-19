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


class ReportApiController extends AbstractRestfulController
{
    protected $identifierName = 'report_id';
   
    const PACKAGE_PAY_AS_YOU_GO = 14;


    public function getList(){     

            $user 	   = $this->zfcUserAuthentication();
            $user_type = $user->getIdentity()->getType();
	    $user_id   = $user->getIdentity()->getId();
	    $client_id = $this->params('client_id');
	    $agency_id = $this->params('agency_id');
            $opParams = $this->getRequest()->getQuery('list');


            $report_service  = $this->getServiceLocator()->get('jimmybase_reports_service');
            $client_service  = $this->getServiceLocator()->get('jimmybase_client_service');
            $user_mapper     = $this->getServiceLocator()->get('jimmybase_user_mapper');
        
    	if($user_type=='coworker'){
		$user_id  = $user_mapper->getMeta($user_id,'parent');
    	}

		if($user_type=='agency' or $user_type=='coworker'){

		   if($opParams=='shared'){
				$reports    = $report_service->getMapper()->fetchShared($user_id,1)->toArray();
		   } else if($opParams=='recent'){
		   		$reports = $report_service->getMapper()->findByAgencyRecent($user_id)->toArray();
		   } else {

				if($client_id){

					$client    = $client_service->getClientMapper()->findById($client_id);

					if(!$this->AclPlugin()->canAccessClient($client))
                                            return new JsonModel(array('success'=>false,'message'=>'Reports not available!'));

					$reports = $report_service->getMapper()->findByUserId($client_id)->toArray();
				} else {
					$reports = $report_service->getMapper()->findByAgency($user_id)->toArray();
				}
			}

		} else  if($user_type=='user'){
			 $reports    = $report_service->getMapper()->fetchShared($user_id,1)->toArray();
		}

		foreach ($reports as $key => $report) {
	 		$client    = $client_service->getClientMapper()->findById($report['user_id']);

			if($client)
				$agency = $user_mapper->findById($client->getParent());


			$report['shared_with_me']	  = $report_service->getMapper()->isSharedWithMe($report['id'],$user_id)->count()?true:false;
			$report['scheduled']  		  = $report_service->getMapper()->isScheduled($report['id'],$user_id)->count()?true:false;
			$report['shared']  		  = $report_service->getMapper()->isShared($report['id'])->count()?true:false;


			$report['created_by'] = $agency;
			$report['created_on'] = date('jS M, Y',strtotime($report['created']));
			$report['client']	  = array('id' => $client->getId(),'name'=> $client->getName());
			$reportsArray[] = $report;
		}

	return new JsonModel($reportsArray);

    }

    public function get($report_id){
		$user 	   = $this->zfcUserAuthentication();
	 	$user_type = $user->getIdentity()->getType();
                $user_id   = $user->getIdentity()->getId();



		$report_service  = $this->getServiceLocator()->get('jimmybase_reports_service');
		$client_service  = $this->getServiceLocator()->get('jimmybase_client_service');
		$user_mapper     = $this->getServiceLocator()->get('jimmybase_user_mapper');                
               
               
 		if($user_type=='coworker'){
			$user_id  = $user_mapper->getMeta($user_id,'parent');
                }

		$report = $report_service->getMapper()->findByIdToArray($report_id);

		$shared = $report_service->getMapper()->isSharedWithMe($report_id,$user_id);


		$report['shared_with_me'] = $shared->count()?true:false;

 		if(!$this->AclPlugin()->canViewReport($report_service->getMapper()->findById($report_id)) && !$shared->count())
			return new JsonModel(array('success'=>false,'message'=>'Report not available!'));

	 	$client = $client_service->getClientMapper()->findById($report['user_id']);

		if($client) {
                      //  $reportService = $this->getServiceLocator()->get('jimmybase_reports_service');
                               //    ->refreshTokens($client->getId()); Function no longer in use.
			$agency = $user_mapper->findById($client->getParent());
                }
		$report['user_id']    = (int)$report['user_id'];
		$report['created_by'] = $agency;
		$report['created_on'] = date('Y-m-d',strtotime($report['created']));

		$report['client']	= $client_service->getClientMapper()->findByIdToArray($report['user_id']);


	 return new JsonModel($report);
    }

    public function create($data){

		try{

                    if(!$this->AclPlugin()->canCreateReport()){
                            return new JsonModel(array("success"=>false,'message'=>'Cannot create new reports. The reports limit has been reached. Please upgrade your package.'));
                    }

                // Authed user
                $user = $this->zfcUserAuthentication()->getIdentity();

                            // Services
                            $client_service   = $this->getServiceLocator()->get('jimmybase_client_service');
                            $report_service   = $this->getServiceLocator()->get('jimmybase_reports_service');
                            $widget_service   = $this->getServiceLocator()->get('jimmybase_widget_service');
                            $metrics_service  = $this->getServiceLocator()->get('jimmybase_metrics_service');
                            $campaign_service = $this->getServiceLocator()->get('jimmybase_campaign_service');

                            $client_accounts_mapper = $this->getServiceLocator()->get('jimmybase_clientaccounts_mapper');

                            $report_mapper    = $this->getServiceLocator()->get('jimmybase_reports_mapper');
                            $braintree_service = $this->getServiceLocator()->get('jimmybase_bt_payment_service');

                            $client_id 			= $data['user_id'];
                            $client_account_id  = $data['widget']['client_account_id'];

                            $report_data['title']     = $data['title'];
                            $report_data['client_id'] = $data['user_id'];
                            $newReport = $data["new_report"];
                            $client = $client_service->getClientMapper()->findById($client_id);

                            if(!$client)
                                    return new JsonModel(array("success" => false,'message' => 'Client not found!'));
                            if (!$newReport) {
                                $client_account   	= $client_accounts_mapper->findById($client_account_id);

                                if(!$client_account)
                                        return new JsonModel(array("success" => false,'message' => 'Client Source Account not found!'));

                                if($data['widget']['channel']=='googleanalytics' && count($data['widget']['metrics'])+count($data['widget']['goals'])>10){
                                        throw new \Exception("You can select upto only 10 metrics. Selecting each goal is counted as individual metrics");
                                }
                            }

                                if($report = $report_service->save($report_data)) {
                                    if (!$newReport) { 
                                        $data['widget']['report_id']  	= $report->getId();
                                        $data['widget']['channel']  	= $data['channel'];

                                        if($widget = $widget_service->save($data['widget'])){
                                                return new JsonModel(array("success" => true,'message'=>'Report Saved Successfully','report_id' => $report->getId()));
                                        }
                                    } else {
                                        if ($this->getPackageId($user) == self::PACKAGE_PAY_AS_YOU_GO) {                                         
                                            $user_id =  $user->getId();                                      
                                            if($user->getType()=='coworker') {
                                                     $userMapper = $this->getServiceLocator()->get('jimmybase_user_mapper');
                                                     $user_id = $userMapper->getMeta($user->getId(),'parent');
                                            }

                                                    $templates = $report_mapper->findByAgency($user_id);
                                                    $braintree_service->updateSubscription($user, $templates->count());
                                        }
                                        
                                         $activityLogService =  $this->getServiceLocator()->get('jimmybase_activity_log_service');
                                         $activityLogService->addActivityLog($user,"created report",$report->getTitle(),"#/report/".$report->getId());
                                        return new JsonModel(array("success" => true,'message'=>'Report Saved Successfully','report_id' => $report->getId()));
                                    }

                                } else {
                                        return new JsonModel(array("success" => false,'message'=>'A problem occurred while creating the report'));
                                }
                        

    	} catch(\Exception $e){
	  	  return new JsonModel(array('success' => false,'message' => $e->getMessage()));
	}
    }



    public function update($id,$data){
		$report_service   = $this->getServiceLocator()->get('jimmybase_reports_service');
		$widget_service   = $this->getServiceLocator()->get('jimmybase_widget_service');
                $user = $this->zfcUserAuthentication()->getIdentity();
                $activityLogService =  $this->getServiceLocator()->get('jimmybase_activity_log_service');
                
		if(!$id)
			return new JsonModel(array("success" => false,'message'=>'Report id not found!'));

		if(!$report = $report_service->getMapper()->findById($id))
			return new JsonModel(array("success"=>false,'message'=>'Report not found!'));

		if($data['action']=='update-title'){
			if($report){
				$report->setTitle($data['title']);
				$report = $report_service->save($report);

				if($report) {
                                        $activityLogService->addActivityLog($user,"changed report title to", $report->getTitle(),"#/report/".$report->getId());
					$json = array("success"=>true,'message'=>'Report title updated','report_id'=>$report->getId());
                                } else {
                                        $json = array("success" => false,'message' => 'A problem occurred while updating report title!');
                                }
			}
		} else if($data['action'] == 'update-widget-orders'){
			if($data){

		 		if($widget_service->sortUpdate($id,$data['widget_ids'])) {
                                        $activityLogService->addActivityLog($user,"cwidget order updated for the report",$report->getTitle(),"#/report/".$report->getId());
			   		$json  = array('success' => true,  'message' => 'Widgets order updated!');
                                } else {
			   		$json  = array('success' => false, 'message' => 'An error occurred while updating the order of the widgets!');
                                }
                        }
		}

	 return new JsonModel($json);
    }



    public function delete($report_id){
		// Services
		$widget_service  = $this->getServiceLocator()->get('jimmybase_widget_service');
		$report_service  = $this->getServiceLocator()->get('jimmybase_reports_service');
                $reportschedule_service  = $this->getServiceLocator()->get('jimmybase_reportschedule_service');
                $user = $this->zfcUserAuthentication()->getIdentity();
               
                $report_mapper    = $this->getServiceLocator()->get('jimmybase_reports_mapper');
		

		// Vars
		$report    = $report_service->getMapper()->findById($report_id);
		$widgets   = $widget_service->getMapper()->findByReportId($report_id);
                $schedules = $reportschedule_service->getMapper()->findByReportId($report_id);
		if(!$report)
			return new JsonModel(array('success'=>false,'message'=>'Report doesnot exist'));

		if(!$this->AclPlugin()->canDeleteReport($report))
			return new JsonModel(array('success'=>false,'message'=>'Sorry! You cannot delete this report'));



		if($report){

			 if($widgets->count()){
			 	foreach ($widgets as $key => $widget) {
					$widget_service->getMapper()->delete($widget->getId());
			 	}
			 }
                         
                         if ($schedules->count()) {
                             foreach ($schedules as $sch) {
                                 $reportschedule_service->getMapper()->delete($sch->getId());
                             }
                         }
                         

			 if( $report_service->getMapper()->delete($report_id) ) {
                             if ($this->getPackageId($user) == self::PACKAGE_PAY_AS_YOU_GO) {                                
                                $braintree_service = $this->getServiceLocator()->get('jimmybase_bt_payment_service');
			 	$user_id =  $user->getId();                                      
                                if($user->getType()=='coworker') {
                                         $userMapper = $this->getServiceLocator()->get('jimmybase_user_mapper');
                                         $user_id = $userMapper->getMeta($user->getId(),'parent');
                                }
                                 $templates = $report_mapper->findByAgency($user_id);
                                 $braintree_service->updateSubscription($user, $templates->count());
                             }  
                                $activityLogService =  $this->getServiceLocator()->get('jimmybase_activity_log_service');
			 	$activityLogService->addActivityLog($user,"deleted report",$report->getTitle(),"");
                                $json = array('success' => true,'message'=>'Report Deleted');
			 } else {
				 $json = array('success'=>false,'error'=>'An error occurred while deleting the report.');
			 }

		}


	 return new JsonModel($json);
    }

  /**
   * To get the package id
   * 
   * @param Jimmybase\Entity\User $user
   * @return integer
   */
  private function getPackageId($user) {
   $userMapper = $this->getServiceLocator()->get('jimmybase_user_mapper');
   $packageMapper = $this->getServiceLocator()->get('jimmybase_package_mapper');

   if($user->getType()=='coworker') {
            $parent_id = $userMapper->getMeta($user->getId(),'parent');
            $user = $userMapper->findById($parent_id);
   }
   $packageId = $userMapper->getMeta($user->getId(),'package');
   
   return $packageId;
  }
      
  


}
