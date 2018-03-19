<?php
/**
 * Google Configuration
 *
 * If you have a ./config/autoload/ directory set up for your project, you can
 * drop this config file in it and change the values as you wish.
 */

$jimmy_settings  = array(
	'baseurl'   => 'https://whitelist.jimmydata.com/',
	'clienturl' => 'https://reports.whitelist.jimmy.com/login',
        'title'     => 'Global RevGen'

);

$logo_settings = array(
    'logo_title'   => 'Global RevGen',
    'logo_url'     => 'grg-logo.png',
    'favicon'     => 'grg-fav-icon.png'
);

$google_settings = array(
     /* Callback url for adwords and analytics */
    'redirect_uri'      => $jimmy_settings['baseurl'].'authcallback',
);

/**
 * You do not need to edit below this line
 */
return array(
	'jimmy-config'		  => $jimmy_settings,
	'google-api-config'       => $google_settings,
	'logo-config'		  => $logo_settings
);
