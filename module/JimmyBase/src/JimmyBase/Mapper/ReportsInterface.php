<?php

namespace JimmyBase\Mapper;

interface ReportsInterface
{

    public function findById($id);
	
    public function findByUserIds($userIds);
	   
	public function findByReportIds($reportIds);

	public function fetchAll();

    public function insert($report);

    public function update($report);
}
