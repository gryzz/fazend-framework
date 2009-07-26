<?php
/**
 *
 * Copyright (c) FaZend.com
 * All rights reserved.
 *
 * You can use this product "as is" without any warranties from authors.
 * You can change the product only through Google Code repository
 * at http://code.google.com/p/fazend
 * If you have any questions about privacy, please email privacy@fazend.com
 *
 * @copyright Copyright (c) FaZend.com
 * @version $Id$
 * @category FaZend
 */
            
require_once 'FaZend/Controller/Action.php';

/**
 * Static file delivery from "views/files"
 *
 * @see http://framework.zend.com/manual/en/zend.loader.html#zend.loader.load.autoload
 */
class Fazend_FileController extends FaZend_Controller_Action {

    /**
     * Show one file
     * 
     * @return void
     */
    public function indexAction() {

        //$this->getResponse()
        //    ->setHeader('Content-type', 'text/javascript');

        $file = APPLICATION_PATH . '/views/files/' . $this->_getParam('file');

        // if it's absent
        if (!file_exists($file)) {

            $file = FAZEND_PATH . '/View/files/' . $this->_getParam('file');
            if (!file_exists($file))
                return $this->_forwardWithMessage('file ' . $this->_getParam('file') . ' not found');
        }

        // tell browser to cache this content    
        $this->_cacheContent();    

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $this->getResponse()->setBody(file_get_contents($file));

    }    
}

