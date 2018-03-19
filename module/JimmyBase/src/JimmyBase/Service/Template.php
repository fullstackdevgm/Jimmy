<?php

namespace JimmyBase\Service;


use Zend\ServiceManager\ServiceManagerAwareInterface;
use ZfcBase\EventManager\EventProvider;
use Zend\ServiceManager\ServiceManager;
use JimmyBase\Entity\Template as TemplateEntity;

class Template extends EventProvider implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    /**
     * Save template.
     *      
     * @param int $reportId
     * @param string $templateName
     * @param int $user
     */
    public function saveTemplate( $reportId, $templateName, $user) {
        try {
            $template = new TemplateEntity;
            $template->setTemplateName($templateName);
            $template->setUserId($user);
            $template->setType("user");
            $template->setCreated();        
            $mapper = $this->getTemplateMapper();        
            $result = $mapper->insert($template);
            $tempId = $result->getGeneratedValue();
            $clientAcc = $this->getClientAccMapper();
            $widgetMapper = $this->getWidgetMapper();
            $widgets = $widgetMapper->findByReportId($reportId);
            foreach($widgets as $w) {
                
                $templateWidget = new \JimmyBase\Entity\TemplateWidget;
                $tempWidgetMapper = $this->getTemplateWidgetMapper();
                $templateWidget->setTemplateId($tempId);
                $templateWidget->setTitle($w->getTitle());
                $templateWidget->setComments($w->getComments());                
                $channelObj = $clientAcc->findById($w->getClientAccountId()); 
                 if ($channelObj == true) {                    
                    $templateWidget->setFields($w->getFields());                    
                    $templateWidget->setChannel($channelObj->getChannel());
                } else {
                    $templateWidget->setChannel(null); 
                }
                $templateWidget->setType($w->getType());
              
               
                $templateWidget->setCreated(date('Y/m/d H:i:s'));
                $tempWidgetMapper->insert($templateWidget);                    

            }
            return array("success" => true, "message" => "Template Saved");
        } catch(\Exception $e) {
            return array("success" => false, "message" => $e->getMessage());
        }
    }
    
    /**
     * 
     * @param type $user
     * @return Template
     */
    public function getUserTemplates($user){
       $globalTemplates = $this->getTemplateMapper()->findByType('jimmy');
       $userTemplates = $this->getTemplateMapper()->findByUser($user);

       $templateWidgetMapper = $this->getTemplateWidgetMapper();
       $tempArray = array();
       $tempArray[] =  array(                                  
                                  "templateName" => '--------------------------------------Jimmy Templates--------------------------------------',               
                                  
                                );
       foreach ($globalTemplates as $t) {           
           $templateWidget = $templateWidgetMapper->findByTemplateId($t->getId());       
           $widgets = array();
           
           foreach($templateWidget as $tw) {
               $title = $tw->getTitle();
               if (strlen($title)>30) {
                   $title = substr($title, 0, 30)."...";                  
               } 
               $widgets[] = array(
                                 "id" => $tw->getId(),
                                 "title" => $title,                                 
                                 "type" => $tw->getType(),
                                 "channel" => $tw->getChannel()
                                );
                
               
           }
           $tempArray[] =  array( "id" => $t->getId(),
                                  "type" => $t->getType(),
                                  "templateName" => $t->getTemplateName(),               
                                  "widgets" => $widgets
                                );
                          
       }   
       $tempArray[] =  array(                                  
                                  "templateName" => '---------------------------------------User Templates---------------------------------------',               
                                  
                                );
    
       foreach ($userTemplates as $t) {           
           $templateWidget = $templateWidgetMapper->findByTemplateId($t->getId());       
           $widgets = array();
           
           foreach($templateWidget as $tw) {
               $title = $tw->getTitle();
            //    if (strlen($title)>30) {
            //        $title = substr($title, 0, 30)."...";                  
            //    } 
               $widgets[] = array(
                                 "id" => $tw->getId(),
                                 "title" => $title,
                                 "type" => $tw->getType(),
                                 "channel" => $tw->getChannel()
                                );
                
               
           }
           $tempArray[] =  array( "id" => $t->getId(),
                                  "type" => $t->getType(),
                                  "templateName" => $t->getTemplateName(),
                                  "widgets" => $widgets
                                );
                          
       }       
       return $tempArray;  
    }
    
    public function useTemplate($templateId, $reportId,$campaign, $profile, $clients) {
        try {
            $template = $this->getTemplateWidgetMapper()
                             ->findByTemplateId($templateId);
            $widgetMapper = $this->getWidgetMapper();
            $clientAccounts = $this->getClientAccountsMapper();

            foreach ($template as $t) {
                   $channel = $t->getChannel();
                   $widgetId = $t->getId();
                // The notes widget will have no channel and client acc id.
                if (($channel == 'googleadwords' && $campaign->$widgetId) 
                       || ($channel == 'googleanalytics' && $profile->$widgetId->id) ) {
                   $widget = new \JimmyBase\Entity\Widget;
                   $widget->setReportId($reportId);
                   $widget->setTitle($t->getTitle());
                   $widget->setType($t->getType());    
                   $widget->setComments($t->getComments());
                   $widget->setOrder(0);
                   $widget->setStatus(1);
                   if(isset($channel)) {
                       
                    if ($channel == 'googleadwords') {
                        $fields = unserialize($t->getFields());
                        $fields['campaigns'] = $campaign->$widgetId;                       
                    } else if ($channel == 'googleanalytics') {
                        $fields = unserialize($t->getFields());
                        $fields['profile_id'] = $profile->$widgetId->id;
                        
                        $fields['segment'] = null;
                        $fields['currency'] = $profile->$widgetId->currency;
                    }
                    
                    $newFields = serialize($fields);                   
                    $widget->setFields($newFields);  
                    $widget->setClientAccountId($clients->$widgetId);
                   } else {
                     $widget->setClientAccountId(null);
                     $widget->setFields($t->getFields()); 
                   }
                   $widget->setCreated(date('Y/m/d H:i:s'));
                   $widget->setUpdated(date('Y/m/d H:i:s'));
                   $widgetMapper->insert($widget); 
                }
            }
           return array("success" => true, "reportId" => $reportId,
                        "message" => "Template widgets Loaded to the report"); 
        } catch (\Exception $e) {
            return array("success" => false, "message" => $e->getMessage());
        }
    }
    
    
    public function deleteTemplate($templateId) {
        $templateMapper = $this->getTemplateMapper();        
        $widgetMapper = $this->getTemplateWidgetMapper();
        $template = $templateMapper->findById($templateId);
        if($template->getType() == 'jimmy') {
             return array("success" => false, "message" => "Cannot delete a jimmy Template");
        }
        $widgetMapper->delete(null, "template_id =". $templateId);
        $templateMapper->delete($templateId);
        return array("success" => true, "message" => "Template Deleted");
       
    }
     /**
     * getTemplateMapper
     *
     * @return templateMapperInterface
     */
    public function getTemplateMapper()
    {       
        return $this->getServiceManager()->get('jimmybase_template_mapper');
       
    }
    
    public function getClientAccMapper()
    {
        return $this->getServiceManager()->get('jimmybase_clientaccounts_mapper');
    }
    /**
     * 
     * @return widgetMapperMapper
     */
    public function getWidgetMapper()
    {
        return $this->getServiceManager()->get('jimmybase_widget_mapper');
    }
    /**
     * 
     * @return templateMapper
     */
    public function getTemplateWidgetMapper()
    {
        return $this->getServiceManager()->get('jimmybase_template_widget_mapper');
    }
    
    /**
     * 
     * @return reportsMapper
     */
    public function getReportsMapper()
    {
        return $this->getServiceManager()->get('jimmybase_reports_mapper');
    }
    
    public function getClientAccountsMapper()
    {
        return $this->getServiceManager()->get('jimmybase_clientaccounts_mapper');
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