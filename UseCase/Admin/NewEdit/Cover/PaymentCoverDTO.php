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

namespace BaksDev\Payment\UseCase\Admin\NewEdit\Cover;

use BaksDev\Payment\Entity\Cover\PaymentCoverInterface;
use BaksDev\Payment\Type\Cover\PaymentCoverUid;
use BaksDev\Payment\Type\Event\PaymentEventUid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;

final class PaymentCoverDTO implements PaymentCoverInterface
{
	
	/** Файл изображения */
	#[Assert\File(
		maxSize: '1024k',
		mimeTypes: [
			'image/png',
			'image/gif',
			'image/jpeg',
			'image/pjpeg',
			'image/webp',
		],
		mimeTypesMessage: 'Please upload a valid file'
	)]
	public ?File $file = null;
	
	private ?string $name = null;
	
	private ?string $ext = null;
	
	private bool $cdn = false;
	
	#[Assert\Uuid]
	private ?PaymentEventUid $dir = null;
	
	
	/** Сущность для загрузки и обновления файла  */
	
	private mixed $entityUpload;
	
	
	public function getName() : ?string
	{
		return $this->name;
	}
	
	
	/* EXT */
	public function getExt() : ?string
	{
		return $this->ext;
	}
	
	
	/* CDN */
	
	public function getCdn() : bool
	{
		return $this->cdn;
	}
	
	
	/* DIR */
	
	public function getDir() : ?PaymentEventUid
	{
		return $this->dir;
	}
	
	
	/** Сущность для загрузки и обновления файла  */
	
	public function getEntityUpload() : mixed
	{
		return $this->entityUpload;
	}
	
	
	public function setEntityUpload(mixed $entityUpload) : void
	{
		$this->entityUpload = $entityUpload;
	}
	
}