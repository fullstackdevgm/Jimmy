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



class ReportShareApiController extends AbstractRestfulController
{
    protected $identifierName = 'sharing_id';

    public function getList(){
   		$report_id 			  = $this->params('report_id');
		$reportshare_service  = $this->getServiceLocator()->get('jimmybase_reportshare_service');
		$user_mapper     	  = $this->getServiceLocator()->get('jimmybase_user_mapper');

   		$reportshare_list = $reportshare_service->getMapper()->findByReportId($report_id);

   		if($reportshare_list->count()){
   			foreach ($reportshare_list as $key => $reportshare) {
   				$user	= $user_mapper->findById($reportshare->getUserId());
   				if(!$user or !$reportshare)
   				   continue;

   				$reportshareArray[] = array('id'=>$reportshare->getId(),'report_id'=>$reportshare->getReportId(),'user_id'=>$user->getId(),'name'=>$user->getName(),'email'=>$user->getEmail());
   			}

   		}

   		return new JsonModel($reportshareArray);
   }


   public function create($data){

   		try {
   			
   			if(!$this->AclPlugin()->canUseOptions())
   				throw new \Exception('Cannot share the report. Please upgrade your package.');
   				
			$report_service  	  = $this->getServiceLocator()->get('jimmybase_reports_service');
			$reportshare_service  = $this->getServiceLocator()->get('jimmybase_reportshare_service');
			$user_service    	  = $this->getServiceLocator()->get('jimmybase_user_service');
			$user_mapper     	  = $this->getServiceLocator()->get('jimmybase_user_mapper');
	        $clientService 		  = $this->getServiceLocator()->get('jimmybase_client_service');


			$report_id = $this->params('report_id');
			$report    = $report_service->getMapper()->findById($report_id);
			$email     = $data['email'];

			$client    = $clientService->getClientMapper()->findById($report->getUserId());

			$current_user 	   = $this->zfcUserAuthentication()->getIdentity();

			if($current_user->getType()=='coworker'){
				$current_user_id  = $user_mapper->getMeta($current_user->getId(),'parent');
				$current_user     = $user_mapper->findById($current_user_id);
			}




			if($report && $email){
					$user_mapper->setUserType('user');
					$user = $user_mapper->findByEmail($email);



				if(!$user){

					$password = $this->App()->randomPassword();


					$userInfo     = array(
										   'name'					=> '',
										   'email' 					=> $email,
										   'password' 				=> $password,
										   'state'					=> 1,
										   'type'					=> 'user',
										 );


					$user   = $user_service->save($userInfo);

					if($user){
			 			$this->getEventManager()->trigger('createUser.success', $this, array('user' => $user,'rawUserData'=> $userInfo, 'report' => $report,'agency'=>$current_user));
		            	$this->getServiceLocator()->get('jimmybase_user_service')->addToMailChimp($user,'client');
					}
					else
			 			$this->getEventManager()->trigger('createUser.failed', $this, array('userRawData'=>$userInfo));
				}

				if($user)	{

					if($user->getId() == $client->getParent()){
						return new JsonModel(array('success' => false,'message'=>"Sorry! You cannot share your report with yourself!"));
					}

					$reportshare = $reportshare_service->getMapper()->sharingExists($report->getId(),$user->getId());
				}

				if(!$reportshare) {
				 	$reportshare      = $reportshare_service->save(array('user_id'=>$user->getId(),'report_id'=>$report->getId()));

					if($reportshare){
					   $json = array('success' => true,'message'=>"Report shared with ".$email);
					   $this->getEventManager()->trigger('reportShare.success', $this, array('report' => $report,'user' => $user,'agency'=>$current_user,'new_user'=>$userInfo));
					}  else {
					   $json = array('success' => false,'message'=>"A problem occurred while sharing report");
					}
				} else {
					   $json = array('success' => false,'message'=>"The report has already been shared this user");
				}
			}

		  return new JsonModel($json);
   		} catch(\Exception $e) {
	  	  return new JsonModel(array('success' => false,'message' => $e->getMessage()));
   		}


   }

   public function deleteList(){

		$report_service  	  = $this->getServiceLocator()->get('jimmybase_reports_service');
		$reportshare_service  = $this->getServiceLocator()->get('jimmybase_reportshare_service');
		$user_service    	  = $this->getServiceLocator()->get('jimmybase_user_service');
		$user_mapper     	  = $this->getServiceLocator()->get('jimmybase_user_mapper');


		$report_id    = $this->params('report_id');
		$sharing_id   = $this->params('sharing_id');
		$report       = $report_service->getMapper()->findById($report_id);


		if($sharing_id){

			$reportshare = $reportshare_service->getMapper()->findById($sharing_id);


			if($reportshare){
			   if($reportshare_service->getMapper()->delete($reportshare->getId())){
			   	  $json = array("success"=>true,'message'=>'Sharing Removed');
			   } else {
			   	  $json = array("success"=>false,'message'=>'Sorry! A problem occurred while removing the sharing');
			   }
			} else
			   $json = array("success"=>false,'message'=>'Sharing Doesnot Exists');

		} else
		  $json = array("success"=>false,'message'=>'Missing Sharing Id');


	 return  new JsonModel($json);
   }


    public function delete($sharing_id){

		$report_service  	  = $this->getServiceLocator()->get('jimmybase_reports_service');
		$reportshare_service  = $this->getServiceLocator()->get('jimmybase_reportshare_service');
		$user_service    	  = $this->getServiceLocator()->get('jimmybase_user_service');
		$user_mapper     	  = $this->getServiceLocator()->get('jimmybase_user_mapper');

		$sharing_id   = $this->params('sharing_id');

		if($sharing_id){

			$reportshare = $reportshare_service->getMapper()->findById($sharing_id);


			if($reportshare){
			   if($reportshare_service->getMapper()->delete($reportshare->getId())){
			   	  $json = array("success"=>true,'message'=>'Sharing Removed');
			   } else {
			   	  $json = array("success"=>false,'message'=>'Sorry! A problem occurred while removing the sharing');
			   }
			} else
			   $json = array("success"=>false,'message'=>'Sharing Doesnot Exists');

		} else
		  $json = array("success"=>false,'message'=>'Missing Sharing Id');


	 return  new JsonModel($json);
   }


}
