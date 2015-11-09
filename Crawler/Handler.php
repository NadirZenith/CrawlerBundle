<?php

namespace Nz\CrawlerBundle\Crawler;

use Nz\CrawlerBundle\Entity\Link;
use Nz\CrawlerBundle\Model\LinkManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Nz\CrawlerBundle\Client\IndexClientInterface;
use Nz\CrawlerBundle\Client\EntityClientInterface;
use Sonata\NewsBundle\Entity\PostManager;
use Nz\CrawlerBundle\Client\EntityClientException;

/* use ClientEnti */

class Handler implements HandlerInterface
{

    protected $linkManager;
    protected $em;
    protected $errors = [];
    protected $links = [];

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Handle Index Client
     * 
     * @param IndexClientInterface $client The client
     * @param boolean $persist If should persist Link
     * 
     * @return array Array of links
     */
    public function handleIndexClient(IndexClientInterface $client, $persist = false)
    {
        $this->links = [];
        $this->errors = [];

        while ($urls = $client->getNextIndexUrls()) {
            /* d($urls); */

            foreach ($urls as $url) {
                $link = new Link();

                $link->setUrl($url);

                $this->links[] = $link;

                if ($persist) {

                    $this->persistIndexLink($link);
                }
            }
        }

        return $this->links;
    }

    /**
     * Handle Entity Client
     * 
     * @param EntityClientInterface $client
     * @param boolean $persist whether to persist entity
     * @param boolean $force if true force handle link already processed
     * 
     * @return Entity | boolean Entity on success false otherwise, true on link already processed or entity already exist
     */
    public function handleEntityClient(EntityClientInterface $client, $persist = false, $force = false)
    {

        $postManager = $this->getPostEntityManager();
        $link = $client->getLink();

        if ($link->getProcessed() && !$force) {
            return true;
        }
        $link->setProcessed(true);

        $this->links[] = $link;

        try {

            $entity = $client->crawlToEntity($this->getNewEntity());

            if ($persist) {

                $postManager->persist($entity);
                $postManager->flush();

                $link->setNote('created_entity', sprintf('created entity %d', $entity->getId()));
                $link->setHasError(false);
                $this->saveIndexLink($link, $persist);

                $client->afterEntityPersist($entity);
            }

            return $entity;
        } catch (UniqueConstraintViolationException $ex) {

            $link->setNote('duplicate_entity_title', 'Entity with duplicate title');
        } catch (\Doctrine\DBAL\Exception\NotNullConstraintViolationException $ex) {

            $link->setNote('not_null_exception', 'Entity with required field empty');
        } catch (EntityClientException $ex) {

            $link->setNote('entity_client_exeption', $ex->getMessage());
        }

        $link->setHasError(true);
        $this->saveIndexLink($link, $persist);

        return false;
    }

    protected function getNewEntity()
    {
        return new \AppBundle\Entity\News\Post();
    }

    private function saveIndexLink(Link $link, $force = false)
    {
        if (null === $link->getId() && !$force) {
            return;
        }

        if (null === $link->getId() && $force) {
            return $this->persistIndexLink($link);
        }


        $em = $this->getEntityManager();

        $em->merge($link);

        $em->flush();
    }

    private function persistIndexLink(Link $link)
    {
        $em = $this->getEntityManager();

        $em->persist($link);

        try {

            $em->flush();

            return true;
        } catch (UniqueConstraintViolationException $ex) {

            $this->errors[] = array_pop($this->links);
            $link->setNote('duplicate_link_url', sprintf('Duplicate link url: %s', $link->getUrl()));

            return false;
        }
    }

    private function getEntityManager()
    {
        $em = $this->getLinkManager()->getEntityManager();

        if (!$em->isOpen()) {
            $em = $em->create(
                $em->getConnection(), $em->getConfiguration());
        }

        return $em;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getLinkManager()
    {
        return $this->linkManager;
    }

    public function setLinkManager(LinkManagerInterface $linkManager)
    {
        $this->linkManager = $linkManager;
    }

    public function setPostManager(PostManager $postManager)
    {
        $this->postManager = $postManager;
    }

    public function getPostEntityManager()
    {
        $em = $this->postManager->getEntityManager();

        if (!$em->isOpen()) {
            $em = $em->create(
                $em->getConnection(), $em->getConfiguration());
        }

        return $em;
        return $this->postManager;
    }
}
