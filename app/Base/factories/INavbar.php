<?php
namespace app\Base\factories;

/**
 * INavbar factory
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
interface INavbar
{
    /**
     * @return \app\Base\controls\Navbar\Navbar
     */
    public function create();
}
