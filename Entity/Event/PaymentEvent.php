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

namespace BaksDev\Payment\Entity\Event;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Payment\Entity\Cover\PaymentCover;
use BaksDev\Payment\Entity\Fields\PaymentField;
use BaksDev\Payment\Entity\Modify\PaymentModify;
use BaksDev\Payment\Entity\Payment;
use BaksDev\Payment\Entity\Trans\PaymentTrans;
use BaksDev\Payment\Type\Event\PaymentEventUid;
use BaksDev\Payment\Type\Id\PaymentUid;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/* PaymentEvent */


#[ORM\Entity]
#[ORM\Table(name: 'payment_event')]
class PaymentEvent extends EntityEvent
{
	public const TABLE = 'payment_event';
	
	/** ID */
	#[ORM\Id]
	#[ORM\Column(type: PaymentEventUid::TYPE)]
	private PaymentEventUid $id;
	
	/** ID Payment */
	#[ORM\Column(type: PaymentUid::TYPE, nullable: false)]
	private ?PaymentUid $payment = null;
	
	/** Обложка способа оплаты */
	#[ORM\OneToOne(targetEntity: PaymentCover::class, mappedBy: 'event', cascade: ['all'])]
	private ?PaymentCover $cover = null;
	
	/** Модификатор */
	#[ORM\OneToOne(targetEntity: PaymentModify::class, mappedBy: 'event', cascade: ['all'])]
	private PaymentModify $modify;
	
	/** Перевод */
	#[ORM\OneToMany(targetEntity: PaymentTrans::class, mappedBy: 'event', cascade: ['all'])]
	private Collection $translate;
	
	/** Перевод */
	#[ORM\OneToMany(targetEntity: PaymentField::class, mappedBy: 'event', cascade: ['all'])]
	private Collection $field;
	
	/** Сортировка */
	#[ORM\Column(type: Types::SMALLINT, length: 3, options: ['default' => 500])]
	private int $sort = 500;
	
	/** Флаг активности */
	#[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
	private bool $active = true;
	
	/** Профиль пользователя, которому доступна оплата */
	#[ORM\Column(type: TypeProfileUid::TYPE, nullable: true)]
	private ?TypeProfileUid $type = null;
	
	
	public function __construct()
	{
		$this->id = new PaymentEventUid();
		$this->modify = new PaymentModify($this);
		
	}
	
	public function __clone()
	{
        $this->id = clone $this->id;
	}
	
	public function __toString(): string
	{
		return (string) $this->id;
	}
	
	public function getId() : PaymentEventUid
	{
		return $this->id;
	}
	
	
	public function setMain(PaymentUid|Payment $payment) : void
	{
		$this->payment = $payment instanceof Payment ? $payment->getId() : $payment;
	}
	
	
	public function getMain() : ?PaymentUid
	{
		return $this->payment;
	}
	
	
	public function getDto($dto): mixed
	{
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

		if($dto instanceof PaymentEventInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function setEntity($dto): mixed
	{
		if($dto instanceof PaymentEventInterface || $dto instanceof self)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	public function getNameByLocale(Locale $locale) : ?string
	{
		$name = null;
		
		/** @var PaymentTrans $trans */
		foreach($this->translate as $trans)
		{
			if($name = $trans->name($locale))
			{
				break;
			}
		}
		
		return $name;
	}
	
	
	public function getUploadCover() : PaymentCover
	{
		return $this->cover ?: $this->cover = new PaymentCover($this);
	}
}