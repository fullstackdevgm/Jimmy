<?php

namespace Chat\Mapper;

interface MessageInterface
{

    public function findById($id);

    public function findByUserId($user_id);

	public function fetchAll();

    public function insert($report);

    public function update($report);

}
