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

namespace BaksDev\Payment\Type\Field;

use BaksDev\Core\Type\UidType\Uid;
use Symfony\Component\Uid\AbstractUid;

final class PaymentFieldUid extends Uid
{
    public const TEST = '0188a99b-91bb-7c7a-8f5b-644682d65bc7';
    
	public const TYPE = 'payment_field';
	
	/**
	 * @var mixed|null
	 */
	private mixed $attr;
	
	/**
	 * @var mixed|null
	 */
	private mixed $option;
	
	/**
	 * @var mixed|null
	 */
	private mixed $type;
	
	private ?bool $required;
	
	
	public function __construct(
		AbstractUid|string|null $value = null,
		mixed $attr = null,
		mixed $option = null,
		mixed $type = null,
		?bool $required = true,
	)
	{
		parent::__construct($value);
		
		$this->attr = $attr;
		$this->option = $option;
		$this->type = $type;
		$this->required = $required;
	}
	
	

	public function getAttr() : mixed
	{
		return $this->attr;
	}
	

	public function getOption() : mixed
	{
		return $this->option;
	}
	

	public function getType() : mixed
	{
		return $this->type;
	}
	

	public function getRequired() : ?bool
	{
		return $this->required;
	}
	
	
}