<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nz\CrawlerBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Nz\CrawlerBundle\Clients;

/**
 * Class CRUDController.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class LinkCRUDController extends Controller
{

    /**
     * default list action
     */
    public function listAction(Request $request = null)
    {
        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }
        $template = $this->admin->getTemplate('list');


        $datagrid = $this->admin->getDatagrid();
        $formView = $datagrid->getForm()->createView();

        return $this->render('NzCrawlerBundle:CRUD:list.html.twig', array(
                'action' => 'list',
                'mode' => 'list',
                'form' => $formView,
                'datagrid' => $datagrid,
                'csrf_token' => $this->getCsrfToken('sonata.batch'),
                ), null, $request);
    }

    /**
     * crawl all indexes into new links
     */
    public function crawlIndexesAction(Request $request = null)
    {

        $handler = $this->getHandler();
        $clientPool = $this->getClientPool();

        $clients_indexes = $clientPool->getIndexClients();
        $links = [];
        $errors = [];
        foreach ($clients_indexes as $client) {
            $l = $handler->handleIndexClient($client, true);
            /* $l = $handler->handleIndexClient($client, false); */

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

        $this->addFlash('sonata_flash_info', sprintf('Clients: %s ', count($clients_indexes)));
        $this->addFlash('sonata_flash_success', sprintf('New Links: %s <br> %s', count($links), $info));
        $this->addFlash('sonata_flash_error', sprintf('Errors: %s <br> %s', count($errors), $error));

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /**
     * Crawl link
     */
    public function crawlLinkAction($id)
    {
        $link = $this->admin->getSubject();

        if (!$link) {
            throw new NotFoundHttpException(sprintf('unable to find the link with id : %s', $id));
        }

        $clientPool = $this->getClientPool();
        $client = $clientPool->getEntityClientForLink($link);
        if (!$client) {
            $this->addFlash('sonata_flash_error', sprintf('No client for url: %s', $link->getUrl()));
            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        $handler = $this->getHandler();
        $entity = $handler->handleEntityClient($client, true, true);

        if (!$entity) {
            $notes = $link->getNotes();
            $error = end($notes);

            $this->addFlash('sonata_flash_error', sprintf('Error creating entity: %s', $error));
        } elseif ($entity === true) {

            $this->addFlash('sonata_flash_success', 'Entity already created');
        } else {

            $this->addFlash('sonata_flash_success', sprintf('Created entity with id %s', $entity->getId()));
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /**
     * Crawl not processed links
     */
    public function crawlLinksAction(Request $request = null)
    {
        $linkManager = $this->getLinkManager();

        $handler = $this->getHandler();
        $links = $linkManager->findLinksForProcess();
        $clientPool = $this->getClientPool();

        $errors = [];
        $entities = [];
        ini_set('max_execution_time', 300);
        foreach ($links as $link) {
            $client = $clientPool->getEntityClientForLink($link);

            if ($client) {
                $entity = $handler->handleEntityClient($client, true);

                if (!$entity) {
                    $notes = $link->getNotes();
                    $errors[] = end($notes);
                } else {
                    $entities[] = $entity->getTitle();
                }
            }
        }

        $this->addFlash('sonata_flash_info', sprintf('Links: %s, Success: %s, Errors: %s', count($links), count($entities), count($errors)));

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /**
     * Crawl link
     */
    public function crawlUrlAction(Request $request)
    {

        $url = $request->get('url');
        $persist = $request->get('persist', false);

        if (!$url) {
            throw new NotFoundHttpException('No url supplied');
        }

        $link = new \Nz\CrawlerBundle\Entity\Link();
        $link->setUrl($url);

        $clientPool = $this->getClientPool();
        $handler = $this->getHandler();

        $client = $clientPool->getEntityClientForLink($link);
        if (!$client) {
            $this->addFlash('sonata_flash_error', sprintf('No client for url: %s', $link->getUrl()));
            return new RedirectResponse($this->admin->generateUrl('list'));
        }
        $entity = $handler->handleEntityClient($client, $persist, false);

        if (!$entity) {
            $notes = $link->getNotes();
            $error = end($notes);

            $this->addFlash('sonata_flash_error', sprintf('Error creating entity: %s', $error));
        } elseif ($entity === true) {
            $this->addFlash('sonata_flash_info', 'Entity already created');
        } else {
            $this->addFlash('sonata_flash_success', sprintf('Created entity with id %s, title %s', $entity->getId(), $entity->getTitle()));
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /**
     * Get Crawler handler
     * 
     * @return \Nz\CrawlerBundle\Crawler\Handler
     */
    private function getHandler()
    {
        return $this->get('nz.crawler.handler');
    }

    /**
     * Get Link Manager
     * 
     * @return \Nz\CrawlerBundle\Client\ClientPool
     */
    private function getClientPool()
    {
        return $this->get('nz.crawler.client.pool');
    }

    /**
     * Get Link Manager
     * 
     * @return \Nz\CrawlerBundle\Entity\LinkManager
     */
    private function getLinkManager()
    {
        return $this->get('nz.crawler.link.manager');
    }

    /**
     * Get Link Manager
     * 
     * @return \Nz\CrawlerBundle\Entity\LinkManager
     */
    private function getMediaManager()
    {
        return $this->get('sonata.media.manager.media');
    }

    /**
     * Dev action
     * 
     * return \Nz\CrawlerBundle\Entity\LinkManager
     */
    public function devProviderAction()
    {

        $mediaManager = $this->getMediaManager();
        $em = $mediaManager->getEntityManager();

        $url = 'http://rd3.videos.sapo.pt/playhtml?file=http://rd3.videos.sapo.pt/3YFfKmdLm4eL7KineB9C/mov/1';

        $media = new \AppBundle\Entity\Media\Media();
        $media->setBinaryContent($url);
        $media->setContext('default');
        $media->setEnabled(true);
        $media->setProviderName('sonata.media.provider.sapo');

        try {
            $em->persist($media);
            $em->flush();
        } catch (Exception $ex) {
            d($ex);
        }
        d($media);

        die('... end ...');
    }

    public function devAction()
    {
        die('dev end');

        $url = 'http://www.tabonito.pt/um-dos-bares-mais-bonitos-do-mundo-fica-nos-acores';
        $persist = false;

        $link = new \Nz\CrawlerBundle\Entity\Link();
        $link->setUrl($url);

        $clientPool = $this->getClientPool();
        $handler = $this->getHandler();


        $client = $clientPool->getEntityClientForLink($link);
        if (!$client) {
            die('no client');
        }

        $entity = $handler->handleEntityClient($client, $persist, false);
        dd($entity);


        die('end');
    }
}
