<?php
namespace app\Base\controls\GridControl\traits;

use app\Base\controls\GridControl\exceptions\GridCallbackException;
use Nette\Utils\Callback;

/**
 * TCallbacks
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
trait TCallbacks
{
    /**
     * @var array
     */
    protected $callbacks;
    
    /**
     * check of exists valid callback
     * @param string $callback
     * @param string $column
     * @return boolean
     */
    protected function checkCallback(string $callback, $column=null)
    {
        if ($column==null) {
            if (isset($this->callbacks->{$callback})) {
                return Callback::check($this->callbacks->{$callback});
            } else {
                return false;
            }
        } else {
            if (isset($this->callbacks->{$callback}[$column])) {
                return Callback::check($this->callbacks->{$callback}[$column]);
            } else {
                return false;
            }
        }
    }
    
    /**
     * invoke callback
     * @param callable $callback
     * @param mixed $params
     * @return mixed
     * @throws GridCallbackException
     */
    protected function invokeCallback(string $callback, $column=null, $param1=null, $param2=null, $param3=null, $param4=null, $param5=null)
    {
        if (!$this->checkCallback($callback, $column)) {
            throw new GridCallbackException("ERROR: Invalid callback", GridCallbackException::INVALID_CALLBACK);
        }
        if ($column==null) {
            $method =  $this->callbacks->$callback;
            return $method($param1, $param2, $param3, $param4, $param5);
        } else {
            $method = $this->callbacks->$callback[$column];
            return $method($param1, $param2, $param3, $param4, $param5);
        }
    }
}
