<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update\Queue;

/**
 * Class for handling Maintenance Mode in Updater app.
 */
class MaintenanceMode
{
    /**
     * Path to the maintenance flag file
     * @var string
     */
    protected $flagFile;

    /**
     * Path to the IP addresses file
     * @var string
     */
    protected $ipFile;

    /**
     * Initialize.
     *
     * @param string|null $flagFile
     * @param string|null $ipFile
     */
    public function __construct($flagFile = null, $ipFile = null)
    {
        $this->flagFile = $flagFile ? $flagFile : UPDATER_BP . '/var/.maintenance.flag';
        $this->ipFile = $ipFile ? $ipFile : UPDATER_BP . '/var/.maintenance.ip';
    }

    /**
     * Checks whether mode is on
     *
     * Optionally specify an IP-address to compare against the white list
     *
     * @param string $remoteAddr
     * @return bool
     */
    public function isOn($remoteAddr = '')
    {
        if (!file_exists($this->flagFile)) {
            return false;
        }
        $info = $this->getAddressInfo();
        return !in_array($remoteAddr, $info);
    }

    /**
     * Sets maintenance mode "on" or "off"
     *
     * @param bool $isOn
     * @return bool
     */
    public function set($isOn)
    {
        if ($isOn) {
            return touch($this->flagFile);
        }
        if (file_exists($this->flagFile)) {
            return unlink($this->flagFile);
        }
        return true;
    }

    /**
     * Sets list of allowed IP addresses
     *
     * @param string $addresses
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function setAddresses($addresses)
    {
        $addresses = (string)$addresses;
        if (empty($addresses)) {
            if (file_exists($this->ipFile)) {
                return unlink($this->ipFile);
            }
            return true;
        }
        if (!preg_match('/^[^\s,]+(,[^\s,]+)*$/', $addresses)) {
            throw new \InvalidArgumentException("One or more IP-addresses is expected (comma-separated)\n");
        }
        $result = file_put_contents($this->ipFile, $addresses);
        return false !== $result ? true : false;
    }

    /**
     * Get list of IP addresses effective for maintenance mode
     *
     * @return string[]
     */
    public function getAddressInfo()
    {
        if (file_exists($this->ipFile)) {
            $temp = file_get_contents($this->ipFile);
            return explode(',', trim($temp));
        } else {
            return [];
        }
    }
}
