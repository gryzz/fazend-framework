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

/**
 * User Interface Modeller
 *
 *
 */
class Fazend_UiController extends FaZend_Controller_Action {

    /**
     * Show the entire map of the system
     *
     * @return void
     */
    public function indexAction() {
        // not implemented yet
    }

    /**
     * Show one mockup
     *
     * @return void
     */
    public function mockupAction() {

        $mockup = new FaZend_UiModeller_Mockup($this->_getParam('id'));

        $this->_returnPNG($mockup->png());

    }

}