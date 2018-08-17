<?php
namespace app\Base\models\entities;
/**
 * IEntity inteface
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
interface IEntity
{
    /**
     * universal getter
     * @param string $name name of property
     * @param bool $ignoreUndefined ignore non-existent property
     * @return mixed | null
     */
    public function get(string $name, bool $ignoreUndefined=false);

    /**
     * universal setter
     * @param string $name name of property
     * @param mixed $value value of property
     * @param bool $ignoreUndefined ignore non-existent property?
     * @return $this
     */
    public function set(string $name,$value, bool $ignoreUndefined=false);
}