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

use Adspray\Adabra\Api\Data\SubFeedInterface;
use Adspray\Adabra\Helper\Data as DataHelper;
use Adspray\Adabra\Helper\Ftp as FtpHelper;
use Adspray\Adabra\Helper\Filesystem;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Io\File;
use Magento\Directory\Helper\Data as DirectoryHelperData;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Newsletter\Model\Subscriber;

class CustomerSubFeed extends AbstractSubFeed implements SubFeedInterface
{
    protected $type = 'customer';
    protected $scope = 'website';
    protected $exportName = 'customers';

    protected $collectionFactory;
    protected $subscriber;

    public function __construct(
        File $file,
        Csv $csv,
        Filesystem $filesystem,
        DataHelper $dataHelper,
        FtpHelper $ftpHelper,
        DirectoryHelperData $directoryHelperData,
        DateTime $dateTime,
        CollectionFactory $collectionFactory,
        Subscriber $subscriber
    ) {
        parent::__construct($file, $csv, $filesystem, $dataHelper, $ftpHelper, $directoryHelperData, $dateTime);

        $this->collectionFactory = $collectionFactory;
        $this->subscriber = $subscriber;
    }

    /**
     * Get headers
     * @return array
     */
    protected function getHeaders()
    {
        return [
            'id_utente',
            'email',
            'nome',
            'cognome',
            'citta',
            'cap',
            'indirizzo',
            'provincia',
            'regione',
            'stato',
            'cellulare',
            'telefono',
            'sesso',
            'nascita_anno',
            'nascita_mese',
            'nascita_giorno',
            'f_business',
            'azienda_nome',
            'azienda_categoria',
            'f_ricevi_newsletter',
            'f_ricevi_comunicazioni_commerciali',
            'data_iscrizione',
            'data_cancellazione',
            'ip',
            'user_agent',
            'f_attivo',
            'f_cancellato',
        ];
    }

    /**
     * Prepare feed collection
     * @return void
     */
    protected function prepareCollection()
    {
        $this->collection = $this->collectionFactory->create();
        $this->collection
            ->addAttributeToSelect('*')
            ->addFieldToFilter('website_id', $this->getFeed()->getWebsite()->getId());
    }

    /**
     * Get virtual field value
     * @param Customer $customer
     * @param $field
     * @return string
     */
    public function getVirtualField(Customer $customer, $field)
    {
        // TODO: Virtual fields mapping
        return $customer->getData($field);
    }

    /**
     * Check if a customer has a newsletter subscription
     * @param Customer $customer
     * @return boolean
     */
    protected function getIsNewsletterSubscriber(Customer $customer)
    {
        return $this->subscriber->loadByEmail($customer->getEmail())->isSubscribed();
    }

    /**
     * Get feed row for entity
     * @param $entity
     * @return array
     */
    protected function getFeedRow($entity)
    {
        /** @var $customer Customer */
        $customer = $entity;

        $shippingAddress = $customer->getDefaultShippingAddress();
        $billingAddress = $customer->getDefaultBillingAddress();

        if ($customer->getDob()) {
            $dob = preg_split('/\D+/', $customer->getDob());
        } else {
            $dob = [0, 0, 0];
        }

        return [[
            $customer->getId(),
            $customer->getEmail(),
            $customer->getFirstname(),
            $customer->getLastname(),
            $shippingAddress ? $shippingAddress->getCity() : '',
            $shippingAddress ? $shippingAddress->getPostcode() : '',
            $shippingAddress ? $shippingAddress->getStreetFull() : '',
            '',
            $shippingAddress ? $shippingAddress->getRegionCode() : '',
            $shippingAddress ? $shippingAddress->getCountry() : '',
            '',
            $shippingAddress ? $shippingAddress->getTelephone() : '',
            $customer->getGender() == 2 ? 'f' : 'm',
            $dob[0],
            $dob[1],
            $dob[2],
            $this->getVirtualField($customer, 'f_business'),
            $billingAddress ? $billingAddress->getCompany() : '',
            $this->getVirtualField($customer, 'azienda_categoria'),
            $this->toBoolean($this->getIsNewsletterSubscriber($customer)),
            $this->toBoolean($this->getVirtualField($customer, 'f_ricevi_comunicazioni_commerciali')),
            $this->toTimestamp2($customer->getCreatedAtTimestamp()),
            '',
            '',
            '',
            $this->toBoolean(true),
            $this->toBoolean(false),
        ]];
    }
}
