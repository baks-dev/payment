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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;



use BaksDev\Contacts\Region\Type\Call\ContactsRegionCallType;
use BaksDev\Contacts\Region\Type\Call\ContactsRegionCallUid;
use BaksDev\Contacts\Region\Type\Call\Email\ContactRegionEmail;
use BaksDev\Contacts\Region\Type\Call\Email\ContactRegionEmailType;
use BaksDev\Contacts\Region\Type\Call\Gps\ContactRegionGps;
use BaksDev\Contacts\Region\Type\Call\Gps\ContactRegionGpsType;
use BaksDev\Contacts\Region\Type\Call\Phone\ContactsRegionCallPhoneType;
use BaksDev\Contacts\Region\Type\Call\Phone\ContactsRegionCallPhoneUid;
use BaksDev\Contacts\Region\Type\Event\ContactsRegionEventType;
use BaksDev\Contacts\Region\Type\Event\ContactsRegionEventUid;
use BaksDev\Contacts\Region\Type\Id\ContactsRegionType;
use BaksDev\Contacts\Region\Type\Id\ContactsRegionUid;
use BaksDev\Payment\Type\Cover\PaymentCoverType;
use BaksDev\Payment\Type\Cover\PaymentCoverUid;
use BaksDev\Payment\Type\Event\PaymentEventType;
use BaksDev\Payment\Type\Event\PaymentEventUid;
use BaksDev\Payment\Type\Field\PaymentFieldType;
use BaksDev\Payment\Type\Field\PaymentFieldUid;
use BaksDev\Payment\Type\Id\PaymentType;
use BaksDev\Payment\Type\Id\PaymentUid;
use Symfony\Config\DoctrineConfig;

return static function(ContainerConfigurator $container, DoctrineConfig $doctrine) {
	
	$doctrine->dbal()->type(PaymentUid::TYPE)->class(PaymentType::class);
	$doctrine->dbal()->type(PaymentEventUid::TYPE)->class(PaymentEventType::class);
	$doctrine->dbal()->type(PaymentFieldUid::TYPE)->class(PaymentFieldType::class);
	$doctrine->dbal()->type(PaymentCoverUid::TYPE)->class(PaymentCoverType::class);


	$emDefault = $doctrine->orm()->entityManager('default');
	
	$emDefault->autoMapping(true);
	$emDefault->mapping('Payment')
		->type('attribute')
		->dir(__DIR__.'/../../Entity')
		->isBundle(false)
		->prefix('BaksDev\Payment\Entity')
		->alias('Payment')
	;
};