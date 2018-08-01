<?php
namespace occ2\inventar\User\controls\forms;

use occ2\FormControl\FormControl;

/**
 * ControlRecaptchaForm
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 *
 * @title user.controlRecaptchaForm.title
 * @comment user.controlRecaptchaForm.comment
 * @styles (headerBackground="light",headerText="dark",size="w-100")
 * @rError (container='div class="row"')
 * @rControl (container='div class="col-lg-12 col-md-12 col-sm-12"')
 * @rLabel (requiredsuffix="",container='div class="col-lg-0 col-md-0 col-sm-0"')
 * @ajax
 * @links (link="backToQuestion!",text="user.controlQuestionForm.reset",class="btn btn-outline-primary")
 */
final class ControlRecaptchaForm extends FormControl
{
    const RECAPTCHA="recaptcha";
    
    /**
     * @type recaptcha
     * @message user.controlRecaptchaForm.recaptcha
     * @var \Contributte\ReCaptcha\Forms\ReCaptchaField
     */
    public $recaptcha;

    /**
     * @label user.controlRecaptchaForm.submit
     * @type submit
     * @var SubmitButton
     */
    public $submit;
}
