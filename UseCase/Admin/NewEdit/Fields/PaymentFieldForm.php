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

use BaksDev\Core\Services\Fields\FieldsChoice;
use BaksDev\Core\Services\Fields\FieldsChoiceInterface;
use BaksDev\Core\Type\Field\InputField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class PaymentFieldForm extends AbstractType
{
	
	private TranslatorInterface $translator;
	
	private FieldsChoice $fields;
	
	public function __construct(FieldsChoice $fields, TranslatorInterface $translator)
	{
		$this->translator = $translator;
		$this->fields = $fields;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options) : void
	{
		
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
		
		
		
		/** Тип поля (input, select, textarea ....) */
		$builder->add
		(
			'type',
			ChoiceType::class,
			[
				'required' => false,
				'choices' => $this->fields->getFields(),
				'choice_value' => function($choice) {
					return $choice instanceof FieldsChoiceInterface ? $choice?->type() : $choice;
				},
				'choice_label' => function($choice) {
					return $this->translator->trans('label', domain: $choice->domain());
				},
			]
		);
		
		$builder->get('type')->addModelTransformer(
			new CallbackTransformer(
				function($type) {
					return $type; // instanceof FieldsChoiceInterface ? $type->type() : $type;
				},
				function($type) {
					return $type instanceof FieldsChoiceInterface ? new InputField($type) : null;
				}
			)
		);
		
		/** Обязательное к заполнению */
		$builder->add('required', CheckboxType::class, [
			'required' => false,
		]);
		
		
		/** Настройки локали полей для заполенния  */
		$builder->add('translate', CollectionType::class, [
			'entry_type' => Trans\PaymentFieldTransForm::class,
			'entry_options' => ['label' => false],
			'label' => false,
			'by_reference' => false,
			'allow_delete' => true,
			'allow_add' => true,
			'prototype_name' => '__field_translate__',
		]);
		
		
		$builder->add
		(
			'DeleteField',
			ButtonType::class,
			[
				'label_html' => true,
				'attr' =>
					['class' => 'btn btn-sm btn-icon btn-light-danger del-item-field'],
			]
		);
		
	}
	
	
	public function configureOptions(OptionsResolver $resolver) : void
	{
		$resolver->setDefaults([
			'data_class' => PaymentFieldDTO::class,
		]);
	}
	
}