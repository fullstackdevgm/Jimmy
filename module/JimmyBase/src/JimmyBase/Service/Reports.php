<?php

namespace JimmyBase\Service;

use Zend\Authentication\AuthenticationService;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Sql\Sql;
use ZfcBase\EventManager\EventProvider;
use JimmyBase\Mapper\ReportInterface as ReportMapperInterface;
use \JimmyBase\Entity\ReportsInterface;

class Reports extends EventProvider   implements ServiceManagerAwareInterface
{
    /**
     * @var UserMapperInterface
     */
    protected $reportsMapper;

    /**
     * @var Form
     */
    protected $reportsForm;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;


    public function save($report)
    {

    		if(is_array($report)){
                $data    = $report;
    			$report  = new \JimmyBase\Entity\Reports();
    			
                $report->setTitle($data['title']);
                $report->setUserId($data['client_id']);
    			$report->setStatus(1);
                $report->setPaid($data['paid']);
    			$now    = date('Y-m-d h:i:s');
    			
                // Add user id here

    			if(!$data['id']){
    				$report->setCreated($now);		
    				$report->setUpdated($now);
    			} else {
    				$report->setUpdated($now);
    			}
    		} elseif($report  instanceof  ReportsInterface){
                $now    = date('Y-m-d h:i:s');
                $report->setStatus(1);

                if(!$report->getId()){
                    $report->setCreated($now);      
                    $report->setUpdated($now);
                } else {
                    $report->setUpdated($now);
                }
            }
	
			$this->getEventManager()->trigger(__FUNCTION__, $this, array('report' => $report, 'form' => $form));
			
			if(!$report->getId())
				$this->getMapper()->insert($report);
			else        	
				$this->getMapper()->update($report);
	
			$this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('report' => $report, 'form' => $form));
			
