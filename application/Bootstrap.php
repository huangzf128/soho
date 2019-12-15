<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    #stores a copy of the config object in the Registry for future references
    #!IMPORTANT: Must be runed before any other inits
    protected function _initConfig()
    {
    	Zend_Registry::set('config', new Zend_Config($this->getOptions()));
    }
    
    protected function _initDoctype()
    {
    	$this->bootstrap('view');
    	$view = $this->getResource('view');
    	$view->doctype('XHTML1_STRICT');
    }
    
    protected function _initDatabases()
    {    	
    	$resource = $this->bootstrap('multidb')->getResource('multidb');
    	$databases = Zend_Registry::get('config')->resources->multidb;
    	foreach ($databases as $name => $adapter)
    	{
    		$db_adapter = $resource->getDb($name);
    		Zend_Registry::set($name, $db_adapter);
    	}
    }    
}

