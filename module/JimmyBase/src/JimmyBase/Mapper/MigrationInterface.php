<?php

namespace JimmyBase\Mapper;

interface MigrationInterface
{
  
	
    public function findByUserId($id);
    
    public function fetchAll();

    public function insert($migration);

    public function update($migration);
	
    public function delete($migration);
}
