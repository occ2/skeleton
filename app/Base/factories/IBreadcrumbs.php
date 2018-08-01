<?php
namespace app\Base\factories;

/**
 * INavbar factory
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
interface IBreadcrumbs
{
    /**
     * @return \app\Base\controls\Breadcrumbs\Breadcrumbs
     */
    public function create();
}
