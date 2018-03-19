<?php

namespace JimmyBase\Service;

use Zend\Authentication\AuthenticationService;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Crypt\Password\Bcrypt;
use ZfcBase\EventManager\EventProvider;

use JimmyBase\Mapper\AgencyInterface as AgencyMapperInterface;

class Agency extends EventProvider   implements ServiceManagerAwareInterface
{

    /**
     * @var UserMapperInterface
     */
    protected $clientMapper;

  
    /**
     * @var Form
     */
    protected $agencyRegisterForm;

    /**
     * @var Form
     */
    protected $changePasswordForm;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;



    /**
     * createFromForm
     *
     * @param array $data
     * @return \ZfcUser\Entity\UserInterface
     * @throws Exception\InvalidArgumentException
     */
    public function save(array $data)
    {
		
        $agency  = new \JimmyBase\Entity\Agency();
		$form = $this->getAgencyRegisterForm();
		$user_service = $this->getServiceManager()->get('jimmybase_user_service');

		if(is_array($data['logo'])){
			$logo = $data['logo'];
			unset($data['logo']);
		}
		
		
        $form->setHydrator(new ClassMethods());
        $form->bind($agency);
        $form->setData($data);
		
	
		if(!$form->isValid()) {
            return false;
        }

        $client = $form->getData();
		
		if(!$client->getId()){
			
			 $bcrypt = new Bcrypt;
        	 $bcrypt->setCost(14);
         	 $agency->setPassword($bcrypt->create($data['password']));
         	 $agency->setState($data['state']);
         	 $agency->setType($data['type']);
			 
		} else {
			
		     $agency->setPassword($data['password']); // Not required to encrypt the password here as it is already encrypted
         	 $agency->setType($data['type']);
		
		}
		
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('agency' => $agency, 'form' => $form));
		
		if(!$agency->getId())
        	$this->getMapper()->insert($agency);
		else        	
		    $this->getMapper()->update($agency);

		if($agency && $logo){
			
			$filename       = md5($agency->getId()).'-'.$logo['name'];
			$filename_large = md5($agency->getId()).'-large-'.$logo['name'];
			$filename_thumb = md5($agency->getId()).'-thumb-'.$logo['name'];
			$user 			= $user_service->getUserMapper()->findById($agency->getId());
			$upload_dir     = './data/logos/agencies/';
			
			if(move_uploaded_file($logo['tmp_name'],$upload_dir.$filename)){
				$app = new \JimmyBase\Controller\Plugin\App($this->getServiceManager());
				//Save Large
				$app->resizeImage($upload_dir.$filename,$upload_dir.$filename_thumb,150,150);
				//Save Thumb
				//$app->resizeImage($upload_dir.$filename,$upload_dir.$filename_thumb,50,50);
			
				$user->setKey('logo');
				$user->setValue($filename_thumb);
				
				$user = $user_service->saveMeta($user);
				$user->setId($agency->getId());
				
                //$user->setKey('logo-thumb');
				//$user->setValue($filename_thumb);
				//$user_service->saveMeta($user);
				
				unlink($upload_dir.$filename);
			
			}
		}
		
        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('agency' => $agency, 'form' => $form));
		
        return $client;
    }
	
	 public function register(array $data)
    {
        $client  = new \Admin\Entity\Client();
        $form  = $this->getClientRegisterForm();
        $form->setHydrator(new ClassMethods());
        $form->bind($client);
        $form->setData($data);
		
		
        if (!$form->isValid()) {
            return false;
        }

        $client = $form->getData();
        /* @var $user \ZfcUser\Entity\UserInterface */
		$client->setName($data['name']);
       
	    $bcrypt = new Bcrypt;
        $bcrypt->setCost(14);
        $client->setPassword($bcrypt->create($client->getPassword()));
		$client->setUsername($data['username']);
		$client->setType($data['type']);
		$client->setState($data['state']);

      
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $client, 'form' => $form));
        $this->getClientMapper()->insert($client);
        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $client, 'form' => $form));
        return $user;
    }

	

    /**
     * change the current users password
     *
     * @param array $data
     * @return boolean
     */
    public function changePassword(array $data)
    {
        $newPass = $data['password'];
		
		$agency = $this->getMapper()->findById($data['id']);
		
		
		if(!$agency)
		 return false;
		
        $bcrypt = new Bcrypt;
        $bcrypt->setCost(14);

        $pass = $bcrypt->create($newPass);
        $agency->setPassword($pass);

        $this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $agency));
        $this->getMapper()->update($agency);
        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $agency));

        return $agency;
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
    public function getMapper()
    {
        if (null === $this->agencyMapper) {
            $this->agencyMapper = $this->getServiceManager()->get('jimmybase_agency_mapper');
        }
        return $this->agencyMapper;
    }

    /**
     * setUserMapper
     *
     * @param UserMapperInterface $userMapper
     * @return User
     */
    public function setMapper(AgencyMapperInterface $agencyMapper)
    {
        $this->agencyMapper = $agencyMapper;
        return $this;
    }

  
   
    public function getAgencyRegisterForm()
    {
        if (!$this->agencyRegisterForm) {
            $this->setAgencyRegisterForm($this->getServiceManager()->get('jimmybase_agency_form'));
        }
        return $this->agencyRegisterForm;
    }
	
     public function setAgencyRegisterForm(Form $agencyRegisterForm)
     {
        $this->agencyRegisterForm = $agencyRegisterForm;
     }

     public function agencyToArray(){
	    
		$agencies = $this->getMapper()->fetchAll();
		
		
		if(!$agencies) return false;
		
		foreach($agencies as $agency){
			$agencyArray[$agency->getId()] = $agency->getName();
		}
		
	  return $agencyArray;
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
