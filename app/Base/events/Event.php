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

namespace app\Base\events;

use Contributte\EventDispatcher\Events\AbstractEvent;
use Nette\Utils\ArrayHash;

/**
 * event container for all events
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
abstract class Event extends AbstractEvent
{
    const ENTITY="entity",
          REPOSITORY="repository",
          COLLECTION="collection",
          QUERY_BUILDER="queryBuilder";
    
    /**
     * @var array | ArrayHash
     */
    private $data;

    /**
     * @var string | null
     */
    private $event;

    /**
     * @param array | ArrayHash $data
     * @param string | null $event
     * @return void
     */
    public function __construct($data, string $event=null)
    {
        $this->data = $data;
        $this->event = $event;
        return;
    }

    /**
     * data getter
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    /**
     * get event name
     * @return string | null
     */
    public function getEvent()
    {
        return $this->event;
    }
}
