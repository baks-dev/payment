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

namespace BaksDev\Payment\Controller\Admin;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Payment\Entity\Event\PaymentEvent;
use BaksDev\Payment\Entity\Payment;
use BaksDev\Payment\UseCase\Admin\NewEdit\PaymentDTO;
use BaksDev\Payment\UseCase\Admin\NewEdit\PaymentForm;
use BaksDev\Payment\UseCase\Admin\NewEdit\PaymentHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_PAYMENT_EDIT')]
final class EditController extends AbstractController
{
    #[Route('/admin/payment/edit/{id}', name: 'admin.newedit.edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity] PaymentEvent $Event,
        PaymentHandler $paymentHandler,
    ): Response {
        $PaymentDTO = new PaymentDTO();
        $Event->getDto($PaymentDTO);

        // Форма
        $form = $this->createForm(PaymentForm::class, $PaymentDTO);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('payment'))
        {
            $this->refreshTokenForm($form);

            $Payment = $paymentHandler->handle($PaymentDTO);

            if($Payment instanceof Payment)
            {
                $this->addFlash('success', 'admin.success.update', 'admin.payment');

                return $this->redirectToRoute('payment:admin.index');
            }

            $this->addFlash('danger', 'admin.danger.update', 'admin.payment', $Payment);

            return $this->redirectToReferer();
        }

        return $this->render(['form' => $form->createView()]);
    }
}
