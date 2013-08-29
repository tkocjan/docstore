<?php
/**
 * Application Action Plugin
 * 
 * This plugin uses the action stack to provide global
 * view segments.
 * 
 * @category   Storefront
 * @package    SF_Plugin
 * @copyright  Copyright (c) 2008 Keith Pope (http://www.thepopeisdead.com)
 * @license    http://www.thepopeisdead.com/license.txt     New BSD License
 */
class SF_Plugin_Action extends Zend_Controller_Plugin_Abstract 
{
    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        global $logger;
        
        Logger::info(__METHOD__.
            ': Request: module='.$request->getModuleName().
            ' controller='.$request->getControllerName().
            ' action='.$request->getActionName()
        );
        
        $view = Zend_Layout::getMvcInstance()->getView();
        $view->active = 'index';
    }
        
    /*
    protected $_stack;

    public function dispatchLoopStartup(
        Zend_Controller_Request_Abstract $request) 
    {        
        // category menu
        $categoryRequest = new Zend_Controller_Request_Simple();
        $categoryRequest->setModuleName('storefront')
                        ->setControllerName('category')
                        ->setActionName('index');
//                        ->setParam('responseSegment', 'categoryMain');

        // push requests into the stack
        $this->getStack()->pushStack($categoryRequest);
    }

    public function getStack()
    {
        if (null === $this->_stack) {
            $front = Zend_Controller_Front::getInstance();
            if (!$front->hasPlugin('Zend_Controller_Plugin_ActionStack')) {
                $this->_stack = new Zend_Controller_Plugin_ActionStack();
                $front->registerPlugin($this->_stack);
            } else {
                $this->_stack = $front->getPlugin('Zend_Controller_Plugin_ActionStack');
            }
        }
        return $this->_stack;
    }
     * 
     */
}
