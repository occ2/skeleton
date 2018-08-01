<?php
namespace occ2\inventar\User\controls\forms;

/**
 * IControlQuestionForm factory interface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
interface IControlQuestionForm
{
    /**
     * @return \occ2\inventar\User\controls\forms\ControlQuestionForm
     */
    public function create();
}
