<?php

namespace Nz\CrawlerBundle\Client;

interface IndexClientInterface
{

    /**
     * Get next index urls
     * 
     * @return array The next urls array
     */
    public function getNextIndexUrls();

    /**
     * Filter urls
     */
    public function filterIndexUrls($urls);
}
