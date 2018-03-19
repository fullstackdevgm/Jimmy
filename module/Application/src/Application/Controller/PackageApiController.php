<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventInterface;
use Zend\Session\Container as SessionContainer;

use JimmyBase\Entity\ClientAccounts;

class PackageApiController extends AbstractRestfulController
{
    protected $identifierName = 'package_id';

    public function getList()
    {


		$packages   = $this->getServiceLocator()->get('jimmybase_package_mapper')->fetchStandard();


		foreach ($packages as $key => $package) {
			$packagesNew[] = array('id'     =>  $package->getId(),
				                   'title'  =>  $package->getTitle(),
				                   'type'   =>  $package->getType(),
				                   'templates_allowed'=>$package->getTemplatesAllowed(),
				                   'price'  =>$package->getPrice(),
				                   'is_free_trial' => $package->getIsFreeTrial());
		}


	 return new JsonModel($packagesNew);
    }

    public function get($package_id)
    {

	 $package   = $this->getServiceLocator()->get('jimmybase_package_mapper')->fetchUnlimited();

	 return new JsonModel($package);
    }


}
