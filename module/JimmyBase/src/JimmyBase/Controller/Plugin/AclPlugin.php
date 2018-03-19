<?php
namespace JimmyBase\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class AclPlugin extends AbstractPlugin
{

    const PACKAGE_TRIAL = 5;
    const PACKAGE_NEW_TRIAL = 15;
    const PACKAGE_14_DAY_TRIAL = 16;
    const DAYS_14_DAY_TRIAL = 14;
    const PACKAGE_WMC = 8;
    
    protected $sesscontainer ;

    private function getSessContainer()
    {
        if (!$this->sesscontainer) {
            $this->sesscontainer = new SessionContainer('Zend_Auth');
        }
        return $this->sesscontainer;
    }

	public function onRender($event)
    {
    	$viewModel = $event->getViewModel();
    	$viewModel->setVariables(array(
    			'report_limit' => 'VALUE'
    	));
    }

    public function doAuthorization($e)
    {

       	$eventManager        = $e->getApplication()->getEventManager();

    	$app          = $e->getApplication();
    	$routeMatch   = $e->getRouteMatch();

		$viewModel    = $app->getMvcEvent()->getViewModel();
    	$sm 	      = $app->getServiceManager();
    	$auth 		  = $sm->get('zfcuser_auth_service');
    	$current_user = $auth->getIdentity();

    	if($auth->hasIdentity()){
    		if($auth->getIdentity()->getType()=='agency'){
    			$package = $sm->get('jimmybase_user_mapper')->getMeta($current_user->getId(),'package');
    			$package = $sm->get('jimmybase_package_mapper')->findById($package);


    			$templates = $sm->get('jimmybase_reports_mapper')
                                ->findByAgency($current_user->getId());

				$viewModel->report_limit = false;

				# If package exists
				if($package){
					if(!($templates->count() < $package->getTemplatesAllowed()) && !is_null($package->getTemplatesAllowed())) {
						$viewModel->report_limit = true;
					}
				}

    		}

       	}
    }

    public function canDeleteClient($client){
        $sm           = $this->getController()->getServiceLocator();
        $auth         = $sm->get('zfcuser_auth_service');
        $current_user = $auth->getIdentity();

        if($current_user->getId()!=$client->getParent())
            return false;
        else
            return true;

    }

    public function canAccessClient($client){
        $sm           = $this->getController()->getServiceLocator();
        $auth         = $sm->get('zfcuser_auth_service');
        $current_user = $auth->getIdentity();
        $user_mapper  = $sm->get('jimmybase_user_mapper');
        $user_id      = $current_user->getId();

        if($current_user->getType()=='coworker'){
            $user_id  = $user_mapper->getMeta($user_id,'parent');
        }

        if(!$client)
            return false;

        if($user_id!=$client->getParent())
            return false;
        else
            return true;

    }




