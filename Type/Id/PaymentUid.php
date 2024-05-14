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

namespace BaksDev\Payment\Type\Id;

use App\Kernel;
use BaksDev\Core\Type\UidType\Uid;
use BaksDev\Payment\Type\Id\Choice\Collection\TypePaymentInterface;
use Symfony\Component\Uid\AbstractUid;

final class PaymentUid extends Uid
{
    public const TEST = '0188a99b-c707-7851-9329-8b7f5e1be6d2';

    public const TYPE = 'payment';

    /**
     * @var mixed|null
     */
    private mixed $option;

    /**
     * @var mixed|null
     */
    private mixed $attr;


    public function __construct(
        AbstractUid|TypePaymentInterface|self|string|null $value = null,
        mixed $option = null,
        mixed $attr = null,
    )
    {
        if(is_string($value) && class_exists($value))
        {
            $value = new $value();
        }

        if($value instanceof TypePaymentInterface)
        {
            $value = $value->getValue();
        }

        parent::__construct($value);

        $this->option = $option;
        $this->attr = $attr;
    }

    public function getOption(): mixed
    {
        return $this->option;
    }

    public function getAttr(): mixed
    {
        return $this->attr;
    }


    public function getTypeDeliveryValue(): string
    {
        return (string) $this->getValue();
    }

    public function getTypeDelivery(): PaymentUid|TypePaymentInterface
    {
        foreach(self::getDeclared() as $declared)
        {
            /** @var TypePaymentInterface $declared */
            if($declared::equals($this->getValue()))
            {
                return new $declared;
            }
        }

        return new self($this->getValue());
    }


    public static function getDeclared(): array
    {
        return array_filter(
            get_declared_classes(),
            static function($className) {
                return in_array(TypePaymentInterface::class, class_implements($className), true);
            }
        );
    }
}