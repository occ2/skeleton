<?php
namespace occ2\control;

use Nette\Application\UI\Control as NControl;
use Contributte\EventDispatcher\EventDispatcher;
use Nette\Localization\ITranslator;
use occ2\flashes\TFlashMessage;

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
     * @var \Nette\Localization\ITranslator
     */
    protected $_translator;

    /**
     * @var \Contributte\EventDispatcher\EventDispatcher
     */
    protected $_eventDispatcher;

    /**
     * @var mixed
     */
    protected $_configurator;

    /**
     * constructor
     * @param EventDispatcher $eventDispatcher
     * @param User $user
     * @param ITranslator $translator
     * @return void
     */
    public function __construct(EventDispatcher $eventDispatcher, ITranslator $translator=null)
    {
        $this->_eventDispatcher = $eventDispatcher;
        $this->_translator = $translator;
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
    }

    /**
     * translate text if translator set
     * @param string $text
     * @return string
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
        return $this->text($text);
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
    public function getTranslator(): ITranslator
    {
        return $this->_translator;
    }

    public function on(string $eventName,$dataContainer)
    {

    }
}
