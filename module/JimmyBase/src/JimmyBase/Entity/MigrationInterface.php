<?php

namespace JimmyBase\Entity;

interface MigrationInterface
{
    /**
     * Get id.
     *
     * @return int
     */
    public function getId();

    /**
     * Set id.
     *
     * @param int $id
     * @return MigrationInterface
     */
    public function setId($id);
	/**
     * Get UserId.
     *
     * @return int
     */
    public function getUserId();

    /**
     * Set UserId.
     *
     * @param string $name
     * @return MigrationInterface
     */
    public function setUserId($name);



    /**
     * Get created.
     *
     * @return date
     */
    public function getCreated();

    /**
     * Set created.
     *
     * @param date $created
     * @return MigrationInterface
     */
    public function setCreated($created);

}
