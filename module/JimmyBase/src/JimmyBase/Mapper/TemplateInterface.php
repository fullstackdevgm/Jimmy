<?php

namespace JimmyBase\Mapper;

interface TemplateInterface
{

    public function findByTemplateName($tourName,$userId);
    
    public function findByUser($userId);
    
    public function fetchAll();

    public function insert($report);

    public function update($report);
    
}
