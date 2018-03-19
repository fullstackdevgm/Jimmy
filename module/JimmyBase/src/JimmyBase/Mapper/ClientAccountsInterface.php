<?php

namespace JimmyBase\Mapper;

interface ClientAccountsInterface
{
   
    public function findById($id);
    
    public function findByClientId($client_id);

    public function findByChannel($client_id,$channel);
    
    public function findByAccountId($id,$account_id);

	public function fetchAll($enabled);

    public function insert($entity);

    public function update($entity);

    public function delete($id);
}
