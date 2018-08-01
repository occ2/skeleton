<?php
namespace occ2\inventar\User\controls\forms;

/**
 * IChangePassForm factory interface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
interface IChangePassForm
{
    /**
     * @return \occ2\inventar\User\controls\forms\ChangePassForm
     */
    public function create();
}
