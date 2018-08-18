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

namespace app\Base\traits;

use Kdyby\Translation\ITranslator;

/**
 * TTranslator
 * translation helper
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
trait TTranslator
{
    /**
     * @var ITranslator | null
     */
    protected $translator=null;

    /**
     * translator setter
     * @param ITranslator $translator
     * @return $this
     */
    public function setTranslator(ITranslator $translator)
    {
        $this->translator = $translator;
        return $this;
    }

    /**
     * translate text
     * @param string $message
     * @param int $count
     * @param array $parameters
     * @return string
     */
    public function _(string $message, $count=null, $parameters=[]) : string
    {
        if($this->translator instanceof ITranslator){
            return $this->translator->translate($message, $count, $parameters);
        } else {
            return $message;
        }
    }
}