<?php

namespace JimmyBase\Mapper;

interface ClientInterface
{
    public function findByEmail($email);    
	
    public function findByAdwordsClientId($adwords_client_id);
	
    public function findById($id);

    public function insert($client);

    public function update($client);
	
    public function delete($client);
}
