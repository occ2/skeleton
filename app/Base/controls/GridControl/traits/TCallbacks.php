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

namespace app\Base\controls\GridControl\traits;

use app\Base\controls\GridControl\exceptions\GridCallbackException;
use Nette\Utils\Callback;

/**
 * TCallbacks
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
trait TCallbacks
{
    /**
     * @var array
     */
    protected $callbacks;
    
    /**
     * check of exists valid callback
     * @param string $callback
     * @param string $column
     * @return boolean
     */
    protected function checkCallback(string $callback, $column=null)
    {
        if ($column==null) {
            if (isset($this->callbacks[$callback])) {
                Callback::check($this->callbacks[$callback]);
                return true;
            } else {
                return false;
            }
        } else {
            if (isset($this->callbacks[$callback][$column])) {
                Callback::check($this->callbacks[$callback][$column]);
                return true;
            } else {
                return false;
            }
        }
    }
    
    /**
     * invoke callback
     * @param string $callback
     * @return mixed
     * @throws GridCallbackException
     */
    protected function invokeCallback(string $callback, $column=null, $param1=null, $param2=null, $param3=null, $param4=null, $param5=null)
    {
        if (!$this->checkCallback($callback, $column)) {
            throw new GridCallbackException("ERROR: Invalid callback", GridCallbackException::INVALID_CALLBACK);
        }
        if ($column==null) {
            $method =  $this->callbacks[$callback];
            return $method($param1, $param2, $param3, $param4, $param5);
        } else {
            $method = $this->callbacks[$callback][$column];
            return $method($param1, $param2, $param3, $param4, $param5);
        }
    }
}
