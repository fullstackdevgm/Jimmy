<?php

namespace JimmyBase\Entity;


class Client implements ClientInterface
{
    /**
     * @var int
     */
    protected $id;
	
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $email;
	
    /*
     * @var accounts
     */
    protected $accounts;
    
    protected $adwords_client_id;

	/**
	 * @var int
	 */
	 
	protected $parent;

    /**
     * @var date
     */
    protected $created;
    
    /**
     * @var date
     */
    protected $updated;
    
    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    

    /**
     * Set id.
     *
     * @param int $id
     * @return ClientInterface
     */
    public function setId($id)
    {
        $this->id = (int) $id;
        return $this;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set username.
     *
     * @param string $username
     * @return ClientInterface
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
   
     /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email.
     *
     * @param string $email
     * @return ClientInterface
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }
   
    
    public function  getAccounts(){
        return $this->accounts;
    }


    public function setAccounts(array $accounts=null){
        $this->accounts = $accounts;
        return $this;
    }
   
	/**
     * Get parent.
     *
     * @return int
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set parent.
     *
     * @param string $parent
     * @return ClientInterface
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }   

    /**
     * Get parent.
     *
     * @return int
     */
    public function getAdwordsClientId()
    {
        return $this->adwords_client_id;
    }

    /**
     * Set parent.
     *
     * @param string $parent
     * @return ClientInterface
     */
    public function setAdwordsClientId($adwords_client_id)
    {
        $this->adwords_client_id = $adwords_client_id;
        return $this;
    }   
	
	
    /**
     * Get logo.
     *
     * @return int
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set logo.
     *
     * @param string $logo
     * @return ClientInterface
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
        return $this;
    }


    /**
     * Get created.
     *
     * @return date
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set created.
     *
     * @param date $created
     * @return ClientInterface
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }


    /**
     * Get updated.
     *
     * @return date
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set updated.
     *
     * @param date $updated
     * @return ClientInterface
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
        return $this;
    }
    
}
