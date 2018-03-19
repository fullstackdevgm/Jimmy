<?php

namespace JimmyBase\Mapper;

use ZfcBase\Mapper\AbstractDbMapper;
use Zend\Stdlib\Hydrator\HydratorInterface;
use JimmyBase\Entity\ReportShareInterface as ReportShareEntityInterface;

class ReportShare extends AbstractDbMapper implements ReportShareInterface
{
    protected $tableName  = 'reports_share';

    public function findById($id)
    {   
        if(!$id)
          return false;
          
        $select = $this->getSelect()
                       ->where(array('id' => $id));

        $entity = $this->select($select)->current();

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }


    public function findByUserId($user_id,$status = null)
    {

        $select = $this->getSelect()
                       ->where(array('user_id' => $user_id));
        
        if(isset($select))
            $select->where(array('status'=>$status));


        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }
    
    public function findByReportId($report_id)
    {
        $select = $this->getSelect()
                       ->where(array('report_id' => $report_id));
        $entity = $this->select($select);
        //$select->getSqlString();
        
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }
    

    public function sharingExists($report_id,$user_id)
    {
        $select = $this->getSelect()
                       ->where(array('user_id' => $user_id,'report_id' => $report_id));
                       
        $entity = $this->select($select)->current();
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }
    
    
    
  
    
    public function fetchAll(){
    
      $select = $this->getSelect()->order('updated DESC');

        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    
    }
    
    
    
    public function insert($entity, $tableName = 'reports_share', HydratorInterface $hydrator = null)
    {
    
        $result = parent::insert($entity, $this->tableName, $hydrator);
        $entity->setId($result->getGeneratedValue());
        return $result;
    }

    public function update($entity, $where = null, $tableName = 'reports_share', HydratorInterface $hydrator = null)
    {
        if (!$where) {
            $where = 'id = ' . $entity->getId();
        }

        return parent::update($entity, $where, $this->tableName, $hydrator);
    }
    
    public function delete($id,$where=null, $tableName = null)
    {
        
        if (!$where) {
            $where = 'id = ' . $id;
        }
        
        # Delete from user table and clients table
        if(parent::delete($where,$this->tableName))
           return true;
        else
           return false;   
            
    }
}
