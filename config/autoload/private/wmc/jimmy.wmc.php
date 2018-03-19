<?php
/**
 * Google Configuration
 *
 * If you have a ./config/autoload/ directory set up for your project, you can
 * drop this config file in it and change the values as you wish.
 */

$jimmy_settings  = array(
	'baseurl'   => 'http://jimmy.webmarketers.com.au',
	'clienturl' => 'http://reports.jimmy.webmarketers.com.au',
        'title'    => 'Webmarketers'
);



$google_settings = array(
    /**
     * Google Client ID
     *
     * Please specify a Google Client ID
     */
    'client_id' => '272310704029-ptkvb24cq75fqs7505afghefr0aci0pj.apps.googleusercontent.com',

    /**
     * Google Secret
     *
     * Please specify a Google Secret
     */
    'client_secret'   => '6GgFigLy07hU2pHzOPq5MEQz',

    /**
     * Developer Token
     *
     * Please specify a Google Secret
     */
    'developer_token' => 'B9EreNAeyihsRf9pyMCMmw',

    /**
     * User Agent
     *
     * Please specify a Google Secret
     */
    'user_agent'      => 'wmc',

	/**
     * User Agent
     *
     * Please specify a Google Secret
     */
    'redirect_uri'      => 'http://jimmy.webmarketers.com.au/authcallback',

    /**
     * End of Jimmy configuration
     */
);




/**
 * You do not need to edit below this line
 */
return array(
	'jimmy-config'		  => $jimmy_settings,
    'google-api-config'   => $google_settings
);
