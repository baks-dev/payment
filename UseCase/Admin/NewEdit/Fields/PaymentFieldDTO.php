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

namespace BaksDev\Payment\UseCase\Admin\NewEdit\Fields;

use BaksDev\Core\Type\Field\InputField;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Payment\Entity\Fields\PaymentFieldInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

final class PaymentFieldDTO implements PaymentFieldInterface
{
	
	/** Перевод (настройки локали) полей способа оплаты */
	#[Assert\Valid]
	private ArrayCollection $translate;
	
	
	/** Тип поля (input, select, textarea ....)  */
	#[Assert\NotBlank]
	private InputField $type;
	
	/** Обязательное к заполнению */
	private bool $required = true;
	
	/** Сортировка */
	#[Assert\NotBlank]
	private int $sort = 100;
	

	public function __construct()
	{
		$this->translate = new ArrayCollection();
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
			$PaymentFieldTransDTO = new Trans\PaymentFieldTransDTO;
			$PaymentFieldTransDTO->setLocal($locale);
			$this->addTranslate($PaymentFieldTransDTO);
		}
		
		return $this->translate;
	}
	
	
	public function addTranslate(Trans\PaymentFieldTransDTO $trans) : void
	{
		if(!$this->translate->contains($trans))
		{
			$this->translate->add($trans);
		}
	}
	
	
	public function removeTranslate(Trans\PaymentFieldTransDTO $trans) : void
	{
		$this->translate->removeElement($trans);
	}
	
	
	
	/** Тип поля (input, select, textarea ....)  */
	
	
	public function getType() : InputField
	{
		return $this->type;
	}

	public function setType(InputField $type) : void
	{
		$this->type = $type;
	}
	
	
	/** Обязательное к заполнению */
	
	
	public function getRequired() : bool
	{
		return $this->required;
	}
	

	public function setRequired(bool $required) : void
	{
		$this->required = $required;
	}
	
	
	/** Сортирвока */
	
	
	public function getSort() : int
	{
		return $this->sort;
	}
	
	
	public function setSort(int $sort) : void
	{
		$this->sort = $sort;
	}
	
	
	
	
	
}