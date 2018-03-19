<?php

namespace JimmyBase\Mapper;

interface UserInterface
{
    public function findByEmail($email);

    public function findByUsername($username);

    public function findById($id);
    
	public function getMeta($id,$key);

    public function insert($user);

    public function update($user);
}
