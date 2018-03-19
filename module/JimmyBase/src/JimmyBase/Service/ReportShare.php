<?php

namespace JimmyBase\Service;

use Zend\Authentication\AuthenticationService;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Crypt\Password\Bcrypt;

use ZfcBase\EventManager\EventProvider;
use JimmyBase\Mapper\ReportShareInterface as ReportShareMapperInterface;

class ReportShare extends EventProvider   implements ServiceManagerAwareInterface
{
    /**
     * @var ReportShareMapperInterface
     */
    protected $reportshareMapper;


    /**
     * @var ServiceManager
     */
    protected $serviceManager;


    public function save(array $data)
    {
		
		 //try{	
			$reportshare  = new \JimmyBase\Entity\ReportShare();
			$reportshare->setUserId($data['user_id']);
            $reportshare->setReportId($data['report_id']);
            $reportshare->setStatus(1);
            $reportshare->setDate(date('Y-m-d h:i:s'));
				
			$this->getEventManager()->trigger(__FUNCTION__, $this, array('reportshare' => $reportshare, 'form' => $form));
			
			$this->getMapper()->insert($reportshare);
	
			$this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('report' => $reportshare, 'form' => $form));
			
			return $reportshare;
		 //} catch(Exception $e){
		//	 print_r($e);
		 
		// }
    }
	
    /**
     * getUserMapper
     *
     * @return ReportShareMapperInterface
     */
    public function getMapper()
    {
        if (null === $this->reportshareMapper) {
            $this->reportshareMapper = $this->getServiceManager()->get('jimmybase_reportshare_mapper');
        }
        return $this->reportshareMapper;
    }

    /**
     * setUserMapper
     *
     * @param ReportShareMapperInterface $userMapper
     * @return User
     */
    public function setMapper(ReportShareMapperInterface $reportMapper)
    {
        $this->reportshareMapper = $reportshareMapper;
        return $this;
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
