<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nz\CrawlerBundle\Admin;

use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Nz\CrawlerBundle\Model\LinkManagerInterface;
use Sonata\AdminBundle\Route\RouteCollection;

class LinkAdmin extends Admin
{

    /**
     * @var LinkManagerInterface
     */
    protected $linkManager;
    protected $maxPerPage = 300;

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('dev', 'dev');
        //crawl indexes // on top
        $collection->add('crawl-indexes', 'crawl-indexes');
        $collection->add('crawl-links', 'crawl-links');
        $collection->add('crawl-url', 'crawl-url');
        //crawl link // on list
        $collection->add('crawl-link', $this->getRouterIdParameter() . '/crawl');
    }

    /**
     * Default values to the datagrid.
     *
     * @var array
     */
    protected $datagridValues = array(
        '_page' => 1,
        '_per_page' => 320,
        '_sort_order' => 'DESC',
        // name of the ordered field (default = the model's id field, if any)
        '_sort_by' => 'crawledAt',
    );

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('url')
            ->add('processed')
            ->add('hasError')
            ->add('notes')
            ->add('crawledAt')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Option', array(
                'class' => 'col-md-8',
            ))
            ->add('url', 'url')
            ->add('processed')
            ->add('hasError')
            /* ->add('notes') */
            ->add('crawledAt')
            ->end()

        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {//list
        $object = $this->getSubject();
        /* dd($object); */
        $listMapper
            /* ->add('custom', 'string', array('template' => 'SonataNewsBundle:Admin:list_post_custom.html.twig', 'label' => 'Post')) */
            ->addIdentifier('url')
            /* ->add('processed') */
            ->add('processed', null)
            /*->add('processed', null, array('editable' => true))*/
            ->add('hasError', null, array('editable' => true))
            ->add('crawledAt')
            /*       custom actions     */
            ->add('_action', 'crawl', array(
                'actions' => array(
                    'Crawl' => array(
                        'template' => 'NzCrawlerBundle:CRUD:list__action_crawl.html.twig'
                    )
                )
            ))

        ;
    }

    /**
     * {@inheritdoc}
     * List
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $that = $this;

        $datagridMapper
            ->add('url')
            ->add('processed')
            ->add('hasError')
        ;
    }
    /**
     * {@inheritdoc}
      } */

    /**
     * {@inheritdoc}
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, array('edit'))) {
            return;
        }
        /*
          $admin = $this->isChild() ? $this->getParent() : $this;

          $id = $admin->getRequest()->get('id');

          $menu->addChild(
          $this->trans('sidemenu.link_edit_post'), array('uri' => $admin->generateUrl('edit', array('id' => $id)))
          );

          $menu->addChild(
          $this->trans('sidemenu.link_view_comments'), array('uri' => $admin->generateUrl('sonata.news.admin.comment.list', array('id' => $id)))
          );

          if ($this->hasSubject() && $this->getSubject()->getId() !== null) {
          $menu->addChild(
          $this->trans('sidemenu.link_view_post'), array('uri' => $admin->getRouteGenerator()->generate('sonata_news_view', array('permalink' => $this->permalinkGenerator->generate($this->getSubject()))))
          );
          }
         */
    }

    /**
     * @param OptionManagerInterface $optionManager
     */
    public function setLinkManager(LinkManagerInterface $linkManager)
    {
        $this->linkManager = $linkManager;
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($option)
    {
        
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($option)
    {
        
    }
}
