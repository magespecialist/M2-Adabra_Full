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

use Magento\Framework\Filesystem\Io\FtpFactory;

class Ftp
{
    protected $data;
    protected $ftpFactory;

    public function __construct(
        Data $data,
        FtpFactory $ftpFactory
    ) {
        $this->data = $data;
        $this->ftpFactory = $ftpFactory;
    }

    /**
     * FTP upload
     * @param $localFileName
     * @param $remoteFileName
     */
    public function uploadFile($localFileName, $remoteFileName)
    {
        $ftp = $this->ftpFactory->create();

        $transferMode = (strpos($remoteFileName, '.gz') === false) ? FTP_ASCII : FTP_BINARY;

        $ftp->open([
            'host' => $this->data->getFtpHost(),
            'port' => $this->data->getFtpPort(),
            'user' => $this->data->getFtpUser(),
            'password' => $this->data->getFtpPass(),
            'ssl' => $this->data->getFtpSsl(),
            'passive' => $this->data->getFtpPassive(),
            'path' => $this->data->getFtpPath(),
            'file_mode' => $transferMode,
        ]);

        $ftp->write($remoteFileName, $localFileName, $transferMode);
        $ftp->close();
    }
}
