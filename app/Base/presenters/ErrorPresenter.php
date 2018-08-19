<?php

/* 
 * The MIT License
 *
 * Copyright 2018 Milan Onderka <milan_onderka@occ2.cz>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace app\Base\presenters;

use Nette;
use Nette\Application\Responses;
use Nette\Http;
use Tracy\ILogger;

class ErrorPresenter implements Nette\Application\IPresenter
{
	use Nette\SmartObject;

        const TEMPLATE_PATH= __DIR__ . '/../templates/Error/500.phtml';
	/** @var ILogger */
	private $logger;


	public function __construct(ILogger $logger)
	{
		$this->logger = $logger;
	}


	/**
	 * @return Nette\Application\IResponse
	 */
	public function run(Nette\Application\Request $request): Nette\Application\IResponse
	{
		$e = $request->getParameter('exception');
                //\Tracy\Debugger::fireLog($e);
                bdump($e);
		if ($e instanceof Nette\Application\BadRequestException) {
                    	// $this->logger->log("HTTP code {$e->getCode()}: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", 'access');
			[$module, , $sep] = Nette\Application\Helpers::splitName($request->getPresenterName());
			$errorPresenter = $module . $sep . 'Error4xx';
			return new Responses\ForwardResponse($request->setPresenterName($errorPresenter));
		}

		$this->logger->log($e, ILogger::EXCEPTION);
		return new Responses\CallbackResponse(function (Http\IRequest $httpRequest, Http\IResponse $httpResponse) {
			if (preg_match('#^text/html(?:;|$)#', $httpResponse->getHeader('Content-Type'))) {
				require static::TEMPLATE_PATH;
			}
		});
	}
}
