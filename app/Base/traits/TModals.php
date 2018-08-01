<?php
namespace occ2\modals;

/**
 * TModals
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
trait TModals
{

    /**
     * show modal
     * @return void
     */
    public function showModal()
    {
        if ($this->isAjax()) {
            $this->payload->isModal = true;
            $this->redrawControl("modal");

        }
        return;
    }

    /**
     * hide modal
     * @return void
     */
    public function hideModal()
    {
        if ($this->isAjax()) {
            $this->payload->isModal = false;
            $this->sendPayload();
        }
        return;
    }
}