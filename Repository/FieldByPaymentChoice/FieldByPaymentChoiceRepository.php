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

namespace BaksDev\Payment\Repository\FieldByPaymentChoice;

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Orders\Order\Repository\FieldByPaymentChoice\FieldByPaymentChoiceInterface;
use BaksDev\Payment\Entity as PaymentEntity;
use BaksDev\Payment\Type\Field\PaymentFieldUid;
use BaksDev\Payment\Type\Id\PaymentUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class FieldByPaymentChoiceRepository implements FieldByPaymentChoiceInterface
{
    private EntityManagerInterface $entityManager;

    private TranslatorInterface $translator;


    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }


    public function fetchPaymentFields(PaymentUid $payment): ?array
    {

        $qb = $this->entityManager->createQueryBuilder();

        $select = sprintf('new %s(field.id, trans.name, trans.description, field.type, field.required)', PaymentFieldUid::class);

        $qb->select($select);

        $qb
            ->from(
                PaymentEntity\Payment::class,
                'payment',
                'payment.id'
            )
            ->where('payment.id = :payment')
            ->setParameter('payment', $payment, PaymentUid::TYPE);

        $qb->join(
            PaymentEntity\Event\PaymentEvent::class,
            'event',
            'WITH',
            'event.id = payment.event'
        );

        $qb->join(
            PaymentEntity\Fields\PaymentField::class,
            'field',
            'WITH',
            'field.event = event.id'
        );

        $qb->leftJoin(
            PaymentEntity\Fields\Trans\PaymentFieldTrans::class,
            'trans',
            'WITH',
            'trans.field = field.id AND trans.local = :local'
        );


        $qb->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);

        $qb->orderBy('field.sort');

        return $qb->getQuery()->getResult();
    }

}
