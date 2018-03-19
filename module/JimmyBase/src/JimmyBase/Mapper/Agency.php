<?php

namespace JimmyBase\Mapper;

use ZfcBase\Mapper\AbstractDbMapper;
use Zend\Stdlib\Hydrator\HydratorInterface;
use JimmyBase\Entity\AgencyInterface as AgencyEntityInterface;
use JimmyBase\Entity\User as UserEntity;

class Agency extends AbstractDbMapper implements AgencyInterface
{
    protected $tableName  			= 'user';
	protected $type 	    		= 'agency';
	
    public function findByEmail($email)
    {	
        $select = $this->getSelect()					   
                       ->where(array('type'  => $this->type))
                       ->where(array('email' => $email))
					   ->where(array('state' => 1));

		
        $entity = $this->select($select)->current();  
		
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
		
		return $entity;
    }

 	public function findByClientId($client_id)
    {
        $select = $this->getSelect()	
        				->join("client", 'user.user_id = client.parent',array())				   
                        ->where(array('client_id' => $client_id));

        $entity = $this->select($select)->current();
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }

    public function findById($id)
    {
        $select = $this->getSelect()
						->where(array('type'  => $this->type))
                        ->where(array('user.user_id' => $id));

        $entity = $this->select($select)->current();
	
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }

 	
	
	public function fetchAll($enabled=1){
	
	  $select = $this->getSelect()					  
	  				   ->where(array('type'  => $this->type));
                       //->where(array('state' => $enabled));
		
        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
	}
	
	
	public function fetchAllClients($parent){
	  $select = $this->getSelect()					  
	  				   //->from("client")    	
					   ->where(array('type'  => 'client'))
                       ->where(array('state' => 1))
                       ->where(array('parent' => $parent));

	        $entity = $this->select($select);
		
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
	}
	
	public function fetchAllCoworkers($parent){
	  $select = $this->getSelect()					  
					 ->join("user_meta", 'user_meta.user_id = user.user_id',array('value'),'left')
					 //->where(array('state'  => UserEntity::ACTIVE))
                     ->where(array('type' => UserEntity::COWORKER))
                     ->where(array('user_meta.key' => 'parent'))
					 ->where(array('user_meta.value' => $parent));

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
            $where = 'user_id = ' . $entity->getId();
        }
		

        parent::update($entity, $where, $tableName, $hydrator);
    	
	}
	
	
    public function delete($id,$where=null, $tableName = null)
    {
		
		if (!$where) {
			$where = 'user_id = ' . $id;
		}
		
		
		parent::delete($where,$this->tableNameClient);	    
	}
}
