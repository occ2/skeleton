<?php
namespace app\Base\controls\FormControl\events;

use Contributte\EventDispatcher\Events\AbstractEvent as BaseEvent;
use Nette\Forms\Container as Form;
use app\Base\controls\FormControl\FormControl;
use Nette\Utils\ArrayHash;
use Nette\Application\UI\Presenter;

/**
 * form event data container
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class FormEvent extends BaseEvent
{

    /**
     * @var string
     */
    private $event;

    /**
     * @var \Nette\Forms\Container
     */
    private $form;

    /**
     * @var FormControl
     */
    private $control;

    /**
     * @param Form $form
     * @param FormControl $control
     * @param string $event
     * @return void
     */
    public function __construct(Form $form, FormControl $control, string $event=null)
    {
        $this->event = $event;
        $this->form = $form;
        $this->control = $control;
        return;
    }

    /**
     * get values from form
     * @param bool $asArray
     * @return ArrayHash | array
     */
    public function getValues(bool $asArray=false)
    {
        return $this->form->getValues($asArray);
    }

    /**
     * get form
     * @param bool $fromControl if true return form from control if false return form from event
     * @return Form
     */
    public function getForm(bool $fromControl=true)
    {
        if($fromControl){
            return $this->control["form"];
        } else {
            return $this->form;
        }
    }

    /**
     * get whole control
     * @return FormControl
     */
    public function getControl()
    {
        return $this->control;
    }

    /**
     * get presenter
     * @return Presenter
     */
    public function getPresenter()
    {
        return $this->control->getPresenter();
    }

    /**
     * get event name
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }
}
