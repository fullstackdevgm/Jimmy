<?php

namespace JimmyBase\Mapper;

use ZfcBase\Mapper\AbstractDbMapper;
use Zend\Stdlib\Hydrator\HydratorInterface;
use JimmyBase\Entity\PackageInterface as PackageEntityInterface;

class Package extends AbstractDbMapper implements PackageInterface
{
    protected $tableName  			= 'package';

	const ENABLED 					= 1;
	const DISABLED 					= 0;
	const TYPE_PAID 				= 'paid';
	const TYPE_FREE 				= 'free';

    public function findById($id,$status = 1)
    {
        $select = $this->getSelect()
                        ->where(array('id'      => $id))
                        ->where(array('status'  => (string)$status));

        $entity = $this->select($select)->current();

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }


    public function findByTitle($title,$status = 1)
    {
        $select = $this->getSelect()
                        ->where(array("LOWER(title) = '".$title."'"))
                        ->where(array('status'       => (string)$status));

        $entity = $this->select($select)->current();
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }


    public function findByIdToArray($id,$status = 1)
    {
        if(!$id)
          return false;

        $select = $this->getSelect()
                        ->where(array('id'      => $id))
                        ->where(array('status'  => (string)$status));


        $entities = $this->select($select)->toArray();
        $entity = $entities[0];
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }
	public function fetchAll($status = 1){

	  $select = $this->getSelect()
                       ->where(array('status' => (string)$status));

        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
	}


	public function fetchFreeTrial(){
	   $select = $this->getSelect()
                       ->where(array('is_free_trial' => 1));

        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
	}

	public function fetchStandard(){
        $select = $this->getSelect()
                       ->where(array('type'          => 'standard'))
                       ->where(array('is_free_trial' => '0'))
                       ->order('price ASC');

        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }

    public function fetchUnlimited(){
        $select = $this->getSelect()                       
                       ->where(array('title' => 'Pay as you go'))
                       ->order('price ASC');
                      

        $entities = $this->select($select)->toArray();

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entities;
    }
    
    public function fetchNonFree(){
        $select = $this->getSelect()                       
                       ->where(array('is_free_trial' =>0))
                       ->order('price ASC');
                      

        $entities = $this->select($select)->toArray();

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entities;
    }


	public function fetchCustom(){
	 	$select = $this->getSelect()
                       ->where(array('type' 	     => 'custom'))
                       ->where(array('is_free_trial' =>'0'));

        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
	}


	public function fetchAllByType($type = 'standard'){

	   $select = $this->getSelect()
                       ->where(array('type' => $type))
                       ->where(array('is_free_trial' => 0));

        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
	}


	public function fetchAllUpgradeable($template_allowed){

	    $select = $this->getSelect()
                       ->where(array("type = 'standard'","templates_allowed > ".$template_allowed,"templates_allowed is NULL ","is_free_trial='0'"));

        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
	}


	public function fetchAllByStatus($status = 1){

	  $select = $this->getSelect()
                       ->where(array('status' => (string)$status));

        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
	}


    public function insert($entity, $tableName = null, HydratorInterface $hydrator = null)
    {

        $result = parent::insert($entity, $tableName, $hydrator);
        $entity->setId($result->getGeneratedValue());

        return $result;
    }

    public function update($entity, $where = null, $tableName = null, HydratorInterface $hydrator = null)
    {
        if (!$where) {
            $where = 'id = ' . $entity->getId();
        }


        parent::update($entity, $where, $tableName, $hydrator);

	}

    public function delete($id,$where=null, $tableName = null)
    {

		if (!$where) {
			$where = 'id = ' . $id;
		}


		parent::delete($where,$this->tableNameClient);
	}
}
