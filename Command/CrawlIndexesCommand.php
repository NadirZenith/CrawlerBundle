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
class CrawlIndexesCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('nz:crawl:indexes');
        $this->addOption('persist', null, InputOption::VALUE_OPTIONAL, 'persist', 0);
        $this->setDescription('Crawl Indexes Command');
    }

    /**
     * Crawl Indexes
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $handler = $this->getHandler();
        $clientPool = $this->getClientPool();
        $clients_indexes = $clientPool->getIndexClients();
        $persist = $input->getOption('persist');

        $links = [];
        $errors = [];
        foreach ($clients_indexes as $client) {
            $l = $handler->handleIndexClient($client, $persist);

            $links = array_merge($links, $l);

            $e = $handler->getErrors();
            $errors = array_merge($errors, $e);
        }


        $info = '';
        foreach ($links as $link) {
            $info .= sprintf('<p>%s</p>', $link->getUrl());
        }

        $error = '';
        foreach ($errors as $err) {
            $notes = $err->getNotes();
            $error .= sprintf('<p>%s</p>', end($notes));
        }

        $output->writeln(sprintf('Clients: %s ', count($clients_indexes)));
        $output->writeln(sprintf('New Links: %s <br> %s', count($links), $info));
        $output->writeln(sprintf('Errors: %s <br> %s', count($errors), $error));
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
}
