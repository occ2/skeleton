<?php
namespace app\Base\controls\Control;

use app\Base\traits\TFlashMessage;
use Contributte\EventDispatcher\Events\AbstractEvent as BaseEvent;
use Contributte\EventDispatcher\EventDispatcher;
use Contributte\Cache\ICacheFactory;
use Nette\Application\UI\Control as NControl;
use Kdyby\Translation\ITranslator;

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
     * @var ITranslator | null
     */
    protected $_translator;

    /**
     * @var EventDispatcher
     */
    protected $_eventDispatcher;

    /**
     * @var ICacheFactory
     */
    public $_cacheFactory;

    /**
     * @var mixed
     */
    protected $_configurator;

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
        $this->_eventDispatcher = $eventDispatcher;
        $this->_translator = $translator;
        $this->_cacheFactory = $cacheFactory;
        parent::__construct();
        $this->startup();
        return;
    }

    public function startup()
    {
    }

    /**
     * render
     */
    public function render()
    {
        if (property_exists($this, "_modal")) {
            $this->template->modal = $this->_modal;
        }
        return;
    }

    /**
     * translate text if translator set
     * @param string $text
     * @return string
     * @deprecated since version 1.1.0
     */
    public function text(string $text):string
    {
        return $this->_translator instanceof ITranslator ? $this->_translator->translate($text) : $text;
    }

    /**
     * shorter alias for text()
     * @param string $text
     * @return string
     */
    public function _(string $text) : string
    {
        return $this->_translator instanceof ITranslator ? $this->_translator->translate($text) : $text;
    }

    /**
     * event dispatcher getter
     * @return EventDispatcher
     */
    public function getEventDispatcher(): EventDispatcher
    {
        return $this->_eventDispatcher;
    }

    /**
     * translator getter
     * @return ITranslator
     */
    public function getTranslator(): ?ITranslator
    {
        return $this->_translator;
    }

    /**
     * configurator getter
     * @return mixed
     */
    public function getConfigurator()
    {
        return $this->_configurator;
    }

    /**
     * fire event
     * @param string $eventName
     * @param \app\Base\controls\Control\ControlEventData $data
     * @return mixed
     */
    public function on(string $eventName, BaseEvent $data)
    {
        return $this->_eventDispatcher->dispatch($eventName, $data);
    }
}
