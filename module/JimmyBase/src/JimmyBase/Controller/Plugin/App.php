<?php

namespace JimmyBase\Controller\Plugin;

use Zend\Mvc\Controller\Plugin;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Authentication\AuthenticationService;

class App extends AbstractPlugin
{
    private $sm;
	
	
	public function __construct($sm){
	
		$this->sm = $sm;
		
	}	
	
	
	public function isAdmin(){
	  // $request = new Request();
	  // echo $this->getRequest();
	   
	    $route = $this->sm->get('Application')->getMvcEvent()->getRouteMatch()->getMatchedRouteName();

       if($route){
	    list($route1,) = explode('/',$route);

		if($route1=='admin') 
			return true;
		else 
			return false;
			
	   }
	   return false;
	}
	
	
	public function resizeImage($file, $newfile, $w, $h, $crop=FALSE) {
		list($width, $height) = getimagesize($file);
		$r = $width / $height;// orginal
		
		if ($crop) {
			if ($width > $height) {
				$width = ceil($width-($width*($r-$w/$h)));
			} else {
				$height = ceil($height-($height*($r-$w/$h)));
			}
			$newwidth = $w;
			$newheight = $h;
		} else {
			
			if ($w/$h > $r) {
				$newwidth  = $h*$r;
				$newheight = $h;
			} else {
				$newheight = $w/$r;
				$newwidth  = $w;
			}
		}
		
		 $gis        = getimagesize($file);
    	 $type       = $gis[2];

		switch($type){
			case "1": $src = imagecreatefromgif($file); break;
			case "2": $src = imagecreatefromjpeg($file);break;
			case "3": $src = imagecreatefrompng($file); break;
			default:  $src = imagecreatefromjpeg($file);
		}
		
		//$src = imagecreatefromjpeg($file);
		$dst = imagecreatetruecolor($newwidth, $newheight);
		
        imagealphablending($dst, false);

		if(imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height)){
		
			if ($type==3){

				imagesavealpha($dst, true);
        		imagepng($dst, $newfile);
        		imagedestroy($dst);

				return true;
			} else if ($type!=3 && imagejpeg($dst, $newfile))
				return true;
			else
				return false;
		}
		
	}
	
	public function randomPassword() {
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
		$pass = array(); //remember to declare $pass as an array
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < 8; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass); //turn the array into a string
}

	
}
