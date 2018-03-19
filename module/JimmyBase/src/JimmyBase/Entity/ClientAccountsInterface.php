<?php

namespace JimmyBase\Entity;


interface ClientAccountsInterface 
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
     * @return ClientAccountInterface
     */
    public function setId($id);
 

    /**
     * Get client_id.
     *
     * @return int
     */
    public function getClientId();

    /**
     * Set client_id.
     *
     * @param int $client_id
     * @return ClientAccountInterface
     */
    public function setClientId($client_id);

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
     * @return ClientAccountInterface
     */
    public function setName($name);
    
    /**
     * Get channel.
     *
     * @return string
     */
    public function getChannel();

    /**
     * Set channel.
     *
     * @param string $channel
     * @return ClientAccountInterface
     */
    public function setChannel($channel);

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
     * @return ClientAccountInterface
     */
    public function setEmail($email);

     /**
     * Get account_id.
     *
     * @return string
     */
    public function getAccountId();

    /**
     * Set account_id.
     *
     * @param string $account_id
     * @return ClientAccountInterface
     */
    public function setAccountId($account_id);
   
    
    /**
     * Get api_auth_info.
     *
     * @return string
     */
    public function getApiAuthInfo();

    /**
     * Set api_auth_info.
     *
     * @param string $api_auth_info
     * @return ClientAccountInterface
     */
    public function setApiAuthInfo($api_auth_info);
    
       /**
     * Get user_token_id.
     *
     * @return int
     */
    public function getUserTokenId();
   
    /**
     * Set id.
     *
     * @param int $user_token_id
     * @return ClientAccountInterface
     */
    public function setUserTokenId($user_token_id);
    

}
