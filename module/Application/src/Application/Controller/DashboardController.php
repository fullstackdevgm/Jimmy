<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventInterface;
use Zend\Session\Container as SessionContainer;

use JimmyBase\Entity\ClientAccounts;

class DashboardController extends AbstractActionController
{

    const PACKAGE_UNLIMITED = 13;
    const PACKAGE_PAY_AS_YOU_GO = 14;

    public function indexAction()
    {

		$viewModel    = new ViewModel();
		$current_user = $this->zfcUserAuthentication()->getIdentity();
		$user_mapper  = $this->getServiceLocator()->get('jimmybase_user_mapper');
		$widget_mapper= $this->getServiceLocator()->get('jimmybase_widget_mapper');

		if(!$this->zfcUserAuthentication()->getIdentity()){
			return $this->redirect()->toRoute('home');
		} else {
				$config       = $this->getServiceLocator()->get('config');

		        if(in_array($current_user->getType(),array('agency','coworker')) ){

						$current_user_id = $current_user->getId();

		            	if($current_user->getType()=='coworker'){
							$current_user_id  = $user_mapper->getMeta($current_user_id,'parent');
		            	}

						$user 		  = $user_mapper->findById($this->ZfcUserAuthentication()->getIdentity()->getId());
            $userPackage = $user_mapper->getMeta($user->getId(), 'package');

            $braintreePayment_service = $this->getServiceLocator()->get('jimmybase_bt_payment_service');
            $braintreePayment_mapper = $this->getServiceLocator()->get('jimmybase_braintree_payment_mapper');

            $btEntity = $braintreePayment_mapper->findByUser($user->getId());

            # Check if user is a paid user.
            if(in_array($userPackage, [self::PACKAGE_UNLIMITED, self::PACKAGE_PAY_AS_YOU_GO]) && $btEntity) {

              # Find the days after the pay day.
              $nextPaymentDateMeta =   $user_mapper->getMeta($user->getId(), 'next_payment_date');
              if ($nextPaymentDateMeta) {
                $userNextPayDate = \DateTime::createFromFormat('Y-m-d',$nextPaymentDateMeta );
                $dayDiffAfterPayDate = ($userNextPayDate->diff(new \DateTime('today'))->format('%R%a'));
              }
              # Check if the pay date is not in the future.
              if($dayDiffAfterPayDate>=0 || !$nextPaymentDateMeta ) {

                # Get subscription if pay day is not in future.
                $subscription = $braintreePayment_service->getSubscriptionStatus($user);

                # Update next billing date and current subscription status
                $nextBillingDate = $subscription->nextBillingDate->format('Y-m-d');
                $currentSubscriptionStatus = $subscription->status;
                $this->setNextPaymentDate($user, $nextBillingDate);
                $btEntity->setStatus($currentSubscriptionStatus);
                $braintreePayment_mapper->update($btEntity);
              }
            }

						$clientService    = $this->getServiceLocator()->get('jimmybase_client_service');
						$clientList 	  = $clientService->getClientMapper()->fetchAllByAgency($current_user_id);
						$package_mapper   = $this->getServiceLocator()->get('jimmybase_package_mapper');

						$current_package  = $user_mapper->getMeta($current_user_id,'package');
						$templates_used   = 0;

						if($current_package){
						   $package       = $package_mapper->findById($current_package);
						}

						$templates      = $this->getServiceLocator()->get('jimmybase_reports_mapper')->findByAgency($current_user_id);
						$templates_used = $templates->count();

						$settings = @unserialize($user_mapper->getMeta($current_user_id,'_settings'));
						$viewModel->setTemplate('application/dashboard/agency.phtml');

					    $this->layout()->setVariable('settings',$settings);
					    $this->layout()->setVariable('logo', $user_mapper->getMeta($current_user->getId(),'logo'));
					    $this->layout()->setVariable('title',$config['jimmy-config']['title']);


				} else if($current_user->getType()=='user'){//or $current_user->getType()=='admin'

				    $report_service 	  = $this->getServiceLocator()->get('jimmybase_reports_service');
					$client_service  	  = $this->getServiceLocator()->get('jimmybase_client_service');
					$agency_service  	  = $this->getServiceLocator()->get('jimmybase_agency_service');
					$reportshare_service  = $this->getServiceLocator()->get('jimmybase_reportshare_service');

					// Find all active sharing
					$reportshared_list    = $report_service->getMapper()->fetchShared($this->ZfcUserAuthentication()->getIdentity()->getId(),1);

					$report_service  = $this->getServiceLocator()->get('jimmybase_reports_service');
					$client_service  = $this->getServiceLocator()->get('jimmybase_client_service');
					$agency_service  = $this->getServiceLocator()->get('jimmybase_agency_service');
					$reportshare_service  = $this->getServiceLocator()->get('jimmybase_reportshare_service');

					$user = $user_mapper->findById($this->ZfcUserAuthentication()->getIdentity()->getId());

					$viewModel->setTemplate('application/dashboard/user.phtml')
							  ->setVariable('user', $user);


				}

				return  $viewModel;
		}
	}

  public function setNextPaymentDate($user, $date) {
      $user_service = $this->getServiceLocator()->get('jimmybase_user_service');

      $user->setKey('next_payment_date');
      $user->setValue($date);
      $user_service->saveMeta($user);

  }

  public function activityLogAction() {
     $content = $this->getRequest()->getContent();
     $postJson = \Zend\Json\Json::decode($content, \Zend\Json\Json::TYPE_OBJECT);
     $limit = $postJson->limit;
     $currentUser = $this->zfcUserAuthentication()->getIdentity();
     $activityLogService = $this->getServiceLocator()->get('jimmybase_activity_log_service');
     $logData = $activityLogService->fetchAllUserLog($currentUser,$limit);
     return new JsonModel(array("success" => true, "logData" => $logData));
  }
}
