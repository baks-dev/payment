<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Payment\Commands\Upgrade;


use BaksDev\Core\Type\Field\InputField;
use BaksDev\Delivery\Entity\Delivery;
use BaksDev\Delivery\Repository\ExistTypeDelivery\ExistTypeDeliveryInterface;
use BaksDev\Delivery\Type\Id\DeliveryUid;
use BaksDev\Delivery\UseCase\Admin\NewEdit\DeliveryDTO;
use BaksDev\Delivery\UseCase\Admin\NewEdit\DeliveryHandler;
use BaksDev\Delivery\UseCase\Admin\NewEdit\Fields\DeliveryFieldDTO;
use BaksDev\Delivery\UseCase\Admin\NewEdit\Fields\Trans\DeliveryFieldTransDTO;
use BaksDev\Delivery\UseCase\Admin\NewEdit\Trans\DeliveryTransDTO;
use BaksDev\Payment\Entity\Payment;
use BaksDev\Payment\Repository\ExistTypePayment\ExistTypePaymentInterface;
use BaksDev\Payment\Type\Id\Choice\TypePaymentCache;
use BaksDev\Payment\Type\Id\PaymentUid;
use BaksDev\Payment\UseCase\Admin\NewEdit\PaymentDTO;
use BaksDev\Payment\UseCase\Admin\NewEdit\PaymentHandler;
use BaksDev\Payment\UseCase\Admin\NewEdit\Trans\PaymentTransDTO;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Users\Profile\TypeProfile\Entity\TypeProfile;
use BaksDev\Users\Profile\TypeProfile\Repository\ExistTypeProfile\ExistTypeProfileInterface;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use BaksDev\Users\Profile\TypeProfile\UseCase\Admin\NewEdit\Section\Fields\SectionFieldDTO;
use BaksDev\Users\Profile\TypeProfile\UseCase\Admin\NewEdit\Section\Fields\Trans\SectionFieldTransDTO;
use BaksDev\Users\Profile\TypeProfile\UseCase\Admin\NewEdit\Section\SectionDTO;
use BaksDev\Users\Profile\TypeProfile\UseCase\Admin\NewEdit\Section\Trans\SectionTransDTO;
use BaksDev\Users\Profile\TypeProfile\UseCase\Admin\NewEdit\Trans\TransDTO;
use BaksDev\Users\Profile\TypeProfile\UseCase\Admin\NewEdit\TypeProfileDTO;
use BaksDev\Users\Profile\TypeProfile\UseCase\Admin\NewEdit\TypeProfileHandler;
use BaksDev\Yandex\Market\Orders\Type\DeliveryType\TypeDeliveryYandexMarket;
use BaksDev\Yandex\Market\Orders\Type\ProfileType\TypeProfileYandexMarket;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'baks:payment:cache',
    description: 'Добавляет способ оплаты при получении'
)]
#[AutoconfigureTag('baks.project.upgrade')]
class UpgradePaymentTypeCacheCommand extends Command
{

    private TranslatorInterface $translator;
    private PaymentHandler $paymentHandler;
    private ExistTypePaymentInterface $existTypePayment;

    public function __construct(
        ExistTypePaymentInterface $existTypePayment,
        PaymentHandler $paymentHandler,
        TranslatorInterface $translator,
    )
    {
        parent::__construct();

        $this->translator = $translator;
        $this->existTypePayment = $existTypePayment;
        $this->paymentHandler = $paymentHandler;
    }

    /** Добавляет доставку Yandex Market  */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $PaymentUid = new PaymentUid(TypePaymentCache::class);

        /** Проверяем наличие доставки Yandex Market */
        $exists = $this->existTypePayment->isExists($PaymentUid);

        if(!$exists)
        {
            $io = new SymfonyStyle($input, $output);
            $io->text('Добавляем способ оплаты при получении');

            $PaymentDTO = new PaymentDTO($PaymentUid);
            $PaymentDTO->setSort(TypePaymentCache::priority());

            $PaymentTransDTO = $PaymentDTO->getTranslate();

            /**
             * Присваиваем настройки локали типа профиля
             *
             * @var PaymentTransDTO $PaymentTrans
             */
            foreach($PaymentTransDTO as $PaymentTrans)
            {
                $name = $this->translator->trans('cache.name', domain: 'payment.type', locale: $PaymentTrans->getLocal()->getLocalValue());
                $desc = $this->translator->trans('cache.desc', domain: 'payment.type', locale: $PaymentTrans->getLocal()->getLocalValue());

                $PaymentTrans->setName($name);
                $PaymentTrans->setDescription($desc);
            }

            $handle = $this->paymentHandler->handle($PaymentDTO);

            if(!$handle instanceof Payment)
            {
                $io->error(
                    sprintf('Ошибка %s при добавлении способа доставки', $handle)
                );

                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }

    /** Чам выше число - тем первым в итерации будет значение */
    public static function priority(): int
    {
        return 99;
    }

}
