<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Backup\PhpArchivator;

class BlacklistFilterIterator extends \RecursiveFilterIterator
{
    /** @var array */
    protected $blacklist = [];

    /** @var  string */
    protected $archivedDirectory;

    /**
     * Init iterator
     *
     * @param \RecursiveIterator $iterator
     * @param $archivedDirectory
     * @param array $blacklist
     */
    public function __construct (\RecursiveIterator $iterator, $archivedDirectory, array $blacklist = [])
    {
        $this->blacklist = $blacklist;
        $this->archivedDirectory = $archivedDirectory;
        parent::__construct($iterator);
    }

    /**
     * Accept files and dirs, which are not in blacklist
     *
     * @return bool
     */
    public function accept()
    {
        $relativePath = str_replace($this->archivedDirectory, '', $this->current()->getPathname());
        $relativePath = str_replace('\\', '/', $relativePath);
        $result = in_array($relativePath, $this->blacklist) ? false : true;
        return $result;
    }

    /**
     * Return the inner iterator's children contained in a BlacklistFilterIterator.
     *
     * @return BlacklistFilterIterator
     */
    public function getChildren() {
        return new self($this->getInnerIterator()->getChildren(), $this->archivedDirectory, $this->blacklist);
    }
}
