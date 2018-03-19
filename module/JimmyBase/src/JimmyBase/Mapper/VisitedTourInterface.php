<?php

namespace JimmyBase\Mapper;

interface VisitedTourInterface
{

    public function findVisited($tourId, $userId);
	
    public function fetchAll();

    public function insert($entity);

    
}
