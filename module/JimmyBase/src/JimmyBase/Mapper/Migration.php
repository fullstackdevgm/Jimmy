<?php

namespace JimmyBase\Mapper;

use ZfcBase\Mapper\AbstractDbMapper;
use JimmyBase\Entity\MigrationInterface as MigrationEntityInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;

class Migration extends AbstractDbMapper implements MigrationInterface
{
    protected $tableName  = 'migration';

    public function findByUserId($id)
    {
        $select = $this->getSelect()
                        //->join("client_accounts", 'client_accounts.client_id = client.client_id',array('*'),'left')
                        ->where(array('migration.user_id' => $id));

        $entity = $this->select($select)->current();

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }
    
   

   

    public function fetchAll()
    {
        $select = $this->getSelect();				          
        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
	}


	


    public function insert($entity, $tableName = null, HydratorInterface $hydrator = null)
    {
       // var_dump($hydrator);
        $result = parent::insert($entity, $tableName, $hydrator);
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

		if (!$where) {
			$where = 'client_id = ' . $id;
		}

		# Delete from clients table
		return parent::delete($where,$this->tableName);
	}
}