    public function canCreateReport(){
        $sm           = $this->getController()->getServiceLocator();
        $auth         = $sm->get('zfcuser_auth_service');
        $current_user = $auth->getIdentity();
       
        if($auth->hasIdentity()){
           
            if($auth->getIdentity()->getType()=='agency' or 
                    $auth->getIdentity()->getType()== 'coworker' ) {
                
                if ($auth->getIdentity()->getType()=='coworker') {
                  $parent = $sm->get('jimmybase_user_mapper')
                                ->getMeta($current_user->getId(),'parent');
                  $package =  $sm->get('jimmybase_user_mapper')
                              ->getMeta($parent,'package');
                } else {
                    $parent = $current_user;
                    $package = $sm->get('jimmybase_user_mapper')
                              ->getMeta($current_user->getId(),'package');
              
                }
                $package = $sm->get('jimmybase_package_mapper')
                              ->findById($package);
               
                $templates = $sm->get('jimmybase_reports_mapper')
                                ->findByAgency($current_user->getId());

                $reportCount = $sm->get('jimmybase_reports_mapper')
                                ->getCount($current_user->getId());


                # If package exists
                if($package) {                    
                    if(in_array($package->getId(), array(self::PACKAGE_TRIAL, self::PACKAGE_NEW_TRIAL, self::PACKAGE_WMC))) {
                        
                        if(!($templates->count() < $package->getTemplatesAllowed())) {
                            return false;
                        } else {
                            return true;
                        }
                    } else {
                            if($package->getId() == self::PACKAGE_14_DAY_TRIAL) {
                                return !$this->hasTrialExpired();
                            } else {
                                return true;
                            }
                       
                        # Check if credit card is added by the user.                          
                            $subscription = $sm->get('jimmybase_braintree_payment_mapper')
                                ->findByUser($parent->getId());

                            if(!$subscription) {
                                return false;
                            } else {
                                if($sm->get('jimmybase_bt_payment_service')
                                    ->checkSubscriptionStatus($current_user)) {
                                    return true;    
                                } else {
                                    return false;
                                }
                            }
//                        if($package->getTemplatesAllowed()==null) {                           
//                            if($package->getId() == self::PACKAGE_14_DAY_TRIAL) {
//                                return !$this->hasTrialExpired();
//                            } else {
//                                return true;
//                            }
//                        } else if(!($templates->count() < $package->getTemplatesAllowed())) {
//                            # Check if credit card is added by the user.                          
//                            $subscription = $sm->get('jimmybase_braintree_payment_mapper')
//                                ->findByUser($current_user->getId());
//
//                            if(!$subscription) {
//                                return false;
//                            } else {
//                                if($sm->get('jimmybase_bt_payment_service')
//                                    ->checkSubscriptionStatus($current_user)) {
//                                    return true;    
//                                } else {
//                                    return false;
//                                }
//                            }
//                            } else {
//                                return true;
//                            }
                    }
                } else {
                    throw new \Exception("Package Doesn't Exists");
                }

            } else {
                throw new \Exception("User is not authorized to create reports");
            }

        }

        throw new \Exception("User is not authorized to create reports");
    }

    public function canDeleteReport($report){
        $sm           = $this->getController()->getServiceLocator();
        $auth         = $sm->get('zfcuser_auth_service');
        $current_user = $auth->getIdentity();
        $user_type    = $current_user->getType();
        $user_id      = $current_user->getId();

        $user_mapper  = $sm->get('jimmybase_user_mapper');

        if($user_type=='coworker'){
            $user_id  = $user_mapper->getMeta($user_id,'parent');
        }

        if(!$report)
            $report       = $sm->get('jimmybase_reports_mapper')
                               ->findByAgency($user_id,$report->getId());

        if(!$report)
            return false;
        else
            return true;

    }

    /**
     * Alias of canDeleteReport, implying a user can upgrade a 
     * report only if the user has the permission to delete it.
     **/
    public function canUpgradeReport($report) {
        return $this->canDeleteReport($report);
    }

    public function canViewReport($report){
        $sm           = $this->getController()->getServiceLocator();
        $auth         = $sm->get('zfcuser_auth_service');
        $current_user = $auth->getIdentity();
        $user_type    = $current_user->getType();
        $user_id      = $current_user->getId();

        $user_mapper  = $sm->get('jimmybase_user_mapper');
        
       
        if($user_type =='coworker'){
            $user_id  = $user_mapper->getMeta($user_id,'parent');
        }

        if($report)
         $report       = $sm->get('jimmybase_reports_mapper')
                           ->findByAgency($user_id,$report->getId());

        if(!$report)
            return false;
        else
            return true;

    }

