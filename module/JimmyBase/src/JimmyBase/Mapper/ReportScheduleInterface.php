<?php

namespace JimmyBase\Mapper;

interface ReportScheduleInterface
{

    public function findById($id);
	
    public function findByUserId($user_id);
    
	public function findByReportId($report_id);
	
    public function scheduleExists($report_id,$email,$frequency);
	
	public function fetchAll();

    public function insert($report);

    public function update($report);
	
    public function delete($report);
}
