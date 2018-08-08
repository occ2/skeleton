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
     * @return void
     */
    public function setTranslator(ITranslator $translator)
    {
        $this->translator = $translator;
        return;
    }

    /**
     * translatoe text
     * @param string $text
     * @param mixed $count
     * @return string
     */
    public function _(string $text,$count=null) : string
    {
        if($this->translator instanceof ITranslator){
            return $this->translator->translate($text);
        } else {
            return $text;
        }
    }
}