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

namespace app\Base\controls\GridControl\events;

use app\Base\controls\GridControl\DataGrid;
use Contributte\EventDispatcher\Events\AbstractEvent as BaseEvent;
use Nette\Forms\Container as Form;
use Nette\Application\UI\Control;

/**
 * grid event container
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class GridEventData extends BaseEvent
{

    /**
     * @var string | null
     */
    protected $event;
    
    /**
     * @var DataGrid
     */
    protected $datagrid;

    /**
     * @var Form | null
     */
    protected $form;

    /**
     * @var Control
     */
    protected $control;

    /**
     * @var mixed | null
     */
    protected $data;

    /**
     * @param mixed $data
     * @param string $event
     * @return void
     */
    public function __construct(DataGrid $datagrid, Control $control, Form $form=null, $data=null, $event=null)
    {
        $this->datagrid = $datagrid;
        $this->event = $event;
        $this->form = $form;
        $this->control = $control;
        $this->data = $data;
        return;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function getDatagrid(): DataGrid
    {
        return $this->datagrid;
    }

    public function getForm(): ?Form
    {
        return $this->form;
    }

    public function getControl(): Control
    {
        return $this->control;
    }

    public function getData()
    {
        return $this->data;
    }
}
