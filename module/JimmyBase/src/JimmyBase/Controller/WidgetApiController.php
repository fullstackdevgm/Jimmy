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
use Zend\View\Model\ModelInterface;
use JimmyBase\Entity\ClientAccounts;



class WidgetApiController extends AbstractRestfulController
{

    protected $identifierName = 'widget_id';

    public function getList(){
		$user 	   = $this->zfcUserAuthentication();
                $report_id = $this->params('report_id');

		$widget_service  = $this->getServiceLocator()->get('jimmybase_widget_service');

		$widgets = $widget_service->getMapper()->findByReportId($report_id)->toArray();


		if(is_array($widgets)){

			foreach ($widgets as $key => &$widget) {
				 	if($widget['type']=='notes')
				 		continue;
					$fields = unserialize($widget['fields']);


					switch ($widget['type']) {
						case 'kpi':
							$metric_key = 'kpi';
							# code...
							break;

						case 'table':
							$metric_key = 'raw_data';
							# code...
							break;
					    case 'piechart':
					    	$metric_key = 'raw_data';
					    	break;
						default:
							$metric_key = 'metrics';
							break;
					}

					$widget['metrics'] = $fields[$metric_key];
					unset($fields[$metric_key]);
					unset($widget['fields']);

					foreach ($fields as $k => $v) {
						$widget[$k] = $v;
					}

					$widget['channel'] = $this->getServiceLocator()
                                                                ->get('jimmybase_clientaccounts_mapper')
                                                                ->findById($widget['client_account_id'])
                                                                ->getChannel();

			}
		}


	 return new JsonModel($widgets);

    }


    public function get($widget_id){
    	session_write_close();
    	$request   = $this->getRequest();


	    $user_id   = $this->zfcUserAuthentication()->getIdentity()->getId();

	    if($user_type=='coworker') {
			$user_id  = $user_mapper->getMeta($user_id,'parent');
            }

		$widget_service  = $this->getServiceLocator()->get('jimmybase_widget_service');
		$report_service  = $this->getServiceLocator()->get('jimmybase_reports_service');

		$widget_id       = $this->params('widget_id');

		$widget = $widget_service->getMapper()->findById($widget_id);

		$shared = $report_service->getMapper()->isShared($widget->getReportId(),$user_id);


		if(!$this->AclPlugin()->canAccessWidget($widget) && !$shared->count())
			return new JsonModel(array('success'=>false,'message'=>'Your are not authorized to access this widget!'));


		try{ 

			$client_account = $this->getServiceLocator()
                                               ->get('jimmybase_clientaccounts_mapper')
					       ->findById($widget->getClientAccountId());


				if($widget->getType()!='notes'){
         
					switch ($client_account->getChannel()) {
						case ClientAccounts::GOOGLE_ADWORDS:
                                                    $return = $this->getServiceLocator()
                                                                    ->get('jimmybase_adwords_service')
                                                                    ->setRequest($request)
                                                                    ->setReportParamsService($this->AdWordsArguments())
                                                                    ->loadReport($widget,$client_account);
							break;

						case ClientAccounts::GOOGLE_ANALYTICS:
                                                    $return = $this->getServiceLocator()
                                                                   ->get('jimmybase_analytics_service')
                                                                   ->setRequest($request)
                                                                   ->setReportRenderer($rr)
                                                                   ->setReportParamsService($this->AdWordsArguments())
                                                                   ->loadReport($widget,$client_account);


							break;
						case ClientAccounts::BING_ADS:
                                                    $return = $this->getServiceLocator()
                                                                        ->get('jimmybase_bingads_service')
                                                                        ->setRequest($request)
                                                                        ->loadReport($widget,$client_account);
							break;
					}
				} else {


		                $notesHtml = $this->getServiceLocator()
                                                  ->get('jimmybase_reportrenderer_service')
		                                  ->setViewRenderer($this->getServiceLocator()->get('viewrenderer'))
		                                  ->renderNotes($report,$widget);
		                $return =  $notesHtml;

				}
		} catch(\ReportDownloadException $e){
			$return  = array('success'=>false,'message' =>$e->getMessage());
		} catch(\OAuth2Exception $e){
			$return  = array('success'=>false,'message' =>'The authorisation has been revoked. Please re-authorise to view the report.'.$e->getMessage());
		} catch(\Exception $e){
			$return  = array('success'=>false,'message' =>$e->getMessage());
		}



	 return new JsonModel($return);
    }

