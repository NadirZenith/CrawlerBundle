<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nz\CrawlerBundle\Entity;

use Doctrine\ORM\EntityRepository;

class LinkRepository extends EntityRepository
{

    public function findFromHost($host)
    {
        $qb = $this->createQueryBuilder('l');

        $query = $qb
            ->select('l')
            ->where($qb->expr()->like('l.url', ':host'))
            ->setParameter('host', '%' . $host . '%')
            ->getQuery()
        ;

        return $query->execute();
    }

    public function findLinksForProcess($limit )
    {
        $qb = $this->createQueryBuilder('l');

        $qb
            ->select('l')
            ->where('l.processed = false')
            ->andWhere('l.hasError = false')
        ;

        if ($limit) {
            $qb
                ->setMaxResults($limit)
            ;
        }

        $query = $qb
            ->getQuery()
        ;

        return $query->execute();
    }

    /**
     * return last post query builder.
     *
     * @param int $limit
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findLastPostQueryBuilder()
    {
        return $this->createQueryBuilder('p')
                ->where('p.enabled = true')
                ->orderby('p.createdAt', 'DESC');
    }

    /**
     * return count comments QueryBuilder.
     *
     * @param  Sonata\NewsBundle\Model\PostInterface
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function countCommentsQuery($post)
    {
        return $this->getEntityManager()->createQuery('SELECT COUNT(c.id)
                                          FROM Application\Sonata\NewsBundle\Entity\Comment c
                                          WHERE c.status = 1
                                          AND c.post = :post')
                ->setParameters(array('post' => $post));
    }
}
