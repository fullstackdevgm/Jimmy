<?php

namespace Admin\Mapper;

use Zend\Stdlib\Hydrator\HydratorInterface;

use ZfcBase\Mapper\AbstractDbMapper;
use JimmyBase\Entity\AdminUserInterface as AdminUserEntityInterface;

class AdminUser extends AbstractDbMapper implements AdminUserInterface
{
    protected $tableName  = 'user';
	protected $type 	  = 'admin';

    public function findByEmail($email)
    {
        $select = $this->getSelect()		               
                       ->where(array('type'  => $this->type))
                       ->where(array('email' => $email));
					   
        $entity = $this->select($select)->current();
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
		
		return $entity;
    }

    public function findByUsername($username)
    {
        $select = $this->getSelect()
                       ->where(array('type'  => $this->type))
                        ->where(array('username' => $username));

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

        return parent::update($entity, $where, $tableName, $hydrator);
    }
}
