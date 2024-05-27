<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Payment\Repository\AllPayments;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Payment\Entity as PaymentEntity;
use BaksDev\Users\Profile\TypeProfile\Entity as TypeProfileEntity;

final class AllPayments implements AllPaymentsInterface
{
    private PaginatorInterface $paginator;

    private DBALQueryBuilder $DBALQueryBuilder;


    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
        PaginatorInterface $paginator,
    )
    {

        $this->paginator = $paginator;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }


    public function fetchAllPaymentsAssociative(SearchDTO $search): PaginatorInterface
    {
        $qb = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();


        $qb->select('payment.id');
        $qb->addSelect('payment.event');
        $qb->from(PaymentEntity\Payment::TABLE, 'payment');

        $qb->addSelect('event.sort AS payment_sort');
        $qb->addSelect('event.active AS payment_active');
        $qb->join('payment', PaymentEntity\Event\PaymentEvent::TABLE, 'event', 'event.id = payment.event');

        $qb->addSelect('trans.name AS payment_name');
        $qb->addSelect('trans.description AS payment_description');

        $qb->leftJoin('event',
            PaymentEntity\Trans\PaymentTrans::TABLE,
            'trans',
            'trans.event = event.id AND trans.local = :local'
        );


        /** Обложка */
        $qb->addSelect('cover.ext AS payment_cover_ext');
        $qb->addSelect('cover.cdn AS payment_cover_cdn');

        $qb->addSelect("
			CASE
			   WHEN cover.name IS NOT NULL THEN
					CONCAT ( '/upload/".PaymentEntity\Cover\PaymentCover::TABLE."' , '/', cover.name)
			   ELSE NULL
			END AS payment_cover_name
		"
        );


        $qb->leftJoin('event',
            PaymentEntity\Cover\PaymentCover::TABLE,
            'cover',
            'cover.event = event.id'
        );

        /** Ограничение профилем */


        $qb->leftJoin('event',
            TypeProfileEntity\TypeProfile::TABLE,
            'type_profile',
            'event.type IS NOT NULL AND type_profile.id = event.type'
        );

        $qb->leftJoin('type_profile',
            TypeProfileEntity\Event\TypeProfileEvent::TABLE,
            'type_profile_event',
            'type_profile_event.id = type_profile.event'
        );

        $qb->addSelect('type_profile_trans.name AS type_profile_name');

        $qb->leftJoin('type_profile_event',
            TypeProfileEntity\Trans\TypeProfileTrans::TABLE,
            'type_profile_trans',
            'type_profile_trans.event = type_profile_event.id AND type_profile_trans.local = :local'
        );


        /* Поиск */
        if($search->getQuery())
        {
            $qb
                ->createSearchQueryBuilder($search)
                ->addSearchLike('trans.name')
                ->addSearchLike('trans.description');
        }

        return $this->paginator->fetchAllAssociative($qb);

    }

}