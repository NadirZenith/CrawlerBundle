<?php

namespace Nz\CrawlerBundle\Command;

use Nz\OptionsBundle\Entity\Option;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use ColourStream\Bundle\CronBundle\Annotation\CronJob;

/**
 */
class CrawlLinksCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('nz:crawl:links');
        $this->addOption('persist', null, InputOption::VALUE_OPTIONAL, 'persist', 0);
        $this->setDescription('Crawl Links Command');
    }

    /**
     * Crawl links
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {

        $linkManager = $this->getLinkManager();
        $handler = $this->getHandler();
        $clientPool = $this->getClientPool();

        $persist = $input->getOption('persist');
        $links = $linkManager->findLinksForProcess();
        $errors = [];
        $entities = [];
        ini_set('max_execution_time', 300);
        foreach ($links as $link) {
            $client = $clientPool->getEntityClientForLink($link);

            if ($client) {

                if ($entity = $handler->handleEntityClient($client, $persist)) {
                    $entities[] = $entity->getTitle();
                } else {
                    $notes = $link->getNotes();
                    $errors[] = end($notes);
                }
            } else {
                $output->writeln(sprintf('No Entity Client for link url: %s', $link->getUrl()));
            }
        }

        $output->writeln(sprintf('Links: %s, Success: %s, Errors: %s', count($links), count($entities), count($errors)));
        return;
    }

    /**
     * Get Crawler handler
     * 
     * @return \Nz\CrawlerBundle\Crawler\Handler
     */
    private function getHandler()
    {
        return $this->getContainer()->get('nz.crawler.handler');
    }

    /**
     * Get Link Manager
     * 
     * @return \Nz\CrawlerBundle\Client\ClientPool
     */
    private function getClientPool()
    {
        return $this->getContainer()->get('nz.crawler.client.pool');
    }

    /**
     * Get Link Manager
     * 
     * @return \Nz\CrawlerBundle\Entity\LinkManager
     */
    private function getLinkManager()
    {
        return $this->getContainer()->get('nz.crawler.link.manager');
    }
}