     public function canAccessWidget($widget){

        if($this->hasTrialExpired()) {
            return false;
        }

        $sm           = $this->getController()->getServiceLocator();
        $auth         = $sm->get('zfcuser_auth_service');
        $current_user = $auth->getIdentity();
        $user_type    = $current_user->getType();
        $user_id      = $current_user->getId();

        $user_mapper  = $sm->get('jimmybase_user_mapper');

        if($user_type=='coworker'){
            $user_id  = $user_mapper->getMeta($user_id,'parent');
        }

        if($widget)
        $widget       = $sm->get('jimmybase_widget_mapper')
                           ->findByAgency($user_id,$widget->getId());

        if(!$widget)
            return false;
        else
            return true;


    }

    public function canDeleteWidget($widget){
        $sm           = $this->getController()->getServiceLocator();
        $auth         = $sm->get('zfcuser_auth_service');
        $current_user = $auth->getIdentity();
        $user_type    = $current_user->getType();
        $user_id      = $current_user->getId();

        $user_mapper  = $sm->get('jimmybase_user_mapper');

        if($user_type=='coworker'){
            $user_id  = $user_mapper->getMeta($user_id,'parent');
        }

        if($widget)
            $widget       = $sm->get('jimmybase_widget_mapper')
                           ->findByAgency($user_id,$widget->getId());

        if(!$widget)
            return false;
        else
            return true;


    }


    public function canUseOptions($report=null) {

        if($this->hasTrialExpired()) {
            return false;
        }

        $sm = $this->getController()->getServiceLocator();
        $auth         = $sm->get('zfcuser_auth_service');
        $current_user = $auth->getIdentity();
        $user_type    = $current_user->getType();
        $user_mapper  = $sm->get('jimmybase_user_mapper');
        $user_id      = $current_user->getId();
        if($user_type=='coworker'){
            $user_id  = $user_mapper->getMeta($user_id,'parent');
        }
        $package =  $user_mapper
                        ->getMeta($user_id,'package');
        # Check user has a package
        if(!$package) {
            throw new \Exception('Package does not exist.');
        } else {
//            $package_mapper = $sm->get('jimmybase_package_mapper');
//            $unlimited_packages = $package_mapper->fetchNonFree();           
//            # Check if user is in unlimited package
//            foreach ($unlimited_packages as $unlimited_package) {
//                if($unlimited_package['id']==$package)
//                    return true;
//            }
            return true;
        }
    }

    public function canSeeInvoice($invoice) {
        $sm = $this->getController()->getServiceLocator();
        $auth = $sm->get('zfcuser_auth_service');
        $current_user = $auth->getIdentity();
        $email = $current_user->getEmail();
        if($email == $invoice->customer->email) {
            return true;
        } else {
            return false;
        }
    }

    public function hasTrialExpired() {
        $sm = $this->getController()->getServiceLocator();
        $auth = $sm->get('zfcuser_auth_service');
        $current_user = $auth->getIdentity();
        
        //no need to check the trial for a user
        if ($current_user->getType() == 'user') {
            return true;
        }

        // if user is a coworker change to parent to find the package details
        if($current_user->getType() == 'coworker') {
            $currentUserId = $current_user->getId();
            $parentId = $sm->get('jimmybase_user_mapper')
               ->getMeta($currentUserId,'parent');

            $current_user = $sm->get('jimmybase_user_mapper')
                   ->findById($parentId);
        }

        // Find user package
        $package = $sm->get('jimmybase_user_mapper')
            ->getMeta($current_user->getId(), 'package');

        // Package does not exist is a error
        if(!$package) {
            throw new \Exception('Package does not exist.');   
        } else {
            // check if user is on 14 day trial package
            if($package == self::PACKAGE_14_DAY_TRIAL) {
                $today = new \DateTime('now');
                $createdDate = new \DateTime($current_user->getcreated());
                // check if user crossed number of days in the trial
                if($createdDate->diff($today)->d > self::DAYS_14_DAY_TRIAL) {
                    // if user crossed the number of days trial has expired
                    return true;
                } else {
                    // if user havent crossed number of trial days trial is yet to expire
                    return false;
                }
            } else {
                // if user is not on trial pack trial hasn't expired
                return false;
            }
        }
    }
}