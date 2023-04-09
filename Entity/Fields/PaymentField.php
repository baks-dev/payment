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

namespace BaksDev\Payment\Entity\Fields;

use BaksDev\Core\Type\Field\InputField;
use BaksDev\Payment\Entity\Event\PaymentEvent;
use BaksDev\Payment\Entity\Fields\Trans\PaymentFieldTrans;
use BaksDev\Payment\Type\Field\PaymentFieldUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Entity\EntityState;
use InvalidArgumentException;

/* Перевод PaymentField */


#[ORM\Entity]
#[ORM\Table(name: 'payment_field')]
//#[ORM\Index(columns: ['name'])]
class PaymentField extends EntityEvent
{
	public const TABLE = 'payment_field';
	
	/** ID */
	#[ORM\Id]
	#[ORM\Column(type: PaymentFieldUid::TYPE)]
	private PaymentFieldUid $id;
	
	/** Связь на поле */
	#[ORM\ManyToOne(targetEntity: PaymentEvent::class, inversedBy: "field")]
	#[ORM\JoinColumn(name: 'event', referencedColumnName: "id")]
	private PaymentEvent $event;
	
	/** Перевод полей для заполнения */
	#[ORM\OneToMany(mappedBy: 'field', targetEntity: PaymentFieldTrans::class, cascade: ['all'])]
	private Collection $translate;
	
	/** Тип поля (input, select, textarea ....)  */
	#[ORM\Column(type: InputField::TYPE, length: 32, options: ['default' => 'input_field'])]
	private InputField $type;
	
	/** Обязательное к заполнению */
	#[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
	private bool $required = true;
	
	/** Сортировка */
	#[ORM\Column(type: Types::SMALLINT, length: 3, options: ['default' => 100])]
	private int $sort = 100;
	
	
	
	public function __construct(PaymentEvent $event)
	{
		$this->id = new PaymentFieldUid();
		$this->event = $event;
	}
	
	public function __clone() : void
	{
		$this->id = new PaymentFieldUid();
	}
	
	
	public function getDto($dto) : mixed
	{
		if($dto instanceof PaymentFieldInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function setEntity($dto) : mixed
	{
		
		if($dto instanceof PaymentFieldInterface)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	
}