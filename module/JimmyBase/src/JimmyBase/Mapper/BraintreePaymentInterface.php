<?php

namespace JimmyBase\Mapper;

interface BraintreePaymentInterface
{
    public function fetchAll();
    
    public function insert($user);

    public function update($user);
	
    public function delete($user);
}
