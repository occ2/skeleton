<?php
namespace occ2\inventar\User\controls\forms;

use occ2\FormControl\FormControl;

/**
 * ControlQuestionForm
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 *
 * @title user.controlQuestionForm.title
 * @styles (headerBackground="light",headerText="dark",size="w-100")
 * @rError (container='div class="row"')
 * @rControl (container='div class="col-lg-12 col-md-12 col-sm-12"')
 * @rLabel (requiredsuffix="",container='div class="col-lg-0 col-md-0 col-sm-0"')
 * @ajax
 * @links (link="clearReset!",text="user.controlQuestionForm.reset",class="btn btn-outline-primary")
 */
final class ControlQuestionForm extends FormControl
{
    const ANSWER="cAnswer";
    
    /**
     * @leftAddon user.controlQuestionForm.answer
     * @rightIcon user-secret
     * @type text
     * @cols 20
     * @validator (type=':filled',message='user.error.requiredAnswer')
     * @var TextInput
     */
    public $cAnswer;

    /**
     * @label user.controlQuestionForm.submit
     * @type submit
     * @var SubmitButton
     */
    public $submit;
}
