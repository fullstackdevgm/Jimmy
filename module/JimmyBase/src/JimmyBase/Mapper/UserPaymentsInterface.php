<?php

namespace JimmyBase\Mapper;

interface UserPaymentsInterface
{
    public function findByUserId($user_id);

    public function findById($id);

    public function insert($user_payment);

    public function update($user_payment);
	
    public function delete($user_payment_id);
}
