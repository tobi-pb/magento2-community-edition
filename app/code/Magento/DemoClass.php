<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento;

/**
 * TODO: Remove demo class when any real class is available.
 */
class DemoClass
{
    public function getCurrentDateTime()
    {
        date_default_timezone_set('UTC');
        return date('m/d/Y h:i:s a', time());
    }
}