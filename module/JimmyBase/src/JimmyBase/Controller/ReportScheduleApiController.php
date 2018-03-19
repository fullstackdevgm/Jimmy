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
use Zend\View\Model\ModelInterface;
use Zend\View\Model\JsonModel;



class ReportScheduleApiController extends AbstractRestfulController
{
    protected $identifierName = 'schedule_id';

    public function getList(){
   		$report_id 			     = $this->params('report_id');
		$reportschedule_service  = $this->getServiceLocator()->get('jimmybase_reportschedule_service');
		$user_mapper     	     = $this->getServiceLocator()->get('jimmybase_user_mapper');

   		$reportschedule_list     = $reportschedule_service->getMapper()->findByReportId($report_id);

   		if($reportschedule_list->count()){
   			foreach ($reportschedule_list as $key => $reportschedule) {

   				$start_date 		= date('Y-m-d H:i',strtotime($reportschedule->getStartDate()));
   				$next_schedule_date = date('Y-m-d H:i',strtotime($reportschedule->getNextScheduleDate()));

   				$reportscheduleArray[] = array('id'=>$reportschedule->getId(),
                                               'report_id'=>$reportschedule->getReportId(),
                                               'email'=>$reportschedule->getEmail(),
                                               'frequency'=>$reportschedule->getFrequency(),
                                               'start_date'=>$start_date,
                                               'next_schedule_date'=>$next_schedule_date,
                                               'timezone'=>$reportschedule->getTimezone(),
                                               'created'=>$reportschedule->getCreated(),
                                               'updated'=>$reportschedule->getUpdated(),
                                               'from_name'=>$reportschedule->getFromName(),
                                               'from_email'=>$reportschedule->getFromEmail(),
                                               'subject'=>$reportschedule->getSubject(),
                                               'body'=>$reportschedule->getBody(),
                                               'ccme'=>$reportschedule->getCcme());
   			}

   		}

   		return new JsonModel($reportscheduleArray);
   }


   public function create($data){

    try {
      if(!$this->AclPlugin()->canUseOptions())
        throw new \Exception('Cannot schedule reports. Please upgrade your package.');
      
      $report_service     = $this->getServiceLocator()->get('jimmybase_reports_service');
      $reportschedule_service  = $this->getServiceLocator()->get('jimmybase_reportschedule_service');
      $user_service       = $this->getServiceLocator()->get('jimmybase_user_service');
      $user_mapper        = $this->getServiceLocator()->get('jimmybase_user_mapper');
          $clientService      = $this->getServiceLocator()->get('jimmybase_client_service');


      $report_id = $this->params('report_id');
      $report    = $report_service->getMapper()->findById($report_id);
      $email     = $data['email'];

      $client    = $clientService->getClientMapper()->findById($report->getUserId());

      $current_user = $this->zfcUserAuthentication()->getIdentity();

      if($current_user->getType()=='coworker'){
        $current_user_id  = $user_mapper->getMeta($current_user->getId(),'parent');
        $current_user     = $user_mapper->findById($current_user_id);
      }

      if($report && $email){

        if($data['frequency']=='send-now'){
          $reportpdf = $this->forward()->dispatch('report', array('action' => 'download','report_id'=>$report_id));

          if($reportpdf->getVariables()['success']) {
            $pdfUrl  = './data/tmp-reports/'.$report_id.'.pdf';
            $data['report_id']  = $report_id;
            if($reportschedule_service->send($data,$report,$pdfUrl)){
              $json = array('success' => true,'message'  => "Report sent successfully");
            } else {
              $json = array('success' => false,'message' => "A problem occurred while sending the report");
            }

          } else {
            $json = array('success' => false,'message' => "A problem occurred while sending the report");
          }

        } else {
          if(!$data['start_date'])
            $json = array('success' => false,'message'=>"Please select a start date");
          else if(!$data['time'])
            $json = array('success' => false,'message'=>"Please select the time");
          else if(!$data['timezone'])
            $json = array('success' => false,'message'=>"Please select the timezone");
          else {

            $reportschedule_exists = $reportschedule_service->getMapper()->scheduleExists($report->getId(),$email,$data['frequency']);

            if(!$reportschedule_exists) {
              $data['report_id']   = $report_id;
              $reportschedule      = $reportschedule_service->save($data);

              if($reportschedule){
                 $json = array('success' => true,'message'=>"Report scheduled with ".$email);
                 $this->getEventManager()->trigger('reportSchedule.success', $this, array('report' => $report,'agency'=>$current_user));
              }  else {
                 $json = array('success' => false,'message'=>"A problem occurred while scheduling report");
              }
            } else {
                 $json = array('success' => false,'message'=>"The report has already been scheduled to this user with the provided parameters!");
            }
          }
        }
      }



      return new JsonModel($json);

    } catch(\Exception $e) {
        return new JsonModel(array('success' => false,'message' => $e->getMessage()));
    }
		
   }





