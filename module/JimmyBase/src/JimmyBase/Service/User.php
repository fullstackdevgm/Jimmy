<?php

namespace JimmyBase\Service;

use Zend\Authentication\AuthenticationService;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Crypt\Password\Bcrypt;
use ZfcBase\EventManager\EventProvider;
use JimmyBase\Mapper\UserInterface as UserMapperInterface;
use JimmyBase\Entity\User as UserEntity;

class User extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     * @var UserMapperInterface
     */
    protected $userMapper;

    /**
     * @var AuthenticationService
     */
    protected $authService;

    /**
     * @var Form
     */
    protected $loginForm;

    /**
     * @var Form
     */
    protected $registerForm;

    /**
     * @var Form
     */
    protected $changePasswordForm;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var UserServiceOptionsInterface
     */
    protected $options;

	 public function save(array $data)
    {

        $user  = new \JimmyBase\Entity\User();

        $user->setName($data['name']);
        $user->setDisplayName($data['name']);
        $user->setEmail($data['email']);




		$now  = date('Y-m-d h:i:s');

		if(!$user->getId()){
			 $bcrypt = new Bcrypt;
        	 $bcrypt->setCost(14);
         	 $user->setPassword($bcrypt->create($data['password']));
         	 $user->setCreated($now);
		} else {
		     $user->setPassword($data['password']); // Not required to encrypt the password here as it is already encrypted

		}

	     $user->setType($data['type'])
              ->setState($data['state'])
              ->setUpdated($now);

        $this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user, 'form' => $form));

		if(!$user->getId())
        	$this->getUserMapper()->insert($user);
		else
		    $this->getUserMapper()->update($user);


        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $user, 'form' => $form));

        return $user;
    }


    public function saveTitle($user)
    {


        $now  = date('Y-m-d h:i:s');

        $user->setUpdated($now);

        $this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user, 'form' => $form));

        if($user->getId())
          $user = $this->getUserMapper()->update($user);
        else
          return false;



        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $user, 'form' => $form));

        return $user;
    }



    public function uploadThumb($user,$logo){
        $user = $this->getUserMapper()->findById($user['user_id']);

        if($user && isset($logo) && !$logo['error']){
            $ext = explode(".", $logo['name']);
            $ext = $ext[count($ext)-1];

            $filename       = md5($user->getId()).'.'.$ext;//str_replace(" ","_",$logo['name']);
            $filename_thumb = md5($user->getId()).'-thumb'.'.'.$ext;//str_replace(" ","_",$logo['name']);

            $upload_dir     = './data/logos/agencies/';

            $old_logo = $this->getUserMapper()->getMeta($user->getId(),'thumb');
            if(is_file($upload_dir.$old_logo))
             unlink($upload_dir.$old_logo);

            if(move_uploaded_file($logo['tmp_name'],$upload_dir.$filename_thumb)){
                $app = new \JimmyBase\Controller\Plugin\App($this->getServiceManager());

                //Save Large
                $app->resizeImage($upload_dir.$filename,$upload_dir.$filename_thumb,150,150);
                $user->setKey('thumb');
                $user->setValue($filename_thumb);
                $user = $this->saveMeta($user);
                unlink($upload_dir.$filename);
            }

        } else {

        }
    }

    public function uploadLogo($user,$logo){
        $user = $this->getUserMapper()->findById($user['user_id']);

        if($user && isset($logo) && !$logo['error']){
            $ext = explode(".", $logo['name']);
            $ext = $ext[count($ext)-1];

            $filename       = md5($user->getId()).'.'.$ext;//str_replace(" ","_",$logo['name']);
            $filename_thumb = md5($user->getId()).'-logo'.'.'.$ext;//str_replace(" ","_",$logo['name']);

            $upload_dir     = './data/logos/agencies/';

            $old_logo = $this->getUserMapper()->getMeta($user->getId(),'logo');
            if(is_file($upload_dir.$old_logo))
             unlink($upload_dir.$old_logo);

            if(move_uploaded_file($logo['tmp_name'],$upload_dir.$filename_thumb)){
                $app = new \JimmyBase\Controller\Plugin\App($this->getServiceManager());

                //Save Large
                $app->resizeImage($upload_dir.$filename,$upload_dir.$filename_thumb,210,75);
                $user->setKey('logo');
                $user->setValue($filename_thumb);
                $user = $this->saveMeta($user);
                unlink($upload_dir.$filename);
            }

        } else {

        }
    }

    // Removes logo meta from database and file from server.
    public function removeLogo($user) {
        // Find user
        $user = $this->getUserMapper()->findById($user['user_id']);
        //Check if user is available
        if($user) {

            $dir     = './data/logos/agencies/';

            $old_logo = $this->getUserMapper()->getMeta($user->getId(),'logo');

            // Remove logo file from server
            if(is_file($dir.$old_logo))
             unlink($dir.$old_logo);

            // Remove logo meta from database
            $this->getUserMapper()->removeLogo($user->getId());

            return true;

        } else {
            return false;
        }
    }

    // Removes logo meta from database and file from server.
    public function removeThumb($user) {
        // Find user
        $user = $this->getUserMapper()->findById($user['user_id']);
        //Check if user is available
        if($user) {

            $dir     = './data/logos/agencies/';

            $old_logo = $this->getUserMapper()->getMeta($user->getId(),'thumb');

            // Remove logo file from server
            if(is_file($dir.$old_logo))
             unlink($dir.$old_logo);

            // Remove logo meta from database
            $this->getUserMapper()->removeThumb($user->getId());

            return true;

        } else {
            return false;
        }
    }

    public function deleteUser($user_id){

        $user_mapper = $this->getUserMapper();

      if($user_mapper->delete($user_id,"user_meta.user_id = '". $user_id."'",'user_meta')){
         if($user_mapper->delete($user_id,"user_provider.user_id = '". $user_id."'",'user_provider')){
            return $user_mapper->delete($user_id);
         }
      }
    }

    public function deleteCoworker($user_id){

        $user_mapper = $this->getUserMapper();

      if($user_mapper->delete($user_id,"user_meta.user_id = '". $user_id."'",'user_meta')){
         if($user_mapper->delete($user_id,"user_provider.user_id = '". $user_id."'",'user_provider')){
            return $user_mapper->delete($user_id,"user.user_id = '". $user_id."' and user.type='".UserEntity::COWORKER."'");
         }
      }
    }


    public function addToMailChimp($user,$type='agency'){
        $config           = $this->getServiceManager()->get('Config');
        $mailchimp_config = $config['mailchimp-config'];

        $mailchimp = $this->getServiceManager()->get('subscriber');

        $name = @explode(" ",$user->getName());

        $fname = $name[0];
        $lname = $name[1];

        $list_id =  $mailchimp_config[$type.'_list_id'];

        try{

            $mailchimp->email($user->getEmail())
                      ->mergeVars(array('FNAME' => $fname,'LNAME' => $lname))
                      ->listId($list_id)
                      ->emailType($mailchimp_config['email_type'])
                      ->subscribe();
        } catch(\Exception $e){
            // Do nothing
        }
    }

	public function setDefaultPackage($user){
        if(!$user->getId())
          return false;

        $config       = $this->getServiceManager()->get('Config');
        $jimmy_config = $config['jimmy-config'];


        $user->setKey('package');
        $user->setValue($jimmy_config['free_package_id']);

        return $this->saveMeta($user);
    }


    public function setReferrerPackage($user,$referrer){
        if(!$user->getId())
          return false;

        $config       = $this->getServiceManager()->get('Config');
        $package      = $this->getServiceManager()->get('jimmybase_package_mapper')->findByTitle($referrer);

        $user->setKey('package');
        $user->setValue($package->getId());

        return $this->saveMeta($user);
    }


	public function saveMeta(\JimmyBase\Entity\User $user){

		$user_id = $user->getId();

        $this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user, 'form' => $form));

		if(!$this->getUserMapper()->getMeta($user->getId(),$user->getKey()))
        	$this->getUserMapper()->insert($user,'user_meta',new \JimmyBase\Mapper\UserMetaHydrator());
		else
		    $this->getUserMapper()->update($user,"user_meta.user_id = ". $user->getId()." and user_meta.key = '".$user->getKey()."'",'user_meta',new \JimmyBase\Mapper\UserMetaHydrator());

		$user->setId($user_id);

        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $user, 'form' => $form));

        return $user;
	}

	public function removeMeta(\JimmyBase\Entity\User $user){

        $this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user, 'form' => $form));

		if($this->getUserMapper()->getMeta($user->getId(),$user->getKey())){

		    $this->getUserMapper()->delete($user,"user_meta.user_id = '". $user->getId()."' and user_meta.key = '".$user->getKey()."'",'user_meta');

		}


        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $user, 'form' => $form));

        return $user;
	}

	public function hasTrial($user){

		if(!$user->getId())
		  return false;

		$userPackageId = $this->getUserMapper()->getMeta($user->getId(),'package');
		$package       = $this->getServiceManager()->get('jimmybase_package_mapper')->findById($userPackageId);


		if($package->getIsFreeTrial())
		   return true;
		else
		   return false;

	}

	public function getPackage($user){
		if(!$user->getId())
		  return false;

		$userPackageId = $this->getUserMapper()->getMeta($user->getId(),'package');

		if(!$userPackageId)
		 return false;

		$package       = $this->getServiceManager()->get('jimmybase_package_mapper')->findById($userPackageId);

		return $package;
	}

    /**
     * change the current users password
     *
     * @param array $data
     * @return boolean
     */
    public function changePassword(array $data)
    {
        $currentUser = $this->getAuthService()->getIdentity();
        $newPass = $data['password'];

		if(empty($newPass))
          return false;

		$bcrypt = new Bcrypt;
        $bcrypt->setCost(14);

       /*if(!$bcrypt->verify($oldPass, $currentUser->getPassword())) {
            return false;
        }*/

        $pass = $bcrypt->create($newPass);

        $currentUser->setPassword($pass);


        $this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $currentUser));
        $this->getUserMapper()->update($currentUser);
        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $currentUser));

        return true;
    }

	public function resetPassword($user,$password){

		$bcrypt = new Bcrypt;
        $bcrypt->setCost(14);

        $pass = $bcrypt->create($password);
        $user->setPassword($pass);


        if($this->getUserMapper()->update($user))
           $this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user,'password'=>$password));

        return true;
	}

    public function changeEmail(array $data)
    {
        $currentUser = $this->getAuthService()->getIdentity();

        $bcrypt = new Bcrypt;
        $bcrypt->setCost($this->getOptions()->getPasswordCost());

        if (!$bcrypt->verify($data['credential'], $currentUser->getPassword())) {
            return false;
        }

        $currentUser->setEmail($data['newIdentity']);

        $this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $currentUser));
        $this->getUserMapper()->update($currentUser);
        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $currentUser));

        return true;
    }

    /**
     * getUserMapper
     *
     * @return UserMapperInterface
     */
    public function getUserMapper()
    {
        if (null === $this->userMapper) {
            $this->userMapper = $this->getServiceManager()->get('zfcuser_user_mapper');
        }
        return $this->userMapper;
    }

    /**
     * setUserMapper
     *
     * @param UserMapperInterface $userMapper
     * @return User
     */
    public function setUserMapper(UserMapperInterface $userMapper)
    {
        $this->userMapper = $userMapper;
        return $this;
    }

    /**
     * getAuthService
     *
     * @return AuthenticationService
     */
    public function getAuthService()
    {
        if (null === $this->authService) {
            $this->authService = $this->getServiceManager()->get('zfcuser_auth_service');
        }
        return $this->authService;
    }

    /**
     * setAuthenticationService
     *
     * @param AuthenticationService $authService
     * @return User
     */
    public function setAuthService(AuthenticationService $authService)
    {
        $this->authService = $authService;
        return $this;
    }

    /**
     * @return Form
     */
    public function getRegisterForm()
    {
        if (null === $this->registerForm) {
            $this->registerForm = $this->getServiceManager()->get('jimmybase_user_form');
        }
        return $this->registerForm;
    }

    /**
     * @param Form $registerForm
     * @return User
     */
    public function setRegisterForm(Form $registerForm)
    {
        $this->registerForm = $registerForm;
        return $this;
    }

    /**
     * @return Form
     */
    public function getChangePasswordForm()
    {
        if (null === $this->changePasswordForm) {
            $this->changePasswordForm = $this->getServiceManager()->get('zfcuser_change_password_form');
        }
        return $this->changePasswordForm;
    }

    /**
     * @param Form $changePasswordForm
     * @return User
     */
    public function setChangePasswordForm(Form $changePasswordForm)
    {
        $this->changePasswordForm = $changePasswordForm;
        return $this;
    }

    /**
     * get service options
     *
     * @return UserServiceOptionsInterface
     */
    public function getOptions()
    {
        if (!$this->options instanceof UserServiceOptionsInterface) {
            $this->setOptions($this->getServiceManager()->get('zfcuser_module_options'));
        }
        return $this->options;
    }

    /**
     * set service options
     *
     * @param UserServiceOptionsInterface $options
     */
    public function setOptions(UserServiceOptionsInterface $options)
    {
        $this->options = $options;
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }
}
