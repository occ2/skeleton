<?php
namespace occ2\inventar\presenters;

use Nette\Application\UI\Presenter;
use Nette\Application\BadRequestException;
use Tracy\Debugger;
use occ2\flashes\TFlashMessage;

class ErrorPresenter extends Presenter
{
    use TFlashMessage;
    /**
     * @inject
     * @var \Kdyby\Translation\Translator
     */
    public $translator;

    /**
     * @inject
     * @var \occ2\inventar\controls\INavbar
     */
    public $navbarFactory;

    /**
     * @inject
     * @var \occ2\breadcrumbs\IBreadcrumbs
     */
    public $breadcrumbsFactory;
    
    public function renderDefault( $exception)
    {
        $code = in_array($exception->getCode(), array(403, 404, 405, 410, 500)) ? $exception->getCode() : 'other';
        $this->template->code = $code;
        $this->template->message = $this->translator->translate($exception->getMessage());
        if($exception instanceof BadRequestException){
            
            $this->setView($code);
            Debugger::log("HTTP code $code: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}", 'access');
        } else {
            $this->setView('500'); // load template 500.latte
            Debugger::log($exception, Debugger::ERROR); // and log exception

        }

        if ($this->isAjax()) { // AJAX request? Note this error in payload.
            bdump($exception);          
        }
        return;
    }

    /**
     * @return \occ2\inventar\controls\Navbar
     */
    public function createComponentNavbar()
    {
        return $this->navbarFactory->create();
    }

    /**
     * @return \occ2\breadcrumbs\Breadcrumbs
     */
    public function createComponentBreadcrumbs()
    {
        $breadcrumbs = $this->breadcrumbsFactory->create();
        $breadcrumbs->addItem("home","base.breadcrumbs.home", $this->link(":User:Main:default"),true);
        $breadcrumbs->addItem("error","base.breadcrumbs.error", $this->link(":User:Main:default"),false);
        return $breadcrumbs;
    }
}