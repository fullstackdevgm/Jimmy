<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace JimmyBase\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use JimmyBase\Entity\ActivityLog as LogEntity;
class ActivityLog extends EventProvider implements ServiceManagerAwareInterface
{
    
/**
 * Insert user activity log to the activity table.
 * 
 * @param \JimmyBase\Entity\User $currentUserEntity
 * @param string $message
 * @param string $link
 * @return boolean
 */
public function addActivityLog($currentUserEntity, $message, $itemName, $link) {
    $parent = $this->getParentUser($currentUserEntity);
    $activityLogObj = new  LogEntity();
    $activityLogObj->setUserId($currentUserEntity->getId());
    $activityLogObj->setParentId($parent->getId());
    $activityLogObj->setMessage($message);
    $activityLogObj->setRelatedItem($itemName);
    $activityLogObj->setRelatedLink($link);
    $activityLogMapper = $this->getServiceManager()
                              ->get('jimmybase_activity_log_mapper');
    $activityLogMapper->insert($activityLogObj);
    return true;
    
}

public function fetchAllUserLog($currentUserEntity, $limit = 15) {
    $parent = $this->getParentUser($currentUserEntity);
    $activityLogMapper = $this->getServiceManager()
                              ->get('jimmybase_activity_log_mapper');
    $userLog = $activityLogMapper->findByParent($parent->getId(),  $limit);   
    
    $logData = array();
    foreach($userLog as $ul) {
      $userMapper = $this->getServiceManager()
                        ->get('jimmybase_user_mapper');
      $activityUser = $userMapper->findById($ul->getUserId());
      $created = new \DateTime($ul->getCreated());
      $now = new \DateTime();
      $interval = $now->diff($created);
      $IntervalText = $this->intervalToText($interval);
      $thumb = $userMapper->getMeta($ul->getUserId(),'thumb');
      $logData[] = array("user" =>$activityUser->getName(),
                         "message" =>$ul->getMessage(),
                         "relatedItem" => $ul->getRelatedItem(),
                         "relatedLink" => $ul->getRelatedLink(),
                         "userThumb" => $thumb,
                         "interval" => $IntervalText
                        );
    }
    return $logData;
}

public function intervalToText($interval) 
{
    if ($interval->y > 0) {
        if ($interval["y"] > 1) {
            return "1 year";
        } else {
            return $interval["y"]." years";
        } 
        
    } else if ($interval->m > 0) {
        if ($interval->m >1) {
            return "1 month";
        } else {
            return $interval->m." months";
        } 
            
    } else if ($interval->d > 0) {
        if ($interval->d == 1) {
            return "1 day";
        } else {
            return $interval->d." days";
        } 
        
    } else if ($interval->h > 0) {
        if ($interval->h == 1) {
            return "1 hour";
        } else {
            return $interval->h." hours";
        } 
    } else {
         if ($interval->i == 1) {
            return "1 minute";
        } else {
            return $interval->i." minutes";
        } 
        
    }
     
}


/**
 * To get the currentLoggedInuser;
 *
 * @return Entity\User
 *
 */
public function getParentUser($userEntity)
{
  $currentUserId = $userEntity->getId();
     if ($userEntity->getType()=='coworker') {
           $currentUserId = $this->getServiceManager()
                                   ->get('jimmybase_user_mapper')
                                   ->getMeta($currentUserId,'parent');
      }
 $user = $this->getServiceManager()
               ->get('jimmybase_user_mapper')
               ->findById($currentUserId);
 return $user;
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