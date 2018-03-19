<?php

namespace JimmyBase\Service;


use Zend\ServiceManager\ServiceManagerAwareInterface;
use ZfcBase\EventManager\EventProvider;
use Zend\ServiceManager\ServiceManager;
use JimmyBase\Entity\VisitedTour;
use JimmyBase\Entity\Tour;

class GuidedTour extends EventProvider implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    public function visitTour($tourName, $userId) {  
         
          $tour = $this->getTourMapper()->findByTourName($tourName); 
          $checkVisited = $this->getVisitedTourMapper()
                               ->findVisited($tour->getId(), $userId);
          
          if($checkVisited) {
              return array(visited => true);
          } else {
            $visitedTour = new VisitedTour();       
            $visitedTour->setTourId($tour->getId())
                        ->setUserId($userId)
                        ->setCreated();
            $this->getVisitedTourMapper()->insert($visitedTour);  
            return array(visited => false);
          }
    }
 
     /**
     * getUserMapper
     *
     * @return UserMapperInterface
     */
    public function getVisitedTourMapper()
    {       
        return $this->getServiceManager()->get('jimmybase_visited_tour_mapper');
       
    }
    
    /**
     * getUserMapper
     *
     * @return UserMapperInterface
     */
    public function getTourMapper()
    {
        return  $this->getServiceManager()->get('jimmybase_tour_mapper');        
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