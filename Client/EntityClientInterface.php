<?php

namespace Nz\CrawlerBundle\Client;

use Nz\CrawlerBundle\Model\LinkInterface;

interface EntityClientInterface extends BaseClientInterface
{

    /**
     * @param LinkInterface $link Set link to handle
     */
    public function setLink(LinkInterface $link);

    /**
     * @return string Client link
     */
    public function getLink();

    /*
     * @param string $host Set host this client handle
      public function setClientHost($host);
     */

    /**
     * @return string Client host
     */
    public function getClientHost();

    /**
     *  Receives a object entity
     * 
     *  @param Object $entity Entity to crawl to
     * 
     *  @return Object Entity Ready to persist entity
     */
    public function crawlToEntity($entity);

    /**
     * @return string Client host
     */
    public function afterEntityPersist($entity);
}
