<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace JimmyBase\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\AbstractRestfulController;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ModelInterface;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Session\Container as SessionContainer;


class SourceApiController extends AbstractRestfulController 
{
   protected $identifierName = 'sourceId';
   
   public function getList() 
   {
    
    $sourceService = $this->getServiceLocator()->get('jimmybase_source_service');
    $currentUserId = $this->ZfcUserAuthentication()->getIdentity()->getId();
    if($this->ZfcUserAuthentication()->getIdentity()->getType()=='coworker') {
             $currentUserId = $this->getServiceLocator()
                                   ->get('jimmybase_user_mapper')
                                   ->getMeta($currentUserId,'parent');
    }
    $sourceList = $sourceService->getSource($currentUserId);
       return new JsonModel($sourceList);       
   }
   
   public function get($sourceId) 
   {
       
   }
   
   public function create($data)
   {       
    $sourceService = $this->getServiceLocator()->get('jimmybase_source_service');
    $currentUser = $this->ZfcUserAuthentication()->getIdentity();
    $currentUserId = $currentUser->getId();
    $session = new SessionContainer('Client_Auth');  
    
    if($this->ZfcUserAuthentication()->getIdentity()->getType()=='coworker') {
             $currentUserId = $this->getServiceLocator()
                                    ->get('jimmybase_user_mapper')
                                    ->getMeta($currentUserId,'parent');
    }
    
    if ( $sourceService->checkSourceExist($data['sourceName'],$currentUserId) && $data['sourceName']) {
        return new JsonModel(array("success" =>false, "message" => "Source Name already exist"));
        
    } else {
        $data['parentId'] = $currentUserId;
        $data['token'] = serialize($session->offsetGet('access_token'));
        $sourceId= $sourceService->addSource($data);
        if ($data['migrate']) {
            $sourceService = $this->getServiceLocator()->get('jimmybase_client_service');
            $sourceService->migrateClients($sourceId);
        }
         
        $activityLogService =  $this->getServiceLocator()->get('jimmybase_activity_log_service');
        $activityLogService->addActivityLog($currentUser,"Added Source ". $data['sourceName'], "","");

        return new JsonModel(array("success" => true, "message" => "Source Added"));
    }
    
   }
   public function update($sourceId, $data)
   {
       
   }
   public function delete($sourceId)
   {
       
   }
   
}