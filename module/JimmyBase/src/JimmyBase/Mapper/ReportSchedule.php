<?php

namespace JimmyBase\Mapper;

use ZfcBase\Mapper\AbstractDbMapper;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Db\Sql\Predicate\PredicateSet;
use JimmyBase\Entity\ReportScheduleInterface as ReportShareEntityInterface;

class ReportSchedule extends AbstractDbMapper implements ReportScheduleInterface
{
    protected $tableName  = 'report_schedule';

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

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }
    
    


	public function scheduleExists($report_id,$email,$frequency)
    {
        $select = $this->getSelect()
                       ->where(array('report_id' => $report_id,'email' => $email,'frequency' => $frequency));

        $entity = $this->select($select)->current();
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }
    
    public function findByParentId($parent) {
          $select = $this->getSelect()
                       ->join('reports', 'report_schedule.report_id = reports.id',array())
                       ->join('client', 'client.client_id = reports.user_id',array())
                       ->where('client.parent ='.$parent);
          
        $adapter = $this->getDbAdapter();
        // Change the default timezone to UTC before executing the script
       $adapter->query("SET time_zone='+0:00'",$adapter::QUERY_MODE_EXECUTE);

        $entity = $this->select($select)->toArray();

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }


    public function findReportsToBeSentToday($date){

        $select = $this->getSelect()
                       ->where(array("(DATE_FORMAT(CONVERT_TZ(next_schedule_date  , timezone,  'UTC'),'%Y-%m-%d %H:%i') <= DATE_FORMAT(now(),'%Y-%m-%d %H:%i'))",
                                      "(DATE_FORMAT(CONVERT_TZ(next_schedule_date  , timezone,  'UTC'),'%Y-%m-%d %H:%i') > DATE_FORMAT(DATE_SUB(now(), INTERVAL 30 MINUTE), '%Y-%m-%d %H:%i'))"),   PredicateSet::COMBINED_BY_AND);

       
        $adapter = $this->getDbAdapter();
        // Change the default timezone to UTC before executing the script
       $adapter->query("SET time_zone='+0:00'",$adapter::QUERY_MODE_EXECUTE);

        $entity = $this->select($select)->toArray();

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }
    
    public function findReportsMissed(){

        $select = $this->getSelect()
                        ->where( array("DATE_FORMAT(CONVERT_TZ(next_schedule_date  , timezone,  'UTC'), '%Y-%m-%d %H:%i') <= DATE_FORMAT(CONVERT_TZ(now(), timezone,  'UTC') ,'%Y-%m-%d %H:%i') "
                                        . "AND "
                                        . " (DATE_FORMAT(CONVERT_TZ(next_schedule_date  , timezone,  'UTC'),'%Y-%m-%d %H:%i') < DATE_FORMAT(DATE_SUB(CONVERT_TZ(now(), timezone,  'UTC'), INTERVAL 30 MINUTE) ,'%Y-%m-%d %H:%i'))"
                                        . "AND frequency != 'one-off'"
                                      )
                                );
                       

        $entity = $this->select($select)->toArray();

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }



	public function fetchAll(){

	  $select = $this->getSelect()->order('updated DESC');

        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;

	}



    public function insert($entity, $tableName = 'report_schedule', HydratorInterface $hydrator = null)
    {
        $result = parent::insert($entity, $this->tableName, $hydrator);
        $entity->setId($result->getGeneratedValue());
        return $result;
    }

    public function update($entity, $where = null, $tableName = 'report_schedule', HydratorInterface $hydrator = null)
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