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

namespace app\Base\controls\Breadcrumbs;

use Nette\Application\UI\Control;
use Nette\Utils\ArrayHash;
use Nette\Application\UI\ITemplate;

/**
 * Breadcrumbs
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class Breadcrumbs extends Control
{
    const TEMPLATE=__DIR__ . "/breadcrumbs.latte";

    /**
     * @var array
     */
    protected $data=[];

    /**
     * render control
     * @return void
     */
    public function render()
    {
        $this->template->data = $this->data;
        if($this->template instanceof ITemplate){
            $this->template->setFile(self::TEMPLATE);
            $this->template->render();
        }
        return;
    }

    /**
     * set item active
     * @param string $key
     * @return $this
     */
    public function active(string $key)
    {
        $this->data[$key]->active=true;
        return $this;
    }

    /**
     * set item inactive
     * @param string $key
     * @return $this
     */
    public function inactive(string $key)
    {
        $this->data[$key]->active=false;
        return $this;
    }

    /**
     * add item
     * @param string $key
     * @param string $name
     * @param string $href
     * @param bool $active
     * @return $this
     */
    public function addItem(string $key, string $name,string $href="#",bool $active=false,bool $ajax=true)
    {
        $item = new ArrayHash;
        $item->name = $name;
        $item->href= $href;
        $item->active = $active;
        $item->ajax = $ajax;
        $this->data[$key] = $item;
        return $this;
    }

    /**
     * remove item
     * @param string $key
     * @return $this
     */
    public function removeItem(string $key)
    {
        unset($this->data[$key]);
        return $this;
    }
}