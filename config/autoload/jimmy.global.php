<?php
/**
 * Google Configuration
 *
 * If you have a ./config/autoload/ directory set up for your project, you can
 * drop this config file in it and change the values as you wish.
 */

$jimmy_settings  = array(
'baseurl'   => 'https://app.jimmydata.com/',
'clienturl' => 'https://reports.jimmydata.com/',
// 'baseurl'   => 'https://dev.jimmydata.com/',
// 'clienturl' => 'https://dev.reports.jimmydata.com/',
//  'baseurl'   => 'https://staging.jimmydata.com/',
//	'clienturl' => 'https://staging.reports.jimmydata.com/',


'free_package_id'    => 16,

'payment' => array(
        'period'     => 1,

        'frequency'	 => 3, // 1 = day , 2 = week , 3 = month, 4 = year

        'currency'	 => 'AUD',

        'processor'  =>  'eWay'
	),
// 'jimmy-env'        => 'sandbox',
 'jimmy-env'        => 'production'
);

$logo_settings = array(
	'logo_title'   => 'Global Revgen',
	'logo_url'	   => 'logo-jimmy.png'
);

$jimmy_default_user_settings  = array(
	'from_email'       => 'no-reply@jimmydata.com',

	'from_name'        => 'Jimmydata',

	'share_report_email_body' => "<p>Hi I&#39;m Jimmy,</p>

			<p><strong>[agency-name]</strong> wants to share a report with you.</p>

			<p>Report: <strong>[report-title]</strong></p>

			[newuser]Your username and password[/newuser]

			<p>To access your new reporting dashboard you can login to [url]</p>

			<p>The report you will be viewing will be real time data. Remember you can also change the date range if you want to view your data in another timeframe.</p>

			<p>Love using Jimmy to view your reports? Then why go backwards? Get your Agency to use Jimmy and access all your real time data in the one place!</p>
	"
);


$google_settings = array(
    /**
     * Google Client ID
     *
     * Please specify a Google Client ID
     */
    //'client_id' => '877944901534-k2fulbmmfie02tfeg23r0ne2svq3m2kf.apps.googleusercontent.com',
    'client_id' => '272310704029-eeqidn6de6g33r514k37le595242u171.apps.googleusercontent.com',

    /**
     * Google Secret
     *
     * Please specify a Google Secret
     */
    //'client_secret'   => 'D17MjQeQCkpE6p1o66GndLVd',
    'client_secret'   => 'hFxpVPoyzu8u4ZCIr5xIyE6N',

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
    'user_agent'      => 'webmarketerscrew',

	/**
     * Callback url for adwords and analytics
     *
     * Please specify a Google Secret
     */
    'redirect_uri'      => $jimmy_settings['baseurl'].'authcallback',

    /**
     * End of Jimmy configuration
     */
);

$braintree_settings = array(
    'sandbox' => array (
        'merchant_id' => 'tff7jk8jfc5hb8gy',
        'public_key' => '2mkrbjnj3v23dgnc',
        'private_key' => 'd5939d2cb35c6bfa972bc4263122d005'
    ),
    'production' => array (
        'merchant_id' => 'w3jtmp7st4twfxvs',
        'public_key' => '327vd9mnw3ppfdkk',
        'private_key' => '7f5e85e0b127bbdeb351da0cec1ce2ae'
    )
);


$eway_settings = array(

	'live'    => array(
				/**
				 * eWay CustomerId
				 *
				 * Please specify the eWay Customer Id
				 */
				'customer_id'   => '15203047',

				/**
				 * eWay UserName
				 *
				 * Please specify the eWay Username
				 */
				'username'      => 'william@webmarketers.com.au',

				/**
				 * eWay Password
				 *
				 * Please specify the eWay Password
				 */
				'password'      => 'Jimmy2013api',
	),
	'sandbox' => array(
                /**
                * eway API key
                *
                * Useful while using Rapid API
                */
                'api_key'       => '60CF3CDttBwvlWa+w7SbiaF02ZHrmhwSPETbPUFc7bRdwlnC1PQ82awcTnv3dT0R4kEJN4',

				/**
				 * eWay CustomerId
				 *
				 * Please specify the eWay Customer Id
				 */
				'customer_id'   => '91641148',//'91641148'

				/**
				 * eWay UserName
				 *
				 * Please specify the eWay Username
				 */
				'username'      => 'maheshmohan073@gmail.com.sand',

				/**
				 * eWay Password
				 *
				 * Please specify the eWay Password
				 */
				'password'      => 'kP8ttSLy!',

                /**
                 * eWay Transaction Endpoint
                 *
                 * Sandbox or Production
                 **/
                'api_endpoint'  => \Eway\Rapid\Client::MODE_SANDBOX,
		),
	/**
     * eWay Test Mode ?
     *
     * Please specify the eWay Test Mode
     */
    'test_mode'      => false,
);


$bing_settings  = array(
    /**
     * Facebook Client ID
     *
     * Please specify a Facebook Client ID
     */
    'client_id'=>'000000004C11F571',
    /**
     * Facebook Secret
     *
     * Please specify a Facebook Secret
     */
    'client_secret' => '-O8upe8ksf7u1pvdy8rVXFKmwJJ55x9z',

    /**
     * Developer Token
     *
     * Please specify a Google Secret
     */
    'developer_token' => '000225M722031073',

    /**
     * User Agent
     *
     * Callback url for adwords and analytics
     */
    'redirect_uri'      => $jimmy_settings['baseurl'].'authcallback',

    /**
     * End of Jimmy configuration
     */
);




$mailchimp_settings = array(
	'agency_list_id' => '7d20c1bd3e',
	//'trial_list_id'  => 'e00345cb3b',
	'client_list_id' => '97d3d40fca',
	'email_type'     => 'html'
);

/**
 * You do not need to edit below this line
 */
return array(
    'jimmy-config'        => $jimmy_settings,
    'google-api-config'   => $google_settings,
    'bing-api-config'     => $bing_settings,
    'eway-api-config'     => $eway_settings,
    'mailchimp-config'    => $mailchimp_settings,
    'default-user-config' => $jimmy_default_user_settings,
    'logo-config'         => $logo_settings,
    'braintree-api-config' => $braintree_settings
);
