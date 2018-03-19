<?php

namespace JimmyBase\Entity;

interface UserCancelLogInterface {

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
     * @return UserInterface
     */
    public function setId($id);

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email.
     *
     * @param string $email
     * @return UserInterface
     */
    public function setEmail($email);

    /**
     * Get displayName.
     *
     * @return string
     */
    public function getDisplayName();

    /**
     * Set displayName.
     *
     * @param string $displayName
     * @return UserInterface
     */
    public function setDisplayName($displayName);

    /**
     * Get name.
     *
     * @return string
     */
    public function getName();

    /**
     * Set name.
     *
     * @param string $name
     * @return UserInterface
     */
    public function setName($name);

    /**
     * Get type.
     *
     * @return string
     */
    public function getType();

    /**
     * Set type.
     *
     * @param string $type
     * @return UserInterface
     */
    public function setType($type);

    /**
     * Get created.
     *
     * @return datetime
     */
    public function getCreated();

    /**
     * Set created.
     *
     * @param datetime $created
     * @return UserInterface
     */
    public function setCreated($created);

    /**
     * Get deleted.
     *
     * @return datetime
     */
    public function getDeleted();

    /**
     * Set deleted.
     *
     * @param datetime $deleted
     * @return UserInterface
     */
    public function setDeleted($deleted);

}