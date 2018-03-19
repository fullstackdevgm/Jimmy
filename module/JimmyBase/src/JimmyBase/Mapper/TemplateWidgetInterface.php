<?php

namespace JimmyBase\Mapper;

interface TemplateWidgetInterface
{

    public function findByTemplateId($templateId);
        
    public function fetchAll();

    public function insert($report);

    public function update($report);
    
    
}
