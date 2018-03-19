<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace JimmyBase\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ModelInterface;
use JimmyBase\Entity\ClientAccounts;



class BlogApiController extends AbstractRestfulController
{
   

    public function getList(){
    	session_write_close();

	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, "https://jimmydata.com/api/get_recent_posts/");
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    $results = curl_exec($ch);
	    curl_close($ch);


	 	echo $results;		
		exit;		
    }

  }
