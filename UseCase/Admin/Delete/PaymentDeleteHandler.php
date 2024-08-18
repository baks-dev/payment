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

namespace BaksDev\Payment\UseCase\Admin\Delete;

use BaksDev\Payment\Entity;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PaymentDeleteHandler
{
	private EntityManagerInterface $entityManager;
	
	private ValidatorInterface $validator;
	
	private LoggerInterface $logger;
	
	
	public function __construct(
		EntityManagerInterface $entityManager,
		ValidatorInterface $validator,
		LoggerInterface $logger,
	)
	{
		$this->entityManager = $entityManager;
		$this->validator = $validator;
		$this->logger = $logger;
	}
	
	
	public function handle(
		PaymentDeleteDTO $command,
	): string|Entity\Payment
	{
		
		/* Валидация DTO */
		$errors = $this->validator->validate($command);
		
		if(count($errors) > 0)
		{
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [self::class.':'.__LINE__]);

			return $uniqid;
		}

		/* Обязательно передается идентификатор события */
		if($command->getEvent() === null)
		{
			$uniqid = uniqid('', false);
			$errorsString = sprintf(
				'Not found event id in class: %s',
				$command::class
			);
			$this->logger->error($uniqid.': '.$errorsString);
			
			return $uniqid;
		}
		
		
		/**
         * Получаем событие
         */
		$Event = $this->entityManager->getRepository(Entity\Event\PaymentEvent::class)->find(
			$command->getEvent()
		);
		
		if($Event === null)
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


        /* Применяем изменения к событию */
        $Event->setEntity($command);
        $this->entityManager->persist($Event);



		/**
         * Получаем корень агрегата
         */
		$Main = $this->entityManager->getRepository(Entity\Payment::class)->findOneBy(
			['event' => $command->getEvent()]
		);
		
		if(empty($Main))
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


        /**
         * Валидация Event
         */

        $errors = $this->validator->validate($Event);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [self::class.':'.__LINE__]);

            return $uniqid;
        }

		
		/* Удаляем корень агрегата */
		$this->entityManager->remove($Main);
		
		$this->entityManager->flush();
		
		return $Main;
	
	}
	
}