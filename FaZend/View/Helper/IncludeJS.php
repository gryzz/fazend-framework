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
 * Include single JS file
 *
 * @see http://naneau.nl/2007/07/08/use-the-url-view-helper-please/
 * @package FaZend 
 */
class FaZend_View_Helper_IncludeJS extends FaZend_View_Helper {

    /**
    * Include a JS file as a link
    *
    * @return void
    */
    public function includeJS($script) {

        $this->getView()->headScript()->appendFile($this->getView()->url(array('script'=>$script), 'js', true));

    }

}
