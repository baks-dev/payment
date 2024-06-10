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

namespace BaksDev\Payment\Repository\PaymentByTypeProfileChoice;

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Orders\Order\Repository\PaymentByTypeProfileChoice\PaymentByTypeProfileChoiceInterface;
use BaksDev\Payment\Entity as PaymentEntity;
use BaksDev\Payment\Type\Id\PaymentUid;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class PaymentByTypeProfileChoiceRepository implements PaymentByTypeProfileChoiceInterface
{
    private EntityManagerInterface $entityManager;

    private TranslatorInterface $translator;


    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }


    public function fetchPaymentByProfile(TypeProfileUid $type): ?array
    {

        $qb = $this->entityManager->createQueryBuilder();

        $select = sprintf('new %s(payment.id, trans.name, trans.description)', PaymentUid::class);

        $qb->select($select);

        $qb->from(PaymentEntity\Payment::class, 'payment', 'payment.id');

        $qb->join(PaymentEntity\Event\PaymentEvent::class, 'event', 'WITH', 'event.id = payment.event AND (event.type IS NULL OR event.type = :type)');

        $qb->leftJoin(PaymentEntity\Trans\PaymentTrans::class, 'trans', 'WITH', 'trans.event = payment.event AND trans.local = :local');


        $qb->setParameter('type', $type, TypeProfileUid::TYPE);
        $qb->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);


        $qb->orderBy('event.sort');

        return $qb->getQuery()->getResult();
    }


    public function fetchAllPayment(): ?array
    {

        $qb = $this->entityManager->createQueryBuilder();

        $select = sprintf('new %s(payment.id, trans.name, trans.description)', PaymentUid::class);

        $qb->select($select);

        $qb->from(PaymentEntity\Payment::class, 'payment', 'payment.id');

        $qb->join(PaymentEntity\Event\PaymentEvent::class, 'event', 'WITH', 'event.id = payment.event');

        $qb->leftJoin(PaymentEntity\Trans\PaymentTrans::class, 'trans', 'WITH', 'trans.event = payment.event AND trans.local = :local');


        //$qb->setParameter('type', $type, TypeProfileUid::TYPE);
        $qb->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);


        $qb->orderBy('event.sort');

        return $qb->getQuery()->getResult();
    }
}
