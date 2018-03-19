<?php

namespace JimmyBase\Mapper;

interface UserTokenInterface
{
    public function findByParent($user);    
	 	
    public function findById($id);

    public function insert($user);

    public function update($user);
	
    public function delete($user);
}
