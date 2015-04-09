<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Backup;

/**
 * Archive interface
 */
interface ArchiveInterface
{
    /**
     * Archive data
     *
     * @return string Backup filename
     * @throws \Exception
     */
    public function archive();
}
