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

namespace Adspray\Adabra\Command;

use Adspray\Adabra\Api\FeedManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Run extends Command
{
    protected $feedManager;
    protected $objectManager;

    public function __construct(
        FeedManagerInterface $feedManager,
        ObjectManagerInterface $objectManager
    ) {
        parent::__construct();

        $this->feedManager = $feedManager;
        $this->objectManager = $objectManager;
    }

    protected function configure()
    {
        $this->setName('adabra:feed:run');
        $this->setDescription('Run Adabra Export Task');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $appState = $this->objectManager->get('Magento\Framework\App\State');
        $appState->setAreaCode('adminhtml');

        $this->feedManager->run();
    }
}
