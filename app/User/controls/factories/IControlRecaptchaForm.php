<?php
namespace occ2\inventar\User\controls\forms;

/**
 * IControlRecaptchaForm factory interface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
interface IControlRecaptchaForm
{
    /**
     * @return \occ2\inventar\User\controls\forms\ControlRecaptchaForm
     */
    public function create();
}
