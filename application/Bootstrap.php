<?php
/**
 * The application bootstrap used by Zend_Application
 *
 * @category   Bootstrap
 * @package    Bootstrap
 * @copyright  Copyright (c) 2008 Keith Pope (http://www.thepopeisdead.com)
 * @license    http://www.thepopeisdead.com/license.txt     New BSD License
 */

/**
 * @var Zend_Log
 */
$logger = null;

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * @var Zend_Log
     */
    protected $logger = null;
    
    /**
     * @var Zend_Application_Module_Autoloader
     */
    protected $_resourceLoader;

    /**
     * @var Zend_Controller_Front
     */
    public $frontController;

    /**
     * Configure the session path
     */
    protected function _initSession()
    {
        //error_log(__CLASS__.'::'.__METHOD__.': entry 0');

        ini_set('session.save_path', SESSION_PATH);
    }

    /**
     * Configure the pluginloader cache
     */
    protected function _initPluginLoaderCache()
    {
        //error_log(__CLASS__.'::'.__METHOD__.': entry 1');
        //if ('production' != $this->getEnvironment())
            //return;

        if (!ENABLE_PLUGIN_CACHE)
            return;
        
        $classFileIncCache = CACHE_PLUGIN_FILE;
        if (file_exists($classFileIncCache)) {
            include_once $classFileIncCache;
        }
        Zend_Loader_PluginLoader::setIncludeFileCache($classFileIncCache);
    }

    /**
     * Setup the logging
     */
    protected function _initLogging()
    {
        //error_log(__CLASS__.'::'.__METHOD__.': entry 2');
        global $logger;
        
        $this->bootstrap('frontController');
        //frontController gets set in Zend_Application_Resource_Frontcontroller
        //  but I will get it anyhow
        $this->frontController = $this->getResource('frontController');
        //error_log('frontController='.get_class($this->frontController));
        
        $logger = new Zend_Log();
/*
        $writer = 'production' == $this->getEnvironment() ?
			new Zend_Log_Writer_Stream(
                            realpath(APPLICATION_PATH . '/../data/logs/app.log')) :
			new Zend_Log_Writer_Firebug();
 * 
 */
	$writer = new Zend_Log_Writer_Stream(LOG_PATH);
        $logger->addWriter($writer);

        if ('production' == $this->getEnvironment())
            $filter = new Zend_Log_Filter_Priority(Zend_Log::CRIT);
        else
            $filter = new Zend_Log_Filter_Priority(Zend_Log::INFO);
        $logger->addFilter($filter);

        Zend_Registry::set('log', $logger);
        $this->logger = $logger;
        //$logger->crit('_initLogging: crit');
        //$logger->info('_initLogging: info');
        //$logger->debug('_initLogging: debug');
        //error_log('_initLogging: error_log');
    }

    /**
     * Configure the default modules autoloading, here we first create
     * a new module autoloader specifiying the base path and namespace
     * for our default module. This will automatically add the default
     * resource types for us. We also add two custom resources for Services
     * and Model Resources.
     */
    protected function _initDefaultModuleAutoloader()
    {
        //error_log(__CLASS__.'::'.__METHOD__.': entry 3');
        $this->_resourceLoader = new Zend_Application_Module_Autoloader(
            array(
                'namespace' => 'Storefront',
                'basePath'  => APPLICATION_PATH . '/modules/storefront',
            ));
        $this->_resourceLoader->addResourceTypes(array(
            'modelResource' => array(
                'path'      => 'models/resources',
                'namespace' => 'Resource',
            )
        ));
    }

    /* for z1doc2-crud
    public function _initAutoloader()
    {
        require_once 'Doctrine/Common/ClassLoader.php';

        $autoloader = \Zend_Loader_Autoloader::getInstance();

        $bisnaAutoloader = new \Doctrine\Common\ClassLoader('Bisna');
        $autoloader->pushAutoloader(array($bisnaAutoloader, 'loadClass'), 'Bisna');

        $appAutoloader = new \Doctrine\Common\ClassLoader('Zstore');
        $autoloader->pushAutoloader(array($appAutoloader, 'loadClass'), 'Zstore');
    }
     * 
     */
    
    public function _initAutoloaderNamespaces()
    {
        require_once 'Doctrine/Common/ClassLoader.php';

        $autoloader = \Zend_Loader_Autoloader::getInstance();
        $fmmAutoloader = new \Doctrine\Common\ClassLoader('Bisna');
        $autoloader->pushAutoloader(array($fmmAutoloader, 'loadClass'), 'Bisna');
    }
    
    /**
     * Setup locale
     */
    protected function _initLocale()
    {
        //error_log(__CLASS__.'::'.__METHOD__.': entry 4');
        $locale = new Zend_Locale('en_US');
        Zend_Registry::set('Zend_Locale', $locale);
    }

    /**
     * Setup the database profiling
     */
    protected function _initDbProfiler()
    {
        //error_log(__CLASS__.'::'.__METHOD__.': entry 5');
        if (!ENABLE_DB_PROFILER)
            return;
        
        if ('production' !== $this->getEnvironment()) {
            $this->bootstrap('db');
            $profiler = new Zend_Db_Profiler_Firebug('All DB Queries');
            $profiler->setEnabled(true);
            $this->getPluginResource('db')->getDbAdapter()
                    ->setProfiler($profiler);
        }
    }
    
    /**
     * Add the config to the registry
     */
    protected function _initConfig()
    {
        //error_log(__CLASS__.'::'.__METHOD__.': entry 6');
        Zend_Registry::set('config', $this->getOptions());
    }

    /**
     * Setup the view
     */
    protected function _initViewSettings()
    {
        //error_log(__CLASS__.'::'.__METHOD__.': entry 7');
        
        $this->bootstrap('view');
        $this->_view = $this->getResource('view');

        // add global helpers
        $this->_view->addHelperPath(APPLICATION_PATH . '/views/helpers', 'App_View_Helper');
        
        $request=new Zend_Controller_Request_Http();    // need for baseUrl
        $this->frontController->setRequest($request);
        $baseUrl = $this->frontController->getBaseUrl();
        SF_Log::info(__METHOD__, 'baseUrl='.$baseUrl);
        //$baseUrl = '/storefront';

        // set encoding and doctype
        $this->_view->setEncoding('UTF-8');
        $this->_view->doctype('XHTML1_STRICT');

        // set the content type and language
        $this->_view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8');
        $this->_view->headMeta()->appendHttpEquiv('Content-Language', 'en-US');
        $this->_view->headMeta()->appendName('viewport', 'width=device-width, initial-scale=1.0');

        // set css links and a special import for the accessibility styles
        //$this->_view->headStyle()->setStyle('@import "/assets/access.css";');
        
        $this->_view->headLink()->appendStylesheet($baseUrl.'/assets/access.css');
        $this->_view->headLink()->appendStylesheet($baseUrl.'/assets/reset.css');
        $this->_view->headLink()->appendStylesheet($baseUrl.'/assets/main.css');
        $this->_view->headLink()->appendStylesheet($baseUrl.'/assets/form.css');
        $this->_view->headLink()->appendStylesheet($baseUrl.'/js/bootstrap/2.3.0/css/bootstrap.css');
        $this->_view->headLink()->appendStylesheet($baseUrl.'/js/bootstrap/2.3.0/css/bootstrap-responsive.css');
        $this->_view->headLink()->appendStylesheet($baseUrl.'/assets/my-bootstrap.css');

        //for head section
        $this->_view->headScript()->setFile(
                $baseUrl.'/js/html5shiv/3.6.2/html5shiv.js',
                'text/javascript',
                array('conditional' => 'lt IE 9'));
        
        //for bottom of html file
        $this->_view->inlineScript()->setFile($baseUrl.'/js/jquery/1.9.1/jquery-1.9.1.js');
//        $this->_view->inlineScript()->appendFile($baseUrl.'/js/bootstrap/2.3.0/js/bootstrap.js');
        $this->_view->inlineScript()->appendFile($baseUrl.'/js/bootstrap/2.3.0/js/bootstrap.js');
       // $this->_view->headScriptBottom = $this->_view->headScript()->toString();
        
        // setting the site in the title
        $this->_view->headTitle('Storefront');

        // setting a separator string for segments:
        $this->_view->headTitle()->setSeparator(' - ');
    }

    /**
     * Add required routes to the router
     */
    protected function _initRoutes()
    {
        //error_log(__CLASS__.'::'.__METHOD__.': entry 8');
        $this->bootstrap('frontController');
        
        //error_log('frontController class='.get_class($this->frontController));
        $router = $this->frontController->getRouter();

        // Admin context route
        $route = new Zend_Controller_Router_Route(
            //'admin/:module/:controller/:action/*',
            'admin/:controller/:action/*',
            array(
                'action'     => 'index',
                'controller' => 'admin',
                'module'     => 'storefront',
                'isAdmin'    => true
            )
        );

        $router->addRoute('admin', $route);

        // catalog category product route
        $route = new Zend_Controller_Router_Route(
            'catalog/:categoryIdent/:productIdent',
            array(
                'action'        => 'view',
                'controller'    => 'catalog',
                'module'        => 'storefront',
                'categoryIdent' => '',
            ),
            array(
                'categoryIdent' => '[a-zA-Z-_0-9]+',
                'productIdent'  => '[a-zA-Z-_][a-zA-Z-_0-9]+'
            )
        );

        $router->addRoute('catalog_category_product', $route);

        // catalog category route
        $route = new Zend_Controller_Router_Route(
            'catalog/:categoryIdent/:page',
            array(
                'action'        => 'index',
                'controller'    => 'catalog',
                'module'        => 'storefront',
                'categoryIdent' => '',
                'page'          => 1
            ),
            array(
                'categoryIdent' => '[a-zA-Z-_0-9]+',
                'page'          => '\d+'
            )
        );

        $router->addRoute('catalog_category', $route);
    }

    /**
     * Add Controller Action Helpers
     */
    protected function _initActionHelpers()
    {
        //error_log(__CLASS__.'::'.__METHOD__.': entry 9');
        Zend_Controller_Action_HelperBroker::addHelper(new SF_Controller_Helper_Acl());
        Zend_Controller_Action_HelperBroker::addHelper(new SF_Controller_Helper_RedirectCommon());
        Zend_Controller_Action_HelperBroker::addHelper(new SF_Controller_Helper_Service());
    }

    /**
     * Init the db metadata and paginator caches
     */
    protected function _initDbCaches()
    {
        //error_log(__CLASS__.'::'.__METHOD__.': entry 10');
        if (!ENABLE_DB_CACHES)
            return;
                
        //if ('testing' == $this->getEnvironment())
        //    return;
        
        // Metadata cache for Zend_Db_Table
        /*
        //xcache cache
        $frontendOptions = array(
            'automatic_serialization' => true
        );

        $cache = Zend_Cache::factory('Core',
            'Xcache',
            $frontendOptions
        );
        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);  
        */
        //file cache
        $frontendOptions = array(
            'lifetime' => 1800,
            'automatic_serialization' => true
        );
        $backendOptions = array(
            //'cache_dir'=> realpath(APPLICATION_PATH . '/../data/cache/meta')
            'cache_dir'=> CACHE_META_PATH
        );
        $cache = Zend_Cache::factory('Core', 'file', $frontendOptions,
            $backendOptions);
        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);  
    }

    /**
     * Add gracefull error handling to the bootstrap process
     */
    protected function _bootstrap($resource = null)
    {
        //error_log(__CLASS__.'::'.__METHOD__.': entry');
        
        $errorHandling = $this->getOption('errorhandling');
        try {
            parent::_bootstrap($resource);
        } catch(Exception $e) {
            if (true == (bool) $errorHandling['graceful']) {
                $this->__handleErrors($e, $errorHandling['email']);
            } else {
                throw $e;
            }
        }
    }

    /**
     * Add graceful error handling to the dispatch, this will handle
     * errors during Front Controller dispatch.
     */
    public function run()
    {
        //error_log(__CLASS__.'::'.__METHOD__.': entry');
        //SF_Log::info(__METHOD__, 'entry');
        
        $errorHandling = $this->getOption('errorhandling');
        try {
            parent::run();
        } catch(Exception $e) {
            if (true == (bool) $errorHandling['graceful']) {
                $this->__handleErrors($e, $errorHandling['email']);
            } else {
                throw $e;
            }
        }
    }

    /**
     * Handle errors gracefully, this will work as long as the views,
     * and the Zend classes are available
     *
     * @param Exception $e
     * @param string $email
     */
    protected function __handleErrors(Exception $e, $email)
    {
        header('HTTP/1.1 500 Internal Server Error');
        $view = new Zend_View();
        $view->addScriptPath(realpath(dirname(__FILE__) . '/../views/scripts'));
        echo $view->render('fatalError.phtml');

        if ('' != $email) {
            $mail = new Zend_Mail();
            $mail->setSubject('Fatal error in application Storefront');
            $mail->addTo($email);
            $mail->setBodyText(
                $e->getFile() . "\n" .
                $e->getMessage() . "\n" .
                $e->getTraceAsString() . "\n"
            );
            @$mail->send();
        }
    }
}
