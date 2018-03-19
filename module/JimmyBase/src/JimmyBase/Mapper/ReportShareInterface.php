<?php

namespace JimmyBase\Mapper;

interface ReportShareInterface
{

    public function findById($id);
	
    public function findByUserId($user_id);
    
	public function findByReportId($report_id);
	
    public function sharingExists($user_id,$report_id);
	
	public function fetchAll();

    public function insert($report);

    public function update($report);
	
    public function delete($report);
}
