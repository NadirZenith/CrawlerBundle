<?php

namespace Nz\CrawlerBundle\Crawler;

use Nz\CrawlerBundle\Entity\Link;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Nz\CrawlerBundle\Client\IndexClientInterface;
use Nz\CrawlerBundle\Client\EntityClientInterface;
use Nz\CrawlerBundle\Client\EntityClientException;

/**
 * Crawl handler
 */
class Handler extends BaseHandler implements HandlerInterface
{

    protected $errors = [];
    protected $links = [];

    /**
     * {@inheritdoc}
     */
    public function handleIndexClient(IndexClientInterface $client, $persist = false)
    {
        $this->links = [];
        $this->errors = [];

        while ($urls = $client->getNextIndexUrls()) {

            foreach ($urls as $url) {
                $link = new Link();

                $link->setUrl($url);

                $this->links[] = $link;

                if ($persist) {

                    $this->persistLink($link);
                }
            }
        }

        return $this->links;
    }

    /**
     * {@inheritdoc}
     */
    public function handleEntityClient(EntityClientInterface $client, $persist = false)
    {

        $link = $client->getLink();

        $link->setProcessed(true);

        $this->links[] = $link;

        try {

            $entity = $client->crawlToEntity($this->getNewEntity());

            if (!$entity) {

                $link->setHasError(true);
                $link->setNote('error_crawling_entity', sprintf('entity with error: %s', $entity->getTitle()));
            } else {
                $link->setHasError(false);
                $link->setNote('crawled_entity', sprintf('crawled entity: %s', $entity->getTitle()));
            }

            if ($persist) {

                $this->persistEntity($entity);

                $link->setNote('created_entity', sprintf('created entity: %d', $entity->getId()));
                $this->persistLink($link);

                $client->afterEntityPersist($entity);
            }

            /* $client->setLink($link); */
            return $entity;
        } catch (UniqueConstraintViolationException $ex) {

            $link->setNote('duplicate_entity_ex', $ex->getMessage());
        } catch (\Doctrine\DBAL\Exception\NotNullConstraintViolationException $ex) {

            $link->setNote('not_null_exception', 'Entity with required field empty');
        } catch (EntityClientException $ex) {

            $link->setNote('entity_client_exeption', $ex->getMessage());
        } catch (\Buzz\Exception\RequestException $ex) {

            $link->setNote('error_requesting_remote', $ex->getMessage());
        }catch (\Exception $ex) {

            $link->setNote('exception', $ex->getMessage());
            $link->setSkip(true);
        }
        $link->setHasError(true);

        if ($persist) {
            $this->persistLink($link);
        }

        return false;
    }

    protected function getNewEntity()
    {
        return new \AppBundle\Entity\News\Post();
    }
}
