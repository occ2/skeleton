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

namespace app\Base\controls\Control;

use app\Base\traits\TFlashMessage;
use app\Base\controls\Control\IConfigurator;
use Contributte\EventDispatcher\Events\AbstractEvent as BaseEvent;
use Contributte\EventDispatcher\EventDispatcher;
use Contributte\Cache\ICacheFactory;
use Nette\Application\UI\Control as NControl;
use Kdyby\Translation\ITranslator;
use Nette\Utils\ArrayHash;

/**
 * parent of all controls
 *
 * @author Milan Onderka
 * @version 1.0.0
 */
abstract class Control extends NControl
{
    use TFlashMessage;

    /**
     * property container
     * @var ArrayHash
     */
    protected $c;

    /**
     * @param EventDispatcher $eventDispatcher
     * @param ICacheFactory $cacheFactory
     * @param ITranslator $translator
     * @return void
     */
    public function __construct(
            EventDispatcher $eventDispatcher,
            ICacheFactory $cacheFactory,
            ITranslator $translator=null)
    {
        $this->c = new ArrayHash();
        $this->c->ed = $eventDispatcher;
        $this->c->translator = $translator;
        $this->c->cacheFactory = $cacheFactory;
        $this->c->configurator = [];
        parent::__construct();
        $this->startup();
    }

    public function startup()
    {
    }

    /**
     * shorter alias for text()
     * @param string $text
     * @return string
     */
    public function _(string $text) : string
    {
        return $this->c->translator instanceof ITranslator ? $this->c->translator->translate($text) : $text;
    }

    /**
     * event dispatcher getter
     * @return EventDispatcher
     */
    public function getEventDispatcher(): EventDispatcher
    {
        return $this->c->ed;
    }

    /**
     * translator getter
     * @return ITranslator
     */
    public function getTranslator(): ?ITranslator
    {
        return $this->c->translator;
    }

    /**
     * configurator getter
     * @return IConfigurator
     */
    public function getConfigurator()
    {
        return $this->c->configurator;
    }

    /**
     * @return ICacheFactory | null
     */
    public function getCacheFactory()
    {
        return isset($this->c->cacheFactory) ? $this->c->cacheFactory : null;
    }

    /**
     * fire event
     * @param string $eventName
     * @param \app\Base\controls\Control\ControlEventData $data
     * @return mixed
     */
    public function on(string $eventName, BaseEvent $data)
    {
        return $this->c->ed->dispatch($eventName, $data);
    }
}
