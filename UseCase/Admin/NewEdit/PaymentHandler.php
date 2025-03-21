<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Payment\UseCase\Admin\NewEdit;

use BaksDev\Core\Entity\AbstractHandler;
use BaksDev\Payment\Entity\Event\PaymentEvent;
use BaksDev\Payment\Entity\Payment;
use BaksDev\Payment\Messenger\PaymentMessage;
use DomainException;

final class PaymentHandler extends AbstractHandler
{
    public function handle(PaymentDTO $command,): string|Payment
    {
        /* Валидация DTO  */
        $this->validatorCollection->add($command);

        $this->main = new Payment($command->getPaymentUid());
        $this->event = new PaymentEvent();

        try
        {
            $command->getEvent() ? $this->preUpdate($command) : $this->prePersist($command);
        }
        catch(DomainException $errorUniqid)
        {
            return $errorUniqid->getMessage();
        }


        /* Загружаем файл обложки */
        if(method_exists($command, 'getCover'))
        {
            /** @var Cover\PaymentCoverDTO $Avatar */
            $Cover = $command->getCover();

            if($Cover->file !== null)
            {
                $DeliveryCover = $this->event->getUploadCover();
                $this->imageUpload->upload($Cover->file, $DeliveryCover);
            }
        }


        /* Валидация всех объектов */
        if($this->validatorCollection->isInvalid())
        {
            return $this->validatorCollection->getErrorUniqid();
        }

        $this->entityManager->flush();

        /* Отправляем событие в шину  */
        $this->messageDispatch->dispatch(
            message: new PaymentMessage($this->main->getId(), $this->main->getEvent(), $command->getEvent()),
            transport: 'payment'
        );

        return $this->main;
    }


//    public function _handle(PaymentDTO $command,): string|Payment
//    {
//        /* Валидация DTO */
//        $errors = $this->validator->validate($command);
//
//        if(count($errors) > 0)
//        {
//            /** Ошибка валидации */
//            $uniqid = uniqid('', false);
//            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [self::class.':'.__LINE__]);
//
//            return $uniqid;
//        }
//
//        if($command->getEvent())
//        {
//            $EventRepo = $this->entityManager->getRepository(PaymentEvent::class)->find(
//                $command->getEvent()
//            );
//
//            if($EventRepo === null)
//            {
//                $uniqid = uniqid('', false);
//                $errorsString = sprintf(
//                    'Not found %s by id: %s',
//                    PaymentEvent::class,
//                    $command->getEvent()
//                );
//                $this->logger->error($uniqid.': '.$errorsString);
//
//                return $uniqid;
//            }
//
//            $EventRepo->setEntity($command);
//            $EventRepo->setEntityManager($this->entityManager);
//            $Event = $EventRepo->cloneEntity();
//        }
//        else
//        {
//            $Event = new PaymentEvent();
//            $Event->setEntity($command);
//            $this->entityManager->persist($Event);
//        }
//
//        //        $this->entityManager->clear();
//        //        $this->entityManager->persist($Event);
//
//
//        /* @var Payment $Main */
//        if($Event->getMain())
//        {
//            $Main = $this->entityManager->getRepository(Payment::class)
//                ->findOneBy(['event' => $command->getEvent()]);
//
//            if(empty($Main))
//            {
//                $uniqid = uniqid('', false);
//                $errorsString = sprintf(
//                    'Not found %s by event: %s',
//                    Payment::class,
//                    $command->getEvent()
//                );
//                $this->logger->error($uniqid.': '.$errorsString);
//
//                return $uniqid;
//            }
//        }
//        else
//        {
//            $Main = new Payment();
//            $this->entityManager->persist($Main);
//            $Event->setMain($Main);
//        }
//
//
//        /** Загружаем файл обложки.
//         *
//         * @var Cover\PaymentCoverDTO $Cover
//         */
//        $Cover = $command->getCover();
//
//        if($Cover->file !== null)
//        {
//            $PaymentCover = $Cover->getEntityUpload();
//            $this->imageUpload->upload($Cover->file, $PaymentCover);
//        }
//
//        /* присваиваем событие корню */
//        $Main->setEvent($Event);
//
//
//        /**
//         * Валидация Event
//         */
//
//        $errors = $this->validator->validate($Event);
//
//        if(count($errors) > 0)
//        {
//            /** Ошибка валидации */
//            $uniqid = uniqid('', false);
//            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [self::class.':'.__LINE__]);
//
//            return $uniqid;
//        }
//
//
//        $this->entityManager->flush();
//
//
//        /* Отправляем событие в шину  */
//        $this->messageDispatch->dispatch(
//            message: new PaymentMessage($Main->getId(), $Main->getEvent(), $command->getEvent()),
//            transport: 'payment'
//        );
//
//
//        return $Main;
//    }
}
