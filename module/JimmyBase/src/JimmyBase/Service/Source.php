<?php

namespace JimmyBase\Service;

use Zend\Authentication\AuthenticationService;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Crypt\Password\Bcrypt;
use ZfcBase\EventManager\EventProvider;
use JimmyBase\Entity\UserToken;

class Source extends EventProvider implements ServiceManagerAwareInterface
{
   /**
    * Add a source.
    * 
    * @param array $source
    * @return boolean\
    */
    public function addSource($source) {      
        $userTokenObj = new UserToken();
        $userTokenObj->setParentId($source['parentId']);
        $userTokenObj->setName($source['sourceName']);
        $userTokenObj->setchannel($source['channel']);
        $userTokenObj->setToken($source['token']);
        $userTokenObj->setCreated(date("Y-m-d H:i:s"));  
        $userTokenObj->setUpdated(date("Y-m-d H:i:s"));
        $tokenMapper = $this->getUserTokenMapper();       
        $insertObj = $tokenMapper->insert($userTokenObj);
        return $insertObj->getGeneratedValue();
    }
    
   /**
    * Check if the Source Name exist for a client.
    * @param string $sourceName
    * @param integer $parent
    * @return boolean
    */
    public function checkSourceExist($sourceName, $parent) {       
        $tokenMapper = $this->getUserTokenMapper();
        $userTokenObj = $tokenMapper->findSourceExist($parent, $sourceName);       
        if($userTokenObj) {
            return true;
        } else {
            return false;
        }
        
    }
    
    /**
     * Get Source Names for a user.
     * @param integer $parent
     * @return array
     */
    public function getSource($parent) {
        $tokenMapper = $this->getUserTokenMapper();
        $data =array();        
        try {
            $userTokenObj = $tokenMapper->findByParent($parent);
            if($userTokenObj) {
                foreach($userTokenObj as $source) {
                   $items[] = array(
                                    "name" =>$source->getName(),
                                    "id" => $source->getId(),
                                    "channel" => $source->getChannel()
                                    ); 
                }
                $data['message'] ="Sources Aviailable";
                $data['sourceList'] = $items;
                $data['success'] = true;
            }else {
                $data['message'] ="No Sources Found";
                $data['sourceList'] = null;
                $data['success'] = false;
            }
            
        } catch (\Exception $e) {
             $data['message'] =$e->getMessage();
             $data['sourceList'] = null;
             $data['success'] = false;            
        }
        return $data;        
        
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
    
    public function getUserTokenMapper() 
    {
        return $this->getServiceManager()->get('jimmybase_usertoken_mapper');
    }
 
}


?>
