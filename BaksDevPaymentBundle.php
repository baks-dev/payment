<?php
/*
 * This file is part of the FreshCentrifugoBundle.
 *
 * (c) Artem Henvald <genvaldartem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace BaksDev\Payment;

use BaksDev\Orders\Order\Repository\FieldByPaymentChoice\FieldByPaymentChoiceInterface;
use BaksDev\Orders\Order\Repository\PaymentByTypeProfileChoice\PaymentByTypeProfileChoiceInterface;
use BaksDev\Payment\Repository\FieldByPaymentChoice\FieldByPaymentChoiceRepository;
use BaksDev\Payment\Repository\PaymentByTypeProfileChoice\PaymentByTypeProfileChoiceRepository;
use DirectoryIterator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class BaksDevPaymentBundle extends AbstractBundle
{
    public const NAMESPACE = __NAMESPACE__.'\\';

    public const PATH = __DIR__.DIRECTORY_SEPARATOR;

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $services = $container->services()
            ->defaults()
            ->autowire()
            ->autoconfigure();

        $services->load(self::NAMESPACE, self::PATH)
            ->exclude([
                self::PATH.'{Entity,Resources,Type}',
                self::PATH.'**/*Message.php',
                self::PATH.'**/*DTO.php',
            ]);

        $services->load(
            self::NAMESPACE.'Type\Id\Choice\\',
            self::PATH.'Type/Id/Choice'
        );

        $services->alias(
            PaymentByTypeProfileChoiceInterface::class.' $paymentChoice',
            PaymentByTypeProfileChoiceRepository::class);

        $services->alias(
            FieldByPaymentChoiceInterface::class.' $paymentFields',
            FieldByPaymentChoiceRepository::class);
    }
}
