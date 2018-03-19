<?php

namespace JimmyBase\Mapper;

interface AgencyInterface
{
    public function findByEmail($email);    
	 	
    public function findById($id);

    public function insert($user);

    public function update($user);
	
    public function delete($user);
}
