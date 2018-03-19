<?php

namespace JimmyBase\Entity;

interface TourInterface
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
     * @return TourInterface
     */
    public function setId($id);

    /**
     * Get tourName.
     *
     * @return string
     */
    public function getTourName();

    /**
     * Set tourName.
     *
     * @param string $tourName
     * @return TourInterface
     */
    public function setTourName($tourName);

}
