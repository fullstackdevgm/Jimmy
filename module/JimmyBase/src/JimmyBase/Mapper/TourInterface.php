<?php

namespace JimmyBase\Mapper;

interface TourInterface
{

    public function findByTourName($tourName);
	
	public function fetchAll();

    public function insert($report);

    public function update($report);
    
}
