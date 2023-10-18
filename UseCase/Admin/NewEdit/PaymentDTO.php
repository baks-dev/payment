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

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Payment\Entity\Event\PaymentEventInterface;
use BaksDev\Payment\Type\Event\PaymentEventUid;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

final class PaymentDTO implements PaymentEventInterface
{
	
	/** Идентификатор события */
	#[Assert\Uuid]
	private ?PaymentEventUid $id = null;
	
	/** Профиль пользователя, которому доступна оплата (null - все) */
	private ?TypeProfileUid $type = null;
	
	/** Обложка способа оплаты */
	#[Assert\Valid]
	private Cover\PaymentCoverDTO $cover;
	
	/** Перевод (настройки локали) способа оплаты */
	#[Assert\Valid]
	private ArrayCollection $translate;
	
	/** Поля для заполнения */
	#[Assert\Valid]
	private ArrayCollection $field;
	
	/** Сортировка */
	#[Assert\NotBlank]
	private int $sort = 500;
	
	/** Флаг активности */
	private bool $active = true;
	
	
	public function __construct()
	{
		$this->translate = new ArrayCollection();
		$this->field = new ArrayCollection();
		$this->cover = new Cover\PaymentCoverDTO();
	}
	
	
	public function getEvent() : ?PaymentEventUid
	{
		return $this->id;
	}
	
	
	/** Перевод (настройки локали) способа оплаты */
	
	public function setTranslate(ArrayCollection $trans) : void
	{
		$this->translate = $trans;
	}
	
	
	public function getTranslate() : ArrayCollection
	{
		/* Вычисляем расхождение и добавляем неопределенные локали */
		foreach(Locale::diffLocale($this->translate) as $locale)
		{
			$PaymentTransDTO = new Trans\PaymentTransDTO;
			$PaymentTransDTO->setLocal($locale);
			$this->addTranslate($PaymentTransDTO);
		}
		
		return $this->translate;
	}
	
	
	public function addTranslate(Trans\PaymentTransDTO $trans) : void
	{
        if(empty($trans->getLocal()->getLocalValue()))
        {
            return;
        }

		if(!$this->translate->contains($trans))
		{
			$this->translate->add($trans);
		}
	}
	
	
	public function removeTranslate(Trans\PaymentTransDTO $trans) : void
	{
		$this->translate->removeElement($trans);
	}
	
	
	/** Поля для заполнения */
	
	public function getField() : ArrayCollection
	{
		return $this->field;
	}
	
	
	public function setField(ArrayCollection $field) : void
	{
		$this->field = $field;
	}
	
	
	public function addField(Fields\PaymentFieldDTO $field) : void
	{
		if(!$this->field->contains($field))
		{
			$this->field->add($field);
		}
	}
	
	
	public function removeField(Fields\PaymentFieldDTO $field) : void
	{
		$this->field->removeElement($field);
	}
	
	
	/** Обложка способа оплаты */
	
	public function getCover() : Cover\PaymentCoverDTO
	{
		return $this->cover;
	}
	
	
	public function setCover(Cover\PaymentCoverDTO $cover) : void
	{
		$this->cover = $cover;
	}
	
	
	/** Сортировка */
	
	public function getSort() : int
	{
		return $this->sort;
	}
	
	
	public function setSort(int $sort) : void
	{
		$this->sort = $sort;
	}
	
	
	/** Флаг активности */
	
	public function getActive() : bool
	{
		return $this->active;
	}

	public function setActive(bool $active) : void
	{
		$this->active = $active;
	}
	
	
	/** Профиль пользователя, которому доступна оплата */

	public function getType() : ?TypeProfileUid
	{
		return $this->type;
	}
	
	public function setType(?TypeProfileUid $type) : void
	{
		$this->type = $type;
	}
	
}