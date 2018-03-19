<?php

namespace JimmyBase\Mapper;

interface WidgetInterface
{

    public function findById($id);
	
    public function findByReportId($report_id);
    
    public function findByClientAccountId($client_account_id);
	
	public function fetchAll();

    public function insert($report);

    public function update($report);
    
}
