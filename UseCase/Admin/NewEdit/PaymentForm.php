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

use BaksDev\Users\Profile\TypeProfile\Repository\TypeProfileChoice\TypeProfileChoiceInterface;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PaymentForm extends AbstractType
{
	private TypeProfileChoiceInterface $profileChoice;
	
	
	public function __construct(TypeProfileChoiceInterface $profileChoice) {
		$this->profileChoice = $profileChoice;
	}
	
	
	public function buildForm(FormBuilderInterface $builder, array $options) : void
	{
		
		$profileChoice = $this->profileChoice->getActiveTypeProfileChoice();
		
		$builder
			->add('type', ChoiceType::class, [
				'choices' => $profileChoice,
				'choice_value' => function(?TypeProfileUid $type) {
					return $type?->getValue();
				},
				'choice_label' => function(TypeProfileUid $type) {
					return $type->getOption();
				},
				'label' => false,
				'expanded' => false,
				'multiple' => false,
				'required' => false
			])
		;
		
		/** Обложка способа оплаты */
		$builder->add('cover', Cover\PaymentCoverForm::class);
		
		/** Настройки локали службы доставки */
		$builder->add('translate', CollectionType::class, [
			'entry_type' => Trans\PaymentTransForm::class,
			'entry_options' => ['label' => false],
			'label' => false,
			'by_reference' => false,
			'allow_delete' => true,
			'allow_add' => true,
			'prototype_name' => '__payment_translate__',
		]);
		
		
		/** Настройки локали службы доставки */
		$builder->add('field', CollectionType::class, [
			'entry_type' => Fields\PaymentFieldForm::class,
			'entry_options' => ['label' => false],
			'label' => false,
			'by_reference' => false,
			'allow_delete' => true,
			'allow_add' => true,
			'prototype_name' => '__payment_field__',
		]);
		
		
		/** Сортировка поля в секции */
		$builder->add
		(
			'sort',
			IntegerType::class,
			[
				'label' => false,
				'attr' => ['min' => 0, 'max' => 999],
			]
		);
		
		
		/** Флаг активности */
		
		$builder->add
		(
			'active',
			CheckboxType::class,
			[
				'label' => false,
				'required' => false,
			]
		);
		
		
		/* Сохранить ******************************************************/
		$builder->add(
			'payment',
			SubmitType::class,
			['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']]
		);
	}
	
	
	public function configureOptions(OptionsResolver $resolver) : void
	{
		$resolver->setDefaults([
			'data_class' => PaymentDTO::class,
		]);
	}
	
}