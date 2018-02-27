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

namespace Adspray\Adabra\Model\SubFeed;

use Adspray\Adabra\Api\Data\FeedInterface;
use Adspray\Adabra\Api\Data\SubFeedInterface;
use Adspray\Adabra\Helper\Data as DataHelper;
use Adspray\Adabra\Helper\Filesystem;
use Adspray\Adabra\Helper\Ftp as FtpHelper;
use Magento\Framework\Data\Collection;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Io\File;
use Magento\Directory\Helper\Data as DirectoryHelperData;
use Magento\Framework\Stdlib\DateTime\DateTime;

abstract class AbstractSubFeed implements SubFeedInterface
{
    protected $file;
    protected $filesystem;
    protected $csv;
    protected $dataHelper;
    protected $ftpHelper;
    protected $directoryHelperData;
    protected $dateTime;

    protected $lastId;
    protected $feed;
    protected $type = null;
    protected $exportName = null;
    protected $scope = 'store';
    protected $collection;
    protected $idFieldName = null;

    public function __construct(
        File $file,
        Csv $csv,
        Filesystem $filesystem,
        DataHelper $dataHelper,
        FtpHelper $ftpHelper,
        DirectoryHelperData $directoryHelperData,
        DateTime $dateTime
    ) {
        $this->file = $file;
        $this->filesystem = $filesystem;
        $this->csv = $csv;
        $this->dataHelper = $dataHelper;
        $this->ftpHelper = $ftpHelper;
        $this->directoryHelperData = $directoryHelperData;
        $this->dateTime = $dateTime;
    }

    /**
     * Get feed code
     * @return string
     */
    public function getCode()
    {
        return $this->getFeed()->getCode($this->scope) . '_' . $this->type;
    }

    /**
     * Get feed file name
     * @return string
     */
    public function getFeedFileName()
    {
        return $this->getFeed()->getCode($this->scope) . '_' . $this->exportName . '.csv';
    }

    /**
     * Get exported filename
     * @param $chunked
     * @param bool $compressed
     * @return string
     */
    public function getExportFile($chunked, $compressed = false)
    {
        $filename = $this->filesystem->getExportPath() . '/' . $this->getFeedFileName() . ($compressed ? '.gz' : '');
        return $chunked ? $filename . "." . $this->getLastId() : $filename;
    }

    /**
     * Get progress file name
     * @return string
     */
    protected function getPositionFileName()
    {
        return $this->getExportFile(false) . '.idx';
    }

    /**
     * Set parent feed
     * @param FeedInterface $feed
     * @return $this
     */
    public function setFeed(FeedInterface $feed)
    {
        $this->feed = $feed;
        return $this;
    }

    /**
     * Get parent feed
     * @return FeedInterface
     */
    public function getFeed()
    {
        return $this->feed;
    }

    /**
     * Get current build status
     * @return string
     */
    public function getBuildStatus()
    {
        return $this->getFeed()->getBuildStatus($this->getType());
    }

    /**
     * Get sub-feed type
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Load last position id
     * @return void
     */
    protected function loadPosition()
    {
        if ($this->file->fileExists($this->getPositionFileName())) {
            $position = $this->file->read($this->getPositionFileName());
        } else {
            $position = 0;
        }

        $this->setLastId($position);
    }

    /**
     * Save last position
     * @return void
     */
    protected function savePosition()
    {
        $this->file->write($this->getPositionFileName(), $this->lastId, Filesystem::PERMS_FILE);
    }

    /**
     * Set last position
     * @param $position
     * @return $this
     */
    protected function setLastId($position)
    {
        $this->lastId = $position;
        return $this;
    }

    /**
     * Get last position
     * @return int
     */
    protected function getLastId()
    {
        return intval($this->lastId);
    }

    /**
     * Change current sub-feed status
     * @param $status
     * @return $this
     */
    protected function changeBuildStatus($status)
    {
        $this->getFeed()->changeBuildStatus($this->getType(), $status);
        return $this;
    }

    /**
     * Get headers
     * @return array
     */
    abstract protected function getHeaders();

    /**
     * Prepare feed collection
     * @return void
     */
    abstract protected function prepareCollection();

    /**
     * Get feed row for entity
     * @param $entity
     * @return array
     */
    abstract protected function getFeedRow($entity);

    /**
     * Get virtual rows
     * @return array
     */
    protected function getVirtualRows()
    {
        return [];
    }

    /**
     * Get collection ID field Name
     * @return string
     */
    protected function getIdFieldName()
    {
        if ($this->idFieldName) {
            return $this->idFieldName;
        }

        if ($this->collection->getIdFieldName()) {
            return $this->collection->getIdFieldName();
        }

        return 'entity_id';
    }

    /**
     * Reset collection
     */
    protected function resetCollection()
    {
        $this->collection = null;
    }

    /**
     * Adds pagination limits to collection
     */
    protected function addPagination()
    {
        if (!$this->dataHelper->isBatchEnabled($this->getType())) {
            return;
        }

        $this->collection->getSelect()->limit($this->dataHelper->getBatchSize($this->getType()));
    }

    /**
     * Adds last id filter to collection
     */
    protected function addPositionFilter()
    {
        if (!$this->dataHelper->isBatchEnabled($this->getType())) {
            return;
        }

        $this->collection->addFieldToFilter(
            $this->getIdFieldName(),
            ['gt' => $this->getLastId()]
        );
    }

