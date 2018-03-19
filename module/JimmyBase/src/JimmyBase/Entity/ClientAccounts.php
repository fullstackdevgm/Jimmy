<?php

namespace JimmyBase\Entity;


class ClientAccounts implements ClientAccountsInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $client_id;

    /**
     * @var int
     */
    //protected $user_token_id;
    
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $channel;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $account_id;

    /**
     * @var int
     */
    protected $api_auth_info;
    
    

    const GOOGLE_ADWORDS     = 'googleadwords';

    const GOOGLE_ANALYTICS   = 'googleanalytics';

    const FACEBOOK_ADS       = 'facebook';

    const BING_ADS           = 'bingads';

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
     * @return ClientAccountInterface
     */
    public function setId($id)
    {
        $this->id = (int) $id;
        return $this;
    }


    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * Set name.
     *
     * @param int $name
     * @return ClientAccountInterface
     */
    public function setClientId($client_id)
    {
        $this->client_id = (int) $client_id;
        return $this;
    }


    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param int $name
     * @return ClientAccountInterface
     */
    public function setName($name)
    {
        $this->name =  $name;
        return $this;
    }

    /**
     * Get account.
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set account.
     *
     * @param string $account
     * @return ClientAccountInterface
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
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
     * @return ClientAccountInterface
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

     /**
     * Get account_id.
     *
     * @return string
     */
    public function getAccountId()
    {
        return $this->account_id;
    }

    /**
     * Set account_id.
     *
     * @param string $account_id
     * @return ClientAccountInterface
     */
    public function setAccountId($account_id)
    {
        $this->account_id = $account_id;
        return $this;
    }


    /**
     * Get api_auth_info.
     *
     * @return string
     */
    public function getApiAuthInfo()
    {
        return $this->api_auth_info;
    }

    /**
     * Set api_auth_info.
     *
     * @param string $api_auth_info
     * @return ClientAccountInterface
     */
    public function setApiAuthInfo($api_auth_info)
    {
         $this->api_auth_info = $api_auth_info;
        return $this;
    }
    
     /**
     * Get user_token_id.
     *
     * @return int
     */
    public function getUserTokenId()
    {
        return $this->user_token_id;
    }

    /**
     * Set id.
     *
     * @param int $user_token_id
     * @return ClientAccountInterface
     */
    public function setUserTokenId($user_token_id)
    {
        $this->user_token_id = (int) $user_token_id;
        return $this;
    }

}
