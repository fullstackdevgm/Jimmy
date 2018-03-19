<?php

namespace Application\Authentication\Adapter;

use Hybrid_Auth;
use ScnSocialAuth\Authentication\Adapter\HybridAuth as ScnHybridAuth;
use ScnSocialAuth\Authentication\Adapter\Exception;
use ScnSocialAuth\Mapper\UserProviderInterface;
use ScnSocialAuth\Options\ModuleOptions;
use Zend\Authentication\Result;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcUser\Authentication\Adapter\AbstractAdapter;
use ZfcUser\Authentication\Adapter\AdapterChainEvent as AuthEvent;
use ZfcUser\Mapper\UserInterface as UserMapperInterface;
use ZfcUser\Options\UserServiceOptionsInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;


class HybridAuth extends ScnHybridAuth implements ServiceManagerAwareInterface, EventManagerAwareInterface
{



    /**
     * set mapper
     *
     * @param  UserProviderInterface $mapper
     * @return HybridAuth
     */
    public function setMapper(UserProviderInterface $mapper)
    {
        $this->mapper = $mapper;

        return $this;
    }

    /**
     * get mapper
     *
     * @return UserProviderInterface
     */
    public function getMapper()
    {
        if (!$this->mapper instanceof UserProviderInterface) {
            $this->setMapper($this->getServiceLocator()->get('ScnSocialAuth-UserProviderMapper'));
        }

        return $this->mapper;
    }

    /**
     * set zfcUserMapper
     *
     * @param  UserMapperInterface $zfcUserMapper
     * @return HybridAuth
     */
    public function setZfcUserMapper(UserMapperInterface $zfcUserMapper)
    {
        $this->zfcUserMapper = $zfcUserMapper;

        return $this;
    }

    /**
     * get zfcUserMapper
     *
     * @return UserMapperInterface
     */
    public function getZfcUserMapper()
    {
        if (!$this->zfcUserMapper instanceof UserMapperInterface) {
            $this->setZfcUserMapper($this->getServiceLocator()->get('zfcuser_user_mapper'));
        }

        return $this->zfcUserMapper;
    }

    /**
     * Utility function to instantiate a fresh local user object
     *
     * @return mixed
     */
    protected function instantiateLocalUser()
    {
        $userModelClass = $this->getZfcUserOptions()->getUserEntityClass();

        return new $userModelClass;
    }

    // Provider specific methods

    protected function facebookToLocalUser($userProfile)
    {
        if (!isset($userProfile->emailVerified)) {
            throw new Exception\RuntimeException(
                'Please verify your email with Facebook before attempting login',
                Result::FAILURE_CREDENTIAL_INVALID
            );
        }
        $mapper = $this->getZfcUserMapper();
        if (false != ($localUser = $mapper->findByEmail($userProfile->emailVerified))) {
            return $localUser;
        }
        $localUser = $this->instantiateLocalUser();
        $localUser->setEmail($userProfile->emailVerified)
            ->setDisplayName($userProfile->displayName)
            ->setPassword(__FUNCTION__);
        $result = $this->insert($localUser, 'facebook', $userProfile);

        return $localUser;
    }

    protected function foursquareToLocalUser($userProfile)
    {
        if (!isset($userProfile->emailVerified)) {
            throw new Exception\RuntimeException(
                'Please verify your email with Foursquare before attempting login',
                Result::FAILURE_CREDENTIAL_INVALID
            );
        }
        $mapper = $this->getZfcUserMapper();
        if (false != ($localUser = $mapper->findByEmail($userProfile->emailVerified))) {
            return $localUser;
        }
        $localUser = $this->instantiateLocalUser();
        $localUser->setEmail($userProfile->emailVerified)
            ->setDisplayName($userProfile->displayName)
            ->setPassword(__FUNCTION__);
        $result = $this->insert($localUser, 'foursquare', $userProfile);

        return $localUser;
    }

    protected function googleToLocalUser($userProfile)
    {
        if (!isset($userProfile->emailVerified)) {
            throw new Exception\RuntimeException(
                'Please verify your email with Google before attempting login',
                Result::FAILURE_CREDENTIAL_INVALID
            );
        }


        $mapper = $this->getZfcUserMapper();
        if (false != ($localUser = $mapper->findByEmail($userProfile->emailVerified))) {
            return $localUser;
        }

        $localUser = $this->instantiateLocalUser();

		    $now  = date('Y-m-d h:i:s');

        $localUser->setEmail($userProfile->emailVerified)
            ->setDisplayName($userProfile->displayName)
            ->setName($userProfile->firstName.' '.$userProfile->lastName)
            ->setType('agency')
            ->setState(1)
			      ->setCreated($now)
         	  ->setUpdated($now)
			      ->setPassword(__FUNCTION__);

        $result = $this->insert($localUser, 'google', $userProfile);

		if($localUser){
            $hybridAuth = $this->getServiceManager()->get('HybridAuth');

            if($referrer = $hybridAuth::storage()->get('hauth_session.referrer')){
               $hybridAuth::storage()->set('hauth_session.referrer',null);
               $this->getServiceManager()->get('jimmybase_user_service')->setReferrerPackage($localUser,$referrer);
            } else {
               $this->getServiceManager()->get('jimmybase_user_service')->setDefaultPackage($localUser);
            }

           $this->getServiceManager()->get('jimmybase_user_service')->addToMailChimp($localUser);
        }


        return $localUser;
    }


