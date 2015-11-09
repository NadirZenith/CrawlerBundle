<?php

namespace Nz\CrawlerBundle\Crawler;

use Nz\CrawlerBundle\Model\LinkManagerInterface;
use Nz\CrawlerBundle\Client\IndexClientInterface;
use Nz\CrawlerBundle\Client\EntityClientInterface;

interface HandlerInterface
{

    public function handleIndexClient(IndexClientInterface $client);

    public function handleEntityClient(EntityClientInterface $client);

    public function setLinkManager(LinkManagerInterface $linkManager);

    public function getLinkManager();
}