			return $report;

    }
	
	
	 public function copy($report)
    {		
			$report->setId(null);
			$report->setStatus(1);
			
			$now    = date('Y-m-d h:i:s');
			
			$report->setCreated($now);		
			$report->setUpdated($now);
			
			$this->getEventManager()->trigger(__FUNCTION__, $this, array('report' => $report));
			
			$this->getMapper()->insert($report);
	
			$this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('report' => $report));
			
			return $report;
    }
	
	
	
	public function saveField(\JimmyBase\Entity\Reports $report)
    {
			$now    = date('Y-m-d h:i:s');
			
			$report->setUpdated($now);
			
			if(!$report->getId())
				$this->getMapper()->insert($report);
			else        	
				$this->getMapper()->update($report);
	
			
			return $report;
    }

    /**
     * getUserMapper
     *
     * @return UserMapperInterface
     */
    public function getMapper()
    {
        if (null === $this->reportMapper) {
            $this->reportMapper = $this->getServiceManager()->get('jimmybase_reports_mapper');
        }
        return $this->reportMapper;
    }
    
    public function getClientAccountMapper()
    {
       return $this->getServiceManager()->get('jimmybase_clientaccounts_mapper');
    }
    public function getClientMapper()
    {
       return $this->getServiceManager()->get('jimmybase_client_mapper');
    }
   
    

    /**
     * setUserMapper
     *
     * @param UserMapperInterface $userMapper
     * @return User
     */
    public function setMapper(ReportMapperInterface $reportMapper)
    {
        $this->reportMapper = $reportMapper;
        return $this;
    }

  
   
    public function getReportForm()
    {
        if (!$this->reportForm) {
            $this->setReportForm($this->getServiceManager()->get('jimmybase_reports_form'));
        }
        return $this->reportForm;
    }
	
    public function setReportForm(Form $reportForm)
    {
        $this->reportForm = $reportForm;
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
    
    
    
    /*this function is no longer in use . This was used to refresh the user token.
     *  No longer need to use it as there is a mechanism to deal handle the tokens*/
    public function refreshTokens($clientId = null, $textOutput = false)
    {  
        
        $url = 'https://www.googleapis.com/oauth2/v3/token';
        $data = array('client_id' => '272310704029-eeqidn6de6g33r514k37le595242u171.apps.googleusercontent.com',
                      'client_secret'=> 'hFxpVPoyzu8u4ZCIr5xIyE6N',
                       'grant_type'=>'refresh_token');
         // use key 'http' even if you send the request to https://...
                $options = array(
                    'http' => array(
                        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method'  => 'POST',
                        'Host' => 'www.googleapis.com'
                    ),
                );
        $clientMapper = $this->getClientMapper();
        if($textOutput) echo "start refresh :". date('m/d/Y h:i:s a', time());
        if (isset($clientId)) {
            $clientMap = $clientMapper->findById($clientId);
            $refreshClients = array($clientMap);
           
        } else {        
            $refreshClients = $clientMapper->findRefreshClients();
        }
        $clientAccMapper = $this->getClientAccountMapper();
        foreach($refreshClients as $r) {            
            if (!$clientId) {
                $items = $clientAccMapper->findByParent($r->getParent());
            } else {
                $items = $clientAccMapper->findByClientId($clientId);                
            }
            foreach($items as $i) {
               $jsonFlag = false;
               $itemArr = unserialize($i->getApiAuthInfo());
               if (!isset($itemArr["refresh_token"])) {
                   $itemArr = json_decode($itemArr, true);
                   $jsonFlag = true;
               }
               $data['refresh_token']= $itemArr["refresh_token"]; 
               $options['http']['content'] = http_build_query($data);
               $context  = stream_context_create($options);
               $result = file_get_contents($url, false, $context);

               if ($result) {
                    $itemArr["access_token"] = $result["access_token"];
                    $itemArr["expires_in"] = $result["expires_in"];
                    $itemArr["token_type"] = $result["token_type"];
                   if ($jsonFlag) {
                       $apiAuthInfo = serialize(json_encode($itemArr));
                   } else {                  
                       $apiAuthInfo = serialize($itemArr);
                   }
                  $i->setApiAuthInfo($apiAuthInfo); 
                  $clientAccMapper->update($i);
                    if($textOutput) echo "updated". $i->getClientId();
                 
               } else {
                   if($textOutput) echo  "false";
               }
            }
        }
        if($textOutput) echo "\nend refresh :". date('m/d/Y h:i:s a', time()). "\n";
    }
    
    
    
    
     public function copyToken($clientId, $channel)
    {  
        $newToken = 'a:7:{s:9:"client_id";s:72:"272310704029-eeqidn6de6g33r514k37le595242u171.apps.googleusercontent.com";s:13:"client_secret";s:24:"hFxpVPoyzu8u4ZCIr5xIyE6N";s:12:"access_token";s:73:"ya29.IALf2oTexvkHPTrBe9JFQ4ehAep0pVab8xGvZqa8myo1oAokHPHFBWQZuisI9dml0h-x";s:10:"token_type";s:6:"Bearer";s:10:"expires_in";i:3600;s:13:"refresh_token";s:45:"1/RJFZgZYojMNARgJLeheyoqykUF2ahE0I4SY_vUdhchE";s:9:"timestamp";i:1446448729;}';

              
        $clientAccMapper = $this->getClientAccountMapper();   
        $clients = $clientAccMapper->findByParentandChannel($clientId, $channel);                                       
        
        $i =1;     
        
          foreach ($clients as $cl) {             
               $cl->setApiAuthInfo($newToken);
               $clientAccMapper->update($cl);
              echo $cl->getid(). "updated \n";
          }
        
       
    }

    /**
     * Finds the total number of reports created by tha agency
     * of the client.
     * @param $clientId ID of the client
     * @return Number of reports created by the parent agency
    **/
    public function getCount($clientId) {
        return $this->getMapper()->getCount($clientId);
    }
}
