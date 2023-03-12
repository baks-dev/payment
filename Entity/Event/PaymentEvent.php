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

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Payment\Entity\Cover\PaymentCover;
use BaksDev\Payment\Entity\Fields\PaymentField;
use BaksDev\Payment\Entity\Modify\PaymentModify;
use BaksDev\Payment\Entity\Payment;
use BaksDev\Payment\Entity\Trans\PaymentTrans;
use BaksDev\Payment\Type\Event\PaymentEventUid;
use BaksDev\Payment\Type\Id\PaymentUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use BaksDev\Core\Entity\EntityEvent;
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
	private ?PaymentUid $main = null;
	
	/** Обложка способа оплаты */
	#[ORM\OneToOne(mappedBy: 'event', targetEntity: PaymentCover::class, cascade: ['all'])]
	private ?PaymentCover $cover = null;
	
	/** Модификатор */
	#[ORM\OneToOne(mappedBy: 'event', targetEntity: PaymentModify::class, cascade: ['all'])]
	private PaymentModify $modify;
	
	/** Перевод */
	#[ORM\OneToMany(mappedBy: 'event', targetEntity: PaymentTrans::class, cascade: ['all'])]
	private Collection $translate;
	
	/** Перевод */
	#[ORM\OneToMany(mappedBy: 'event', targetEntity: PaymentField::class, cascade: ['all'])]
	private Collection $field;
	
	/** Сортировка */
	#[ORM\Column(type: Types::SMALLINT, length: 3, options: ['default' => 500])]
	private int $sort = 500;
	
	/** Флаг активности */
	#[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
	private bool $active = true;
	
	public function __construct()
	{
		$this->id = new PaymentEventUid();
		$this->modify = new PaymentModify($this);
		
	}
	
	public function __clone()
	{
		$this->id = new PaymentEventUid();
	}
	
	public function __toString() : string
	{
		return (string) $this->id;
	}
	
	public function getId() : PaymentEventUid
	{
		return $this->id;
	}
	
	
	public function setMain(PaymentUid|Payment $main) : void
	{
		$this->main = $main instanceof Payment ? $main->getId() : $main;
	}
	
	
	public function getMain() : ?PaymentUid
	{
		return $this->main;
	}
	
	
	public function getDto($dto) : mixed
	{
		if($dto instanceof PaymentEventInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function setEntity($dto) : mixed
	{
		if($dto instanceof PaymentEventInterface)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
//	public function isModifyActionEquals(ModifyActionEnum $action) : bool
//	{
//		return $this->modify->equals($action);
//	}
	
	//	public function getUploadClass() : PaymentImage
	//	{
	//		return $this->image ?: $this->image = new PaymentImage($this);
	//	}
	
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