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

namespace BaksDev\Payment\UseCase\Admin\NewEdit;

use BaksDev\Core\Services\Messenger\MessageDispatchInterface;
use BaksDev\Files\Resources\Upload\Image\ImageUploadInterface;
use BaksDev\Payment\Entity;
use BaksDev\Payment\Messenger\PaymentMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PaymentHandler
{
    private EntityManagerInterface $entityManager;

    private ValidatorInterface $validator;

    private LoggerInterface $logger;

    private ImageUploadInterface $imageUpload;

    private MessageDispatchInterface $messageDispatch;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        ImageUploadInterface $imageUpload,
        MessageDispatchInterface $messageDispatch,
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->imageUpload = $imageUpload;
        $this->messageDispatch = $messageDispatch;
    }

    public function handle(
        PaymentDTO $command,
    ): string|Entity\Payment {
        /* Валидация */
        $errors = $this->validator->validate($command);

        if (count($errors) > 0)
        {
            $uniqid = uniqid('', false);
            $errorsString = (string) $errors;
            $this->logger->error($uniqid.': '.$errorsString);

            return $uniqid;
        }

        if ($command->getEvent())
        {
            $EventRepo = $this->entityManager->getRepository(Entity\Event\PaymentEvent::class)->find(
                $command->getEvent()
            );

            if ($EventRepo === null)
            {
                $uniqid = uniqid('', false);
                $errorsString = sprintf(
                    'Not found %s by id: %s',
                    Entity\Event\PaymentEvent::class,
                    $command->getEvent()
                );
                $this->logger->error($uniqid.': '.$errorsString);

                return $uniqid;
            }

            $Event = $EventRepo->cloneEntity();
        } else
        {
            $Event = new Entity\Event\PaymentEvent();
            $this->entityManager->persist($Event);
        }

        $this->entityManager->clear();

        /* @var Entity\Payment $Main */
        if ($Event->getMain())
        {
            $Main = $this->entityManager->getRepository(Entity\Payment::class)->findOneBy(
                ['event' => $command->getEvent()]
            );

            if (empty($Main))
            {
                $uniqid = uniqid('', false);
                $errorsString = sprintf(
                    'Not found %s by event: %s',
                    Entity\Payment::class,
                    $command->getEvent()
                );
                $this->logger->error($uniqid.': '.$errorsString);

                return $uniqid;
            }
        } else
        {
            $Main = new Entity\Payment();
            $this->entityManager->persist($Main);
            $Event->setMain($Main);
        }

        $Event->setEntity($command);
        $this->entityManager->persist($Event);

        /** Загружаем файл обложки.
         *
         * @var Cover\PaymentCoverDTO $Cover
         */
        $Cover = $command->getCover();

        if ($Cover->file !== null)
        {
            $PaymentCover = $Cover->getEntityUpload();
            $this->imageUpload->upload($Cover->file, $PaymentCover);
        }

        /* присваиваем событие корню */
        $Main->setEvent($Event);
        $this->entityManager->flush();


        /* Отправляем событие в шину  */
        $this->messageDispatch->dispatch(
            message: new PaymentMessage($Main->getId(), $Main->getEvent(), $command->getEvent()),
            transport: 'payment'
        );


        return $Main;
    }
}
