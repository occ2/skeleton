<?php
namespace app\Base\traits;

use Nette\Localization\ITranslator;

/**
 * TTranslator
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
trait TTranslator
{
    /**
     * @var ITranslator | null
     */
    protected $translator=null;

    /**
     * translator setter
     * @param ITranslator $translator
     * @return $this
     */
    public function setTranslator(ITranslator $translator)
    {
        $this->translator = $translator;
        return $this;
    }

    /**
     * translate text
     * @param string $message
     * @param int $count
     * @param array $parameters
     * @return string
     */
    public function _(string $message, $count=null, $parameters=[]) : string
    {
        if($this->translator instanceof ITranslator){
            return $this->translator->translate($message, $count, $parameters);
        } else {
            return $message;
        }
    }
}