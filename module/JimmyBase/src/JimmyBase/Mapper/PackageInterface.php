<?php

namespace JimmyBase\Mapper;


interface PackageInterface
{

    public function findById($id);
    
	public function fetchAll($status);

	public function fetchAllByType($type);

	public function fetchAllByStatus($status);

    public function insert($user);

    public function update($user);
	
    public function delete($user);

}
