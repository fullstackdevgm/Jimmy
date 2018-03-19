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
use JimmyBase\Entity\ClientInterface;

class Client extends EventProvider  implements ServiceManagerAwareInterface
{

    /**
     * @var UserMapperInterface
     */
    protected $clientMapper;

    /**
     * @var Form
     */
    protected $clientRegisterForm;

    /**
     * @var Form
     */
    protected $changePasswordForm;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;


	protected $cache;


	protected $clientapi_service;


    public function save($client)
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, 
                                          array('client' => $client, 'form' => $form));
        $date = date('Y-m-d h:i:s');     
     
       if (!$client  instanceof  ClientInterface) {       
            $data    = $client;
            $client  = new  \JimmyBase\Entity\Client();
            //$client->setId($data['client_id']);
            $client->setName($data['name']);
            $client->setParent($data['parent']);
            $client->setCreated($date);
            $client->setUpdated($date);
            $this->getClientMapper()->insert($client);

       } else {
          if($client->getId()) {
            $client->setUpdated($date);
            $this->getClientMapper()->update($client);
            $client_accounts_mapper  = $this->getClientAccountMapper();
          } else {
            $this->getClientMapper()->insert($client);
          }
        }
        
        $client_accounts_mapper  = $this->getClientAccountMapper();
        $account = new  \JimmyBase\Entity\ClientAccounts();
        if($client && $data['account']) {            
            $account->setClientId($client->getId());
            $account->setChannel($data['account']['channel']);
            $account->setName($data['account']['name']);
            $account->setEmail($data['account']['email']);
            $account->setAccountId($data['account']['account_id']);
            $account->setUserTokenId($data['account']['user_token_id']);
            //$account->setApiAuthInfo($data['account']['api_auth_info']);
            $client_accounts_mapper->insert($account);
          
        }
      
        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('client' => $client, 'form' => $form));

        return $client;
    }
    
    /**
     * Get the list of unMapped Clients. This is for the puropse of migration.
     * @param integer $currentUser
     * @return array
     */
    public function getUnmappedClients($currentUser) {
        $clientAccountMapper = $this->getClientAccountMapper();
        $unmappedClients = $clientAccountMapper->findUnmappedClients($currentUser);
        $client = $this->getClientMapper();  
        if ($unmappedClients) {
           foreach ($unmappedClients as $uc) {
               
              $data[] = array("name" => $client->findById($uc->getClientId())->getName(),
                              "channel" => $uc->getChannel(),
                              "accountId" => $uc->getAccountId(),
                              "id" => $uc->getId()
                             );
              
           }
        }
        return $data;
    }
    
    /**
     * Migrate all the clients in the selected source.
     * @param A $source
     * @return boolean
     */
    public function migrateClients($id) {
        $clientAccountMapper = $this->getClientAccountMapper();
        $userTokenMapper =  $this->getServiceManager()->get('jimmybase_usertoken_mapper');
        $source = $userTokenMapper->findById($id);
        $parent = $source->getParentId();
        $token = $source->getToken();
        $channel = $source->getChannel();
        $userTokenId = $source->getId();
       
       
        $clientAcc = $this->getClientApiService()
                          ->setChannel($channel)
                          ->setApiAccessToken(unserialize($token))
                          ->fetchClientsAccounts($parent);    
        $unmappedClients = $this->getUnmappedClients($parent);
    
       if($clientAcc) {
           foreach($clientAcc as $ca) {
               foreach($unmappedClients as $uc) {
                if ($ca["customerId"]) {
                    if($ca["customerId"] == $uc['accountId']) {
                        $clientObj = $clientAccountMapper->findById($uc['id']);
                        $clientObj->setUserTokenId($userTokenId);
                        $clientAccountMapper->update($clientObj);
                    }
                             
                               
                } else if ($ca["id"]) 
                    if($ca["id"] == $uc['accountId']) {
                        $clientObj = $clientAccountMapper->findById($uc['id']);
                        $clientObj->setUserTokenId($userTokenId);
                        $clientAccountMapper->update($clientObj);
                    }                   
                }
           }
       }
       return true;
          
    } 
    
    
    /**
     * Add Account.
     * 
     * @param array $data
     * @return \JimmyBase\Entity\ClientAccounts
     */
    public function addAccount($data)
    {
        $account = new  \JimmyBase\Entity\ClientAccounts();
        $client_accounts_mapper  = $this->getClientAccountMapper();
        
        $client = $this->getClientMapper()->findById($data['client_id']);     
         if($client && $data['account']) {
           
            $account->setClientId($client->getId());
            $account->setChannel($data['account']['channel']);
            $account->setName($data['account']['name']);
            $account->setEmail($data['account']['email']);
            $account->setAccountId($data['account']['account_id']);
            $account->setUserTokenId($data['account']['user_token_id']);
          //  $account->setApiAuthInfo($data['account']['api_auth_info']);
            if(!$account->getId()) {
              $client_accounts_mapper->insert($account);
            }
            else
              $client_accounts_mapper->update($account);
        }

        return $account;
    }

    public function upload($client,$logo){
        $client = $this->getClientMapper()->findById($client['client_id']);

        if($client && isset($logo) && !$logo['error']){
            $ext = explode(".", $logo['name']);
            $ext = $ext[count($ext)-1];

            $filename       = md5($client->getId()).'.'.$ext;//str_replace(" ","_",$logo['name']);
            $filename_thumb = md5($client->getId()).'-thumb'.'.'.$ext;//str_replace(" ","_",$logo['name']);


            $upload_dir     = './data/logos/clients/';


            // Delete the old logo
            $old_logo = $client->getLogo();

            if(is_file($upload_dir.$old_logo))
                unlink($upload_dir.$old_logo);

            if(move_uploaded_file($logo['tmp_name'],$upload_dir.$filename)){
                $app = new \JimmyBase\Controller\Plugin\App($this->getServiceManager());

                //Save Large
                $app->resizeImage($upload_dir.$filename,$upload_dir.$filename_thumb,150,150);

                $client->setLogo($filename_thumb);
                $this->getClientMapper()->update($client);
                unlink($upload_dir.$filename);

            }
        } else {

        }
    }


    public function saveAccount(array $data){

        $client  = new  \JimmyBase\Entity\ClientAccounts();
        $form = $this->getClientRegisterForm();


        $client->setLogo($data['logo']);
        unset($data['logo']);


        $form->setHydrator(new ClassMethods());
        $form->bind($client);
        $form->setData($data);



        if(!$form->isValid()) {
            return false;
        }


        $client = $form->getData();


        $this->getEventManager()->trigger(__FUNCTION__, $this, array('client' => $client, 'form' => $form));

        $date = date('Y-m-d h:i:s');

        if(!$client->getId()) {
            $client->setCreated($date);
            $client->setUpdated($date);
            $this->getClientMapper()->insert($client);
        } else  {
            $client->setUpdated($date);
            $this->getClientMapper()->update($client);
        }


        if($client && isset($logo_new) && !$logo_new['error']){

            $filename       = md5($client->getId()).str_replace(" ","_",$logo_new['name']);
            $filename_thumb = md5($client->getId()).'-thumb-'.str_replace(" ","_",$logo_new['name']);

            $upload_dir     = './data/logos/clients/';


            // Delete the old logo
            $old_logo = $client->getLogo();

            if(is_file($upload_dir.$old_logo))
                unlink($upload_dir.$old_logo);

            if(move_uploaded_file($logo_new['tmp_name'],$upload_dir.$filename)){
                $app = new \JimmyBase\Controller\Plugin\App($this->getServiceManager());

                //Save Large
                $app->resizeImage($upload_dir.$filename,$upload_dir.$filename_thumb,150,150);

                $client->setLogo($filename_thumb);
                $this->getClientMapper()->update($client);
                unlink($upload_dir.$filename);

            }
        } else {

        }
        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('client' => $client, 'form' => $form));

        return $client;
    }


    public function clientsToArray($agency_id = null) {

           if($agency_id)
                   $clients = $this->getClientMapper()->fetchAllByAgency($agency_id);
           else
                   $clients = $this->getClientMapper()->fetchAll();


           if(!$clients) return false;

           foreach($clients as $client){
                   $clientArray[$client->getId()] = $client->getName();
           }

     return $clientArray;
    }



    public function register(array $data){
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




	public function getAdwordsClients($agency_id,$api_access){
		# Set the cache key for the client
            
		$key  = $agency_id-'adwords';

		if($this->getCache()->hasItem($key)){

		   $cache_clients = unserialize($this->getCache()->getItem($key));
		}

		if(isset($cache_clients['clients'])){
			$clients_array = $cache_clients['clients'];
		} else {
			$clients_api   = $this->getClientApi();
			$clients_api->setApiAccessToken($agency_id,$api_access);

	    	$clients_array = $clients_api->fetchAdwordsClientAccounts($api_access);

			$cache_clients['clients'] = $clients_array;
			$this->getCache()->setItem($key,serialize($cache_clients));
		}


	   return $clients_array;
	}



	public function getAdwordsClientsArray($agency_id,$api_access){
		# Set the cache key for the client
		$clients = $this->getAdwordsClients($agency_id,$api_access);

		if(!$clients) return false;

		foreach($clients as $client){
				$clients_array[$client['customerId']] = $client['name'];
		}


		return $clients_array;
	}



    /**
     * getUserMapper
     *
     * @return UserMapperInterface
     */
    public function getClientAccountMapper()
    {
        if (null === $this->clientAccountMapper) {
            $this->clientAccountMapper = $this->getServiceManager()->get('jimmybase_client_accounts_mapper');
        }
        return $this->clientAccountMapper;
    }
    
     public function getClientApiService()
    {
        if (!$this->clientApiService) {
            $this->clientApiService = $this->getServiceManager()->get('jimmybase_clientapi_service');
        }
        return $this->clientApiService;
    }


    /**
     * setUserMapper
     *
     * @param UserMapperInterface $userMapper
     * @return User
     */
    public function setClientAccountMapper(ClientAccountMapperInterface $clientAccountMapper)
    {
        $this->clientAccountMapper = $clientAccountMapper;
        return $this;
    }


    /**
     * getUserMapper
     *
     * @return UserMapperInterface
     */
    public function getClientMapper()
    {
        if (null === $this->clientMapper) {
            $this->clientMapper = $this->getServiceManager()->get('jimmybase_client_mapper');
        }
        return $this->clientMapper;
    }
    

    /**
     * setUserMapper
     *
     * @param UserMapperInterface $userMapper
     * @return User
     */
    public function setClientMapper(ClientMapperInterface $clientMapper)
    {
        $this->clientMapper = $clientMapper;
        return $this;
    }



    public function getClientRegisterForm()
    {
        if (!$this->clientRegisterForm) {
            $this->setClientRegisterForm($this->getServiceManager()->get('jimmybase_client_form'));
        }
        return $this->clientRegisterForm;
    }

   public function setClientRegisterForm(Form $clientRegisterForm)
    {
        $this->clientRegisterForm = $clientRegisterForm;
    }


	public function getClientApi()
    {
       if (!$this->clientapi_service) {
            $this->setClientApi($this->getServiceManager()->get('jimmybase_clientapi_service'));
       }

       return $this->clientapi_service;
    }

	public function setClientApi($clientapi_service)
    {
        $this->clientapi_service = $clientapi_service;
    }


	public function getCache(){
		if (!$this->cache) {
			$this->setCache($this->getServiceManager()->get('cache'));
		}

      return $this->cache;
    }

	public function setCache($cache){
        $this->cache = $cache;
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
