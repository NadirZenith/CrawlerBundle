<?php

namespace Nz\CrawlerBundle\Client;

use Symfony\Component\DomCrawler\Crawler;

abstract class BaseIndexClient extends BaseClient implements IndexClientInterface
{

    /**
     * The base url to crawl
     *
     * @var string
     */
    protected $baseurl;

    /**
     * The page to start crawling
     *
     * @var int
     */
    private $current_page = 0;

    /**
     * The maximum number of pages to crawl. 0 for all.
     *
     * @var int
     */
    protected $limit_pages = 0;

    /**
     * The css filter path for index links
     *
     * @var string
     */
    protected $index_link_filter;

    public function getCurrentPage()
    {
        return $this->current_page;
    }
    
    /*protected function*/

    public function getNextIndexUrls()
    {
        if ($this->limit_pages && $this->current_page >= $this->limit_pages) {
            return false;
        }

        $nextUrl = $this->getNextPageUrl($this->current_page);
        $this->current_page ++;
        $base_crawler = $this->getBaseCrawler($nextUrl);
        if (!$base_crawler) {
            return false;
        }

        $index_urls = $base_crawler->filter($this->index_link_filter);

        $unfiltered_urls = $this->getArrayAttributes($index_urls, 'href');

        $urls = $this->filterIndexUrls($unfiltered_urls);

        return $urls;
    }

    private function getIndexUrlFrom()
    {
        
    }


    public function filterIndexUrls($urls)
    {
        return $urls;
    }
    
    abstract protected function getNextPageUrl($current_page);
}
