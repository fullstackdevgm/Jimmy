<?php

namespace Chat\Mapper;

use ZfcBase\Mapper\AbstractDbMapper;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Chat\Entity\MessageInterface as MessageEntityInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class Message extends AbstractDbMapper implements MessageInterface
{
    protected $tableName  = 'message';

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


	public function findByUserId($report_id)
    {
        $select = $this->getSelect()
                       ->where(array('user_id' => $report_id))
                       ->order('order ASC')
                       ->order('created ASC');

        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }


    public function fetchWidgetMessage($widget_id){
        $subQry = new Select('user_meta');

        $subQry->columns(array('value'))
               ->where(array('user_meta.key'     => 'logo',"user_meta.user_id" => new \Zend\Db\Sql\Expression("message.user_id")));

        $select = $this->getSelect()
                ->columns(array('*','logo' =>  new \Zend\Db\Sql\Expression('?', array($subQry))))
                //->join("message_users",  'message.id = message_users.msg_id',array(),'left')
                ->join("message_widget", 'message.id = message_widget.msg_id',array(),'left')
                ->join("user", 'user.user_id = message.user_id',array('*'),'left')
              //  ->join("user_meta", 'user_meta.user_id = message.user_id',array('*'),'left')
                ->where(array('message_widget.widget_id' => $widget_id))
                ->group('message.id')
                ->order('message.id ASC');

        // echo $select->getSqlString();
        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;

    }


	public function fetchAll(){

	    $select = $this->getSelect();
        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;

	}


    public function insert($entity, $tableName = null, HydratorInterface $hydrator = null)
    {

        if($tableName)
           $tbl = $tableName;
        else
           $tbl = $this->tableName;

        $result = parent::insert($entity, $tbl, $hydrator);

        $entity->setId($result->getGeneratedValue());
        return $result;
    }

    public function update($entity, $where = null, $tableName = null, HydratorInterface $hydrator = null)
    {
        if (!$where) {
            $where = 'id = ' . $entity->getId();
        }

		if(!$tableName)
		   $tableName = $this->tableName;

        return parent::update($entity, $where, $this->tableName, $hydrator);
    }

	public function delete($id,$where=null, $tableName = null)
    {

        if (!$where) {
            $where = 'id = ' . $id;
        }

        if(!$tableName)
           $tableName = $this->tableName;

        # Delete from widget
        if(parent::delete($where,$tableName))
           return true;
        else
           return false;

    }

}
