<?php
namespace JimmyBase\Mapper;

use Zend\StdLib\HydratorInterface;

use ZfcBase\Mapper\AbstractDbMapper;
use JimmyBase\Mapper\UserCancelLogInterface;

class UserCancelLog extends AbstractDbMapper implements UserCancelLogInterface {
	
	protected $tableName = 'user_cancel_log';

	public function insert($entity, $tableName = null, HydratorInterface $hydrator = null) {
	if($tableName)
           $tbl = $tableName;
        else
           $tbl = $this->tableName;

        $result = parent::insert($entity, $tbl, $hydrator);

        $entity->setId($result->getGeneratedValue());
        return $result;
	}

}