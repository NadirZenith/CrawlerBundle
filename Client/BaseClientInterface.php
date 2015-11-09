<?php

namespace Nz\CrawlerBundle\Client;

use Nz\CrawlerBundle\Model\LinkInterface;

interface BaseClientInterface
{

    /*
     * @param string $host Set host this client handle
      public function setClientHost($host);
     */

    /**
     * @return string Client host
     */
    public function getClientHost();
}