    protected function liveToLocalUser($userProfile)
    {
        if (!isset($userProfile->emailVerified)) {
            throw new Exception\RuntimeException(
                'Please verify your email with Google before attempting login',
                Result::FAILURE_CREDENTIAL_INVALID
            );
        }


        $mapper = $this->getZfcUserMapper();
        if (false != ($localUser = $mapper->findByEmail($userProfile->emailVerified))) {
            return $localUser;
        }

        $localUser = $this->instantiateLocalUser();

        $now  = date('Y-m-d h:i:s');

        $localUser->setEmail($userProfile->emailVerified)
            ->setDisplayName($userProfile->displayName)
            ->setName($userProfile->firstName.' '.$userProfile->lastName)
            ->setType('agency')
            ->setState(1)
            ->setCreated($now)
            ->setUpdated($now)
            ->setPassword(__FUNCTION__);

        $result = $this->insert($localUser, 'live', $userProfile);

    if($localUser){
            $hybridAuth = $this->getServiceManager()->get('HybridAuth');

            if($referrer = $hybridAuth::storage()->get('hauth_session.referrer')){
               $hybridAuth::storage()->set('hauth_session.referrer',null);
               $this->getServiceManager()->get('jimmybase_user_service')->setReferrerPackage($localUser,$referrer);
            } else {
               $this->getServiceManager()->get('jimmybase_user_service')->setDefaultPackage($localUser);
            }

           $this->getServiceManager()->get('jimmybase_user_service')->addToMailChimp($localUser);
        }


        return $localUser;
    }


    protected function linkedInToLocalUser($userProfile)
    {
        if (!isset($userProfile->emailVerified)) {
            throw new Exception\RuntimeException(
                'Please verify your email with LinkedIn before attempting login',
                Result::FAILURE_CREDENTIAL_INVALID
            );
        }
        $mapper = $this->getZfcUserMapper();
        if (false != ($localUser = $mapper->findByEmail($userProfile->emailVerified))) {
            return $localUser;
        }
        $localUser = $this->instantiateLocalUser();
        $localUser->setDisplayName($userProfile->displayName)
            ->setEmail($userProfile->emailVerified)
            ->setPassword(__FUNCTION__);
        $result = $this->insert($localUser, 'linkedIn', $userProfile);

        return $localUser;
    }

    protected function twitterToLocalUser($userProfile)
    {
        $localUser = $this->instantiateLocalUser();
        $localUser->setUsername($userProfile->displayName)
            ->setDisplayName($userProfile->firstName)
            ->setPassword(__FUNCTION__);
        $result = $this->insert($localUser, 'twitter', $userProfile);

        return $localUser;
    }

    protected function yahooToLocalUser($userProfile)
    {
        $localUser = $this->instantiateLocalUser();
        $localUser->setDisplayName($userProfile->displayName)
            ->setPassword(__FUNCTION__);
        $result = $this->insert($localUser, 'yahoo', $userProfile);

        return $localUser;
    }

    protected function githubToLocalUser($userProfile)
    {
        $localUser = $this->instantiateLocalUser();
        $localUser->setDisplayName($userProfile->displayName)
                  ->setPassword(__FUNCTION__)
                  ->setEmail($userProfile->email);

        $this->getEventManager()->trigger(__FUNCTION__, $localUser, array('userProfile' => $userProfile));

        $result = $this->insert($localUser, 'github', $userProfile);

        return $localUser;
    }

    /**
     * persists the user in the db, and trigger a pre and post events for it
     * @param  mixed  $user
     * @param  string $provider
     * @param  mixed  $userProfile
     * @return mixed
     */
    protected function insert($user, $provider, $userProfile)
    {
        $zfcUserOptions = $this->getZfcUserOptions();

        // If user state is enabled, set the default state value
        if ($zfcUserOptions->getEnableUserState()) {
            if ($zfcUserOptions->getDefaultUserState()) {
                $user->setState($zfcUserOptions->getDefaultUserState());
            }
        }

        $options = array(
            'user'          => $user,
            'provider'      => $provider,
            'userProfile'   => $userProfile,
        );


        $this->getEventManager()->trigger('registerViaProvider', $this, $options);
        $result = $this->getZfcUserMapper()->insert($user);
                    
                $activityLogService =  $this->getServiceManager()->get('jimmybase_activity_log_service');
                $activityLogService->addActivityLog($user," signed up with Jimmy!",'Add your first report','#/report/new-report');
               

	      $hybridAuth = $this->getServiceManager()->get('HybridAuth');

  		if($hybridAuth::storage()->get('hauth_session.trial')){
  			$hybridAuth::storage()->set('hauth_session.trial',null);
   			$this->getEventManager()->trigger('registerViaProviderTrial.post', $this, $options);
  		} elseif($referrer = $hybridAuth::storage()->get('hauth_session.referrer')){
        $options['referrer'] = $referrer;
        $this->getEventManager()->trigger('registerViaProviderReferrer.post', $this, $options);
      }

		  $this->getEventManager()->trigger('registerViaProvider.post', $this, $options);

        return $result;
    }

    /**
     * Set Event Manager
     *
     * @param  EventManagerInterface $events
     * @return HybridAuth
     */
    public function setEventManager(EventManagerInterface $events)
    {

        $events->setIdentifiers(array(
            __CLASS__,
            get_called_class(),
        ));
        $this->events = $events;

        return $this;
    }

    /**
     * Get Event Manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (null === $this->events) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }
}
