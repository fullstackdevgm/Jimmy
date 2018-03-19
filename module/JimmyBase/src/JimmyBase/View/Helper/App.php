<?php

namespace JimmyBase\View\Helper;
 
use Zend\View\Helper\AbstractHelper;
use Zend\Mvc\Controller\Plugin\FlashMessenger as FlashMessenger;
 
/**
 * @author Rick <rick@thewebmen.com>
 * @company The Webmen
 */
class FlashMessages extends AbstractHelper
{
    /**
     * @var FlashMessenger
     */
    protected $flashMessenger;
	private $messages;
	
	const NAMESPACE_ERROR 	= 'error';
	const NAMESPACE_SUCCESS = 'success';
	const NAMESPACE_INFO 	= 'info';
	const NAMESPACE_DEFAULT = 'default';
	
 
    public function setFlashMessenger(FlashMessenger $flashMessenger)
    {
        $this->flashMessenger = $flashMessenger;
    }
 
    public function __invoke($includeCurrentMessages = false){
		$messages = array(
            FlashMessenger::NAMESPACE_ERROR => array(),
            FlashMessenger::NAMESPACE_SUCCESS => array(),
            FlashMessenger::NAMESPACE_INFO => array(),
            FlashMessenger::NAMESPACE_DEFAULT => array()
        );
 
        foreach ($messages as $ns => &$m) {
            $m = $this->flashMessenger->getMessagesFromNamespace($ns);
            if ($includeCurrentMessages) {
                $m = array_merge($m, $this->flashMessenger->getCurrentMessagesFromNamespace($ns));
            }
			$this->flashMessenger->clearMessagesFromNamespace($ns);
        }
 
 		$this->messages = $messages;
		$this->flashMessenger->clearMessagesFromContainer();
		//echo '<pre>';
		//print_r($messages);
        return $this;
	}
	
	public function display(){
		foreach ($this->messages as $namespace => $messages) :?>
            <?php if (is_array($messages)) : ?>
                <?php foreach ($messages as $message) : ?>
                <div class="alert alert-<?=$namespace?>">
                    <a class="close" data-dismiss="alert">×</a><?php echo $message; ?>
                </div>
                <?php endforeach;  
				endif; 
         endforeach; 
	}
}