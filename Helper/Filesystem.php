<?php
/**
 * IDEALIAGroup srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@idealiagroup.com so we can send you a copy immediately.
 *
 * @category   Adspray
 * @package    Adspray_Adabra
 * @copyright  Copyright (c) 2016 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Adspray\Adabra\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;

class Filesystem
{
    const PERMS_LOCK_FILE = 660;
    const PERMS_DIR = 0770;
    const PERMS_FILE = 0660;

    protected $directoryList;
    protected $file;

    protected $locks = [];

    public function __construct(
        DirectoryList $directoryList,
        File $file
    ) {
        $this->directoryList = $directoryList;
        $this->file = $file;
    }

    /**
     * Get adabra feed export path
     * @return string
     */
    public function getExportPath()
    {
        $res = $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/adabra';
        $this->file->checkAndCreateFolder($res, self::PERMS_DIR);

        return $res;
    }

    /**
     * Get lock file name by lock name
     * @param $lockName
     * @return string
     */
    protected function getLockFile($lockName)
    {
        $lockName = preg_replace('/[^\w\-\_]+/', '', $lockName);
        return $this->directoryList->getPath(DirectoryList::TMP) . '/' . $lockName . '.lck';
    }

    /**
     * Acquire lock by lock name and return true on success
     * @param $lockName
     * @return bool
     */
    public function acquireLock($lockName)
    {
        $lockFile = $this->getLockFile($lockName);

        // @codingStandardsIgnoreStart
        // Io/File only supports blocking locks
        $this->locks[$lockName] = fopen($lockFile, 'w');
        chmod($lockFile, self::PERMS_LOCK_FILE);
        return flock($this->locks[$lockName], LOCK_EX | LOCK_NB);
        // @codingStandardsIgnoreEnd
    }

    /**
     * Release a previously acquired lock
     * @param $lockName
     * @return $this
     */
    public function releaseLock($lockName)
    {
        if (isset($this->locks[$lockName])) {
            flock($this->locks[$lockName], LOCK_UN);
            fclose($this->locks[$lockName]);

            unset($this->locks[$lockName]);
        }

        return $this;
    }
}
