<?php

namespace JimmyBase\Mapper;

use ZfcBase\Mapper\AbstractDbMapper;
use Zend\Stdlib\Hydrator\HydratorInterface;
use JimmyBase\Entity\ReportsInterface as ReportEntityInterface;

class Reports extends AbstractDbMapper implements ReportsInterface
{
    protected $tableName  = 'reports';

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

    public function findByIdToArray($id)
    {
        if(!$id)
          return false;

        $select = $this->getSelect()
                       ->where(array('id' => $id));

        $entities = $this->select($select)->toArray();
        $entity = $entities[0];
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }

	public function findByUserId($client_id)
    {
        $select = $this->getSelect()
                       ->where(array('user_id' => $client_id))
                       ->order('reports.created DESC');

        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }

    public function findByAgency($agency_id,$report_id=null){

         $select = $this->getSelect()
                        ->columns(array('*'))
                        ->join("client", 'reports.user_id = client.client_id',array('client_id'),'left')
                        ->where(array('client.parent' => $agency_id))
                        ->order('reports.created DESC');


        if($report_id){
            $select->where(array('reports.id' => $report_id));
            $entity = $this->select($select)->current();
        } else
            $entity = $this->select($select);

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }

    public function findByAgencyRecent($agency_id){

         $select = $this->getSelect()
                        ->columns(array('*'))
                        ->join("client", 'reports.user_id = client.client_id',array('client_id'),'left')
                        ->where(array('client.parent' => $agency_id))
                        ->order('reports.created DESC')
                        ->limit(10);

        $entity = $this->select($select);

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }


    public function fetchShared($user_id){
        $select = $this->getSelect()
                    ->join("reports_share", 'reports_share.report_id = reports.id',array(),'left')
                    ->join("client", 'client.client_id = reports.user_id',array(),'left')
                    ->join("user", 'user.user_id = client.parent',array(),'left')
                    ->where(array('user.state'   => 1))
                    ->where(array('reports_share.user_id' => $user_id));

        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
     return $entity;
    }

    public function isSharedWithMe($report_id,$user_id){
        $select = $this->getSelect()
                    ->join("reports_share", 'reports_share.report_id = reports.id',array(),'left')
                    ->join("client", 'client.client_id = reports.user_id',array(),'left')
                    ->join("user", 'user.user_id = client.parent',array(),'left')
                    ->where(array('user.state'   => 1))
                    ->where(array('reports_share.user_id' => $user_id))
                    ->where(array('reports.id' => $report_id));

        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
     return $entity;
    }

    public function isShared($report_id){
        $select = $this->getSelect()
                    ->join("reports_share", 'reports_share.report_id = reports.id',array(),'inner')
                    ->where(array('reports.id' => $report_id));

        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
     return $entity;
    }

    public function isScheduled($report_id,$user_id){
        $select = $this->getSelect()
                   ->join("report_schedule", 'report_schedule.report_id = reports.id',array(),'inner')
                   ->where(array('reports.id' => $report_id));
         //echo $select->getSqlString();

        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
     return $entity;
    }


    public function countAgencyReports($id){

    	$select = $this->getSelect()
					   ->columns(array('total' => new \Zend\Db\Sql\Expression('COUNT(*)')))
    	    		   ->join("client", 'reports.user_id = client.client_id',array('client_id'),'left')
    				   ->where(array('client.parent' => $id));


    	echo $select->getSqlString();

    	$entity = $this->select($select);
    	echo '<pre>';print_r($entity);exit;

    	$this->getEventManager()->trigger('find', $this, array('entity' => $entity));
    	return $entity;
    }

	public function findByUserIds($client_ids)
    {
		$predicate = new \Zend\Db\Sql\Predicate\Predicate();
        $select = $this->getSelect()
                       ->where($predicate->in('user_id',$client_ids));
        $entity = $this->select($select);

		//echo $select->getSqlString();

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }

	public function findByReportIds($reportIds)
    {
		$predicate = new \Zend\Db\Sql\Predicate\Predicate();
        $select = $this->getSelect()
                       ->where($predicate->in('id',$reportIds));
        $entity = $this->select($select);


        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }

	public function fetchAll(){

	    $select = $this->getSelect()->order('updated DESC');

        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;

	}




    public function insert($entity, $tableName = null, HydratorInterface $hydrator = null)
    {

        $result = parent::insert($entity, 'reports', $hydrator);
        $entity->setId($result->getGeneratedValue());
        return $result;
    }

    public function update($entity, $where = null, $tableName = null, HydratorInterface $hydrator = null)
    {
        if (!$where) {
            $where = 'id = ' . $entity->getId();
        }

        return parent::update($entity, $where, $tableName, $hydrator);
    }

	public function delete($id,$where=null, $tableName = null)
    {
		echo $where;
		if (!$where) {
			$where = 'id = ' . $id;
		}

		# Delete from user table and clients table
		if(parent::delete($where,$this->tableName))
		   return true;
		else
		   return false;

	}

    public function deletez($id,$where=null, $tableName = null)
    {

        if (!$where) {
            $where = 'client_id = ' . $id;
        }

        # Delete from user table and clients table
        if(parent::delete($where,$this->tableNameClient))
           return parent::delete($where, $tableName);
        else
           return false;

    }

    /**
     * Finds the list of number of reports created
     * by the user
     * @param $userId ID of the user
     * @return number of reports created
     **/
    public function getCount($userId) {
        $reports = $this->findByUserId($userId);
        $reportsArray = $reports->toArray();
        $reportsCount = count($reportsArray);
        return $reportsCount;
    }

    /**
     * Finds the number of paid reports created
     * by the user
     * @param $userId ID of the user
     * @return number of reports created
     **/
    public function getPaidCount($userId) {
        $select = $this->getSelect()
                        ->columns(array('*'))
                        ->join("client", 'reports.user_id = client.client_id',array('client_id'),'left')
                        ->where(array(
                            'client.parent' => $userId,
                            'reports.paid' => 1
                            ));
        $reports = $this->select($select);
        $reportsArray = $reports->toArray();
        $reportsCount = count($reportsArray);
        return $reportsCount;
    }
}