    public function create($data){
		try{
			// Services
			$report_service   = $this->getServiceLocator()->get('jimmybase_reports_service');
			$widget_service   = $this->getServiceLocator()->get('jimmybase_widget_service');                        
			$client_accounts_mapper = $this->getServiceLocator()->get('jimmybase_clientaccounts_mapper');

			if(!$data['report_id'])
				return new JsonModel(array("success" => false,'message'=>'Report id not found!'));

			if(!$report = $report_service->getMapper()->findById($data['report_id']))
			 	return new JsonModel(array("success" => false,'message'=>'Report not found!'));

			$client_account   	= $client_accounts_mapper->findById($data['client_account_id']);

			if(!$client_account && !$data['type']=='notes')
				return new JsonModel(array("success" => false,'message'=>'Client Source Account not found!'));


			$data['report_id']  = $report->getId();

			if($data['type']!='notes')
			    $data['channel']    = $client_account->getChannel();
		
			$widget = $widget_service->save($data);
                        //Adding to activity log.
                        $user = $this->zfcUserAuthentication()->getIdentity();
                        $activityLogService =  $this->getServiceLocator()->get('jimmybase_activity_log_service');
			$activityLogService->addActivityLog($user,"added widget ". $widget->getTitle()." to the report",$report->getTitle(),"#/report/".$widget->getReportId());
                      
                        return new JsonModel(array("success"=>true,'message'=>'New Widget ( '.$widget->getTitle().' ) created! ','report_id' => $widget->getReportId()));
    	} catch(\Exception $e){
	   	  return new JsonModel(array('success'=>false, 'message' => $e->getMessage()));
	    }
    }




    public function update($id,$data){

		try{
			$widget_service   = $this->getServiceLocator()->get('jimmybase_widget_service');
			$report_service   = $this->getServiceLocator()->get('jimmybase_reports_service');


			if(!$id)
				return new JsonModel(array("success" => false,'message'=>'Widget id not found!'));


			if($widget = $widget_service->save($data)) {
                            $report = $report_service->getMapper()->findById($widget->getReportId());
                            $user = $this->zfcUserAuthentication()->getIdentity();
                            $activityLogService =  $this->getServiceLocator()->get('jimmybase_activity_log_service');
                            $activityLogService->addActivityLog($user,"updated widget ". $widget->getTitle()." in the report",$report->getTitle(),"#/report/".$widget->getReportId());
                            return new JsonModel(array("success"=>true,'message'=>"Widget updated!"));
                        } else {
                            return new JsonModel(array("success"=>true,'message'=>"Widget could not be updated!"));
                        }
		} catch(\Exception $e){
	   	  return new JsonModel(array('success'=>false, 'message' => $e->getMessage()));
	    }
    }




    public function delete($widget_id){

		// Services
		$widget_service  = $this->getServiceLocator()->get('jimmybase_widget_service');

		$widget    = $widget_service->getMapper()->findById($widget_id);

		if(!$widget)
			return new JsonModel(array('success'=>false,'message'=>'Widget doesnot exist'));

		if(!$this->AclPlugin()->canDeleteWidget($widget))
			return new JsonModel(array('success'=>false,'message'=>'Sorry! You cannot delete this widget'));


		if($widget){
				$report_id = $widget->getReportId();

			 if( $widget_service->getMapper()->delete($widget_id) ) {
                                $report_service   = $this->getServiceLocator()->get('jimmybase_reports_service');
                                $report = $report_service->getMapper()->findById($report_id);
                                $user = $this->zfcUserAuthentication()->getIdentity();
                                $activityLogService =  $this->getServiceLocator()->get('jimmybase_activity_log_service');
                                $activityLogService->addActivityLog($user,"deleted widget ". $widget->getTitle()." from the report",$report->getTitle(),"#/report/".$widget->getReportId());
                             
			 	 $json = array('success' => true,'message'=>'Widget Deleted');
                         }
			 else
				 $json = array('success'=>false,'error'=>'An error occurred while deleting the widget.');

		}


	 return new JsonModel($json);
    }

  }
