<?php

namespace JimmyBase\Entity;

interface VisitedTourInterface
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
     * @return VisitedTourInterfcace
     */
    public function setId($id);

    /**
     * Get tourId.
     *
     * @return int
     */
    public function getTourId();

    /**
     * Set tourId.
     *
     * @param int $tourId
     * @return VisitedTourId
     */
    public function setTourId($tourId);
    
     /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId();

    /**
     * Set userId.
     *
     * @param int $userId
     * @return VisitedTourId
     */
    public function setUserId($userId);

}
