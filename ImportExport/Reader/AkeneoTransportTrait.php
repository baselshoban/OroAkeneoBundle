<?php

namespace Oro\Bundle\AkeneoBundle\ImportExport\Reader;

use Oro\Bundle\AkeneoBundle\Integration\AkeneoTransport;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;

trait AkeneoTransportTrait
{
    /** @var AkeneoTransport */
    protected $akeneoTransport;

    /** @var ConnectorContextMediator */
    protected $contextMediator;

    public function setContextMediator(ConnectorContextMediator $contextMediator)
    {
        $this->contextMediator = $contextMediator;
    }

    public function getAkeneoTransport(ContextInterface $context)
    {
        if (null === $this->akeneoTransport) {
            $this->initTransport($context);
        }

        return $this->akeneoTransport;
    }

    protected function initTransport(ContextInterface $context)
    {
        $this->akeneoTransport = $this->contextMediator->getTransport($context, true);
        $channel = $this->contextMediator->getChannel($context);

        $this->akeneoTransport->init($channel->getTransport());
    }
}
