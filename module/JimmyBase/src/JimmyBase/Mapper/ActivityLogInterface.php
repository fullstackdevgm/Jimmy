<?php

namespace JimmyBase\Mapper;

interface ActivityLogInterface
{
   
    public function fetchAll();
    
    public function findByParent($parent, $limit);

    public function insert($activityLog);

    public function update($activityLog);
	
    public function delete($activityLog);
}