    /**
     * Sorts collection by id
     */
    protected function addCollectionSort()
    {
        $this->collection->setOrder(
            $this->getIdFieldName(),
            Collection::SORT_ORDER_ASC
        );
    }

    /**
     * Get collection
     * @return Collection
     */
    protected function getCollection()
    {
        if (is_null($this->collection)) {
            $this->prepareCollection();
            $this->addPagination();
            $this->addPositionFilter();
            $this->addCollectionSort();
        }

        return $this->collection;
    }

    /**
     * Convert to boolean
     * @param $val
     * @return string
     */
    protected function toBoolean($val)
    {
        return $val ? 'true' : 'false';
    }

    /**
     * Convert to currency
     * @param $val
     * @param $currencyConvert
     * @return string
     */
    protected function toCurrency($val, $currencyConvert = false)
    {
        if ($currencyConvert) {
            $val = $this->directoryHelperData->currencyConvert(
                $val,
                $this->getFeed()->getStore()->getBaseCurrencyCode(),
                $this->getFeed()->getCurrencyCode()
            );
        }

        return number_format($val, 4, '.', '');
    }

    /**
     * Assemble CSV files
     */
    protected function assembleFiles()
    {
        $fileName = $this->getExportFile(false);
        $headingFileName = $fileName . '.hdr';

        // Add headers and virtual rows
        $rows = [$this->getHeaders()];

        $virtualRows = $this->getVirtualRows();
        if (count($virtualRows)) {
            foreach ($virtualRows as $virtualRow) {
                $rows[] = $virtualRow;
            }
        }

        $this->csv->saveData($headingFileName, $rows);

        // Locate chunk files
        $this->file->open(['path' => $this->filesystem->getExportPath()]);
        $chunkFiles = $this->file->ls();

        $chunks = [];
        foreach ($chunkFiles as $chunkFile) {
            $chunkFileName = $this->filesystem->getExportPath() . '/' . $chunkFile['text'];

            if ((strpos($chunkFileName, $fileName) !== false) &&
                preg_match('/\.(\d+)$/', $chunkFileName, $matches)
            ) {
                $chunks[intval($matches[1])] = $chunkFileName;
            }
        }

        ksort($chunks, SORT_NUMERIC);
        array_unshift($chunks, $headingFileName);

        $content = '';
        foreach ($chunks as $chunk) {
            $content.= $this->file->read($chunk);
            $this->file->rm($chunk);
        }

        $this->file->write($fileName, $content);
        $this->file->rm($this->getPositionFileName());
    }

    /**
     * Perform gzip compression
     */
    protected function gzipCompression()
    {
        $plainFile = $this->getExportFile(false, false);
        $compressedFile = $this->getExportFile(false, true);

        // @codingStandardsIgnoreStart
        $fp = gzopen($compressedFile, 'w9');
        gzwrite($fp, file_get_contents($plainFile));
        gzclose($fp);
        @unlink($plainFile);
        // @codingStandardsIgnoreEnd
    }

    /**
     * Convert value to timestamp (format 2)
     * @param $ts
     * @return string
     */
    protected function toTimestamp2($ts)
    {
        return $this->dateTime->date('Y-m-d H:i:s', $ts);
    }

    /**
     * Convert value to timestamp (format 1)
     * @param $ts
     * @return string
     */
    protected function toTimestamp($ts)
    {
        return $this->dateTime->date('Y-m-d', $ts).'T'.$this->dateTime->date('H:i:s', $ts);
    }

    /**
     * Get feed content
     * @param $compress
     * @return string
     */
    public function getFeedContent($compress = false)
    {
        $fileName = $this->getExportFile(false, $compress);

        $exportPath = $this->filesystem->getExportPath();
        $this->file->open(array('path' => $exportPath));

        return $this->file->read($fileName);
    }

    /**
     * Export feed
     * @return void
     */
    public function export()
    {
        if ($this->getBuildStatus() == self::STATUS_READY) {
            return;
        }

        $this->loadPosition();
        $this->changeBuildStatus(SubFeedInterface::STATUS_BUILDING);

        $csvData = [];

        $collection = $this->getCollection();
        foreach ($collection as $entity) {
            $csvLines = $this->getFeedRow($entity);
            foreach ($csvLines as $csvLine) {
                $csvData[] = $csvLine;
            }
            $this->setLastId($entity->getId());
        }

        if (count($csvData)) {
            $this->csv->saveData($this->getExportFile(true), $csvData);
        }

        $this->savePosition();

        // Check if collection is finished
        $this->resetCollection();
        $collection = $this->getCollection();
        if (!$collection->getSize() || !$this->dataHelper->isBatchEnabled($this->getType())) {
            $this->assembleFiles();
            $this->changeBuildStatus(SubFeedInterface::STATUS_READY);

            $compress = $this->dataHelper->getCompress();
            if ($compress) {
                $this->gzipCompression();
            }

            if ($this->dataHelper->isFtpEnabled()) {
                $fileName = $this->getExportFile(false, $compress);
                $pathInfo = $this->file->getPathInfo($fileName);

                $this->ftpHelper->uploadFile(
                    $fileName,
                    $pathInfo['basename']
                );
            }
        }
    }
}
