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
use BaksDev\Payment\Entity\Cover\PaymentCover;
use BaksDev\Payment\Entity\Event\PaymentEvent;
use BaksDev\Payment\Entity\Payment;
use BaksDev\Payment\Entity\Trans\PaymentTrans;
use BaksDev\Users\Profile\TypeProfile\Entity\Event\TypeProfileEvent;
use BaksDev\Users\Profile\TypeProfile\Entity\Trans\TypeProfileTrans;
use BaksDev\Users\Profile\TypeProfile\Entity\TypeProfile;

final class AllPaymentsRepository implements AllPaymentsInterface
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


        $qb
            ->select('payment.id')
            ->addSelect('payment.event')
            ->from(Payment::class, 'payment');

        $qb
            ->addSelect('event.sort AS payment_sort')
            ->addSelect('event.active AS payment_active')
            ->join(
                'payment',
                PaymentEvent::class,
                'event',
                'event.id = payment.event'
            );

        $qb
            ->addSelect('trans.name AS payment_name')
            ->addSelect('trans.description AS payment_description')
            ->leftJoin(
                'event',
                PaymentTrans::class,
                'trans',
                'trans.event = event.id AND trans.local = :local'
            );


        /** Обложка */
        $qb
            ->addSelect('cover.ext AS payment_cover_ext')
            ->addSelect('cover.cdn AS payment_cover_cdn')
            ->addSelect("
			CASE
			   WHEN cover.name IS NOT NULL THEN
					CONCAT ( '/upload/".$qb->table(PaymentCover::class)."' , '/', cover.name)
			   ELSE NULL
			END AS payment_cover_name
		"
            )
            ->leftJoin('event',
                PaymentCover::class,
                'cover',
                'cover.event = event.id'
            );


        /** Ограничение профилем */

        $qb->leftJoin('event',
            TypeProfile::class,
            'type_profile',
            'event.type IS NOT NULL AND type_profile.id = event.type'
        );

        $qb
            ->addSelect('type_profile_trans.name AS type_profile_name')
            ->leftJoin(
                'type_profile',
                TypeProfileTrans::class,
                'type_profile_trans',
                'type_profile_trans.event = type_profile.event AND type_profile_trans.local = :local'
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