    public function update($schedule_id,$data){

      try {
        if(!$this->AclPlugin()->canUseOptions())
          throw new \Exception('Cannot update schedule. Please upgrade your package.');

        $report_service       = $this->getServiceLocator()->get('jimmybase_reports_service');
        $reportschedule_service  = $this->getServiceLocator()->get('jimmybase_reportschedule_service');
        $user_service         = $this->getServiceLocator()->get('jimmybase_user_service');
        $user_mapper          = $this->getServiceLocator()->get('jimmybase_user_mapper');
        $clientService        = $this->getServiceLocator()->get('jimmybase_client_service');


        $report_id = $this->params('report_id');
        $report    = $report_service->getMapper()->findById($report_id);
        $email     = $data['email'];

        $client    = $clientService->getClientMapper()->findById($report->getUserId());

        $current_user      = $this->zfcUserAuthentication()->getIdentity();

        if($current_user->getType()=='coworker'){
            $current_user_id  = $user_mapper->getMeta($current_user->getId(),'parent');
            $current_user     = $user_mapper->findById($current_user_id);
        }



        if($report && $email){

            if($data['frequency']=='send-now'){
                $reportpdf = $this->forward()->dispatch('report', array('action' => 'download','report_id'=>$report_id));

                if($reportpdf->getVariables()['success']) {
                    $pdfUrl  = './data/tmp-reports/'.$report_id.'.pdf';
                    $data['report_id']  = $report_id;
                    if($reportschedule_service->send($data,$report,$pdfUrl)){
                        $json = array('success' => true,'message'  => "Report sent successfully");
                    } else {
                        $json = array('success' => false,'message' => "A problem occurred while sending the report");
                    }

                } else {
                    $json = array('success' => false,'message' => "A problem occurred while sending the report");
                }

            } else {
                if(!$data['start_date'])
                    $json = array('success' => false,'message'=>"Please select a start date");
                else if(!$data['time'])
                    $json = array('success' => false,'message'=>"Please select the time");
                else if(!$data['timezone'])
                    $json = array('success' => false,'message'=>"Please select the timezone");
                else {

                    if($data) {

                        $reportschedule    = $reportschedule_service->save($data);

                        if($reportschedule){
                           $json = array('success' => true,'message'=>"Report scheduled updated");
                           $this->getEventManager()->trigger('reportSchedule.success', $this, array('report' => $report,'agency'=>$current_user));
                        }  else {
                           $json = array('success' => false,'message'=>"A problem occurred while scheduling report");
                        }
                    } else {
                           $json = array('success' => false,'message'=>"The report has already been scheduled to this user with the provided parameters!");
                    }
                }
            }
        }

        return new JsonModel($json);
    
      } catch(\Exception $e) {
        return new JsonModel(array('success' => false,'message' => $e->getMessage()));
      }
        
    }



   public function delete(){

		$report_service  	  = $this->getServiceLocator()->get('jimmybase_reports_service');
		$reportschedule_service  = $this->getServiceLocator()->get('jimmybase_reportschedule_service');
		$user_service    	  = $this->getServiceLocator()->get('jimmybase_user_service');
		$user_mapper     	  = $this->getServiceLocator()->get('jimmybase_user_mapper');
                $clientService 		  = $this->getServiceLocator()->get('jimmybase_client_service');

		$report_id     = $this->params('report_id');
		$schedule_id   = $this->params('schedule_id');
		$report        = $report_service->getMapper()->findById($report_id);


		if($schedule_id){

			$reportschedule = $reportschedule_service->getMapper()->findById($schedule_id);


			if($reportschedule){
			   if($reportschedule_service->getMapper()->delete($reportschedule->getId())){
			   	  $json = array("success"=>true,'message'=>'Schedule Removed');
			   } else {
			   	  $json = array("success"=>false,'message'=>'Sorry! A problem occurred while removing the schedule');
			   }
			} else
			   $json = array("success"=>false,'message'=>'Schedule Doesnot Exists');

		} else
		  $json = array("success"=>false,'message'=>'Missing Schedule Id');


	 return  new JsonModel($json);
   }


}
