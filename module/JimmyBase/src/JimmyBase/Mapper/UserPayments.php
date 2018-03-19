<?php

namespace JimmyBase\Mapper;

use Zend\Stdlib\Hydrator\HydratorInterface;
use ZfcBase\Mapper\AbstractDbMapper;
use JimmyBase\Entity\UserPaymentsInterface as UserPaymentsEntityInterface;

class UserPayments extends AbstractDbMapper  implements UserPaymentsInterface
{
    protected $tableName      = 'user_payments';
    
    public function findByUserId($user_id)
    {	
		$select = $this->getSelect()
                       ->where(array('user_id' => $user_id))
                       ->order(array('date asc' ));
					   
			 
        $entity = $this->select($select)->current();  
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        
        return $entity;
    }
   
    public function fetchAllByUserId($user_id){
        $select = $this->getSelect()
                       ->where(array('user_id' => $user_id))
                       ->order(array('date desc'));

        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));

        $result = array();
        foreach ($entity as $key) {
            $row = array(
                'id'=>$key->getId(),
                'amount'=>$key->getAmount(),
                'status'=>$key->getStatus(),
                'currency'=>$key->getCurrency(),
                'date'=>$key->getDate(),
                'comment'=>$key->getComments(),
                'transaction_id'=>$key->getTransId(),
                'processoe'=>$key->getProcessor()
                );
            array_push($result, $row);
        }

        return $result;

    }

    public function findById($id)
    {
        $select = $this->getSelect()
                        ->where(array('id' => $id));

        $entity = $this->select($select)->current();
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }

	

    public function insert($entity, $tableName = null, HydratorInterface $hydrator = null)
    {
        $result = parent::insert($entity, $this->tableName, $hydrator);
        $entity->setId($result->getGeneratedValue());
        return $result;
    }

    public function update($entity, $where = null, $tableName = null, HydratorInterface $hydrator = null)
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
	

				
		# Delete from widget
		if(parent::delete($where,$this->tableName))
		   return true;
		else
		   return false;   
		    
	}
	
	
	
}
