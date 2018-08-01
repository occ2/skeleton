<?php
namespace occ2\inventar\User\controls\forms;

/**
 * ISettingsForm factory interface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
interface ISettingsForm
{
    /**
     * @return \occ2\inventar\User\controls\forms\SettingsForm
     */
    public function create();
}
