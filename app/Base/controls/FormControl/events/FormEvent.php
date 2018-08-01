<?php
namespace occ2\FormControl;

use Contributte\EventDispatcher\Events\AbstractEvent;
use Nette\Forms\Container as Form;
use Nette\Application\UI\Presenter;

/**
 * form event container
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class FormEvent extends AbstractEvent
{

    /**
     * @var string
     */
    public $event;

    /**
     * @var \Nette\Forms\Container
     */
    public $form;

    /**
     * @var \Nette\Application\UI\Presenter
     */
    public $presenter;

    /**
     * @param mixed $data
     * @param string $event
     * @return type
     */
    public function __construct(Form $form, Presenter $presenter, $event=null)
    {
        $this->event = $event;
        $this->form = $form;
        $this->presenter = $presenter;
        return;
    }
}
