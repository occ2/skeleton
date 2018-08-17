<?php
namespace app\Base\presenters;

use app\Base\traits\TFlashMessage;
use Nette\Application\UI\Presenter;
use Nette\Application\BadRequestException;
use Tracy\Debugger;

/**
 * Error presenter
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class ErrorPresenter extends Presenter
{
    use TFlashMessage;
    /**
     * @inject
     * @var \Kdyby\Translation\Translator
     */
    public $translator;

    /**
     * @inject
     * @var \app\Base\factories\INavbar
     */
    public $navbarFactory;

    /**
     * @inject
     * @var \app\Base\factories\IBreadcrumbs
     */
    public $breadcrumbsFactory;

    /**
     * render exception
     * @param \Exception $exception
     * @return void
     */
    public function renderDefault($exception)
    {
        $code = in_array($exception->getCode(), array(403, 404, 405, 410, 500)) ? $exception->getCode() : 'other';
        $this->template->code = $code;
        $this->template->message = $this->translator->translate($exception->getMessage());
        if($exception instanceof BadRequestException){
            //$this->setView($code);
            Debugger::log("HTTP code $code: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}", 'access');
            Debugger::fireLog("HTTP code $code: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}");
            bdump($exception);
        }else {
            $this->setView('500'); // load template 500.latte
            Debugger::log($exception, Debugger::ERROR); // and log exception
            Debugger::fireLog(dump($exception));
            bdump($exception);
        }
        return;
    }

    /**
     * @return \app\Base\controls\Navbar\Navbar
     */
    public function createComponentNavbar()
    {
        return $this->navbarFactory->create();
    }

    /**
     * @return \app\Base\controls\Breadcrumbs\Breadcrumbs
     */
    public function createComponentBreadcrumbs()
    {
        $breadcrumbs = $this->breadcrumbsFactory->create();
        $breadcrumbs->addItem("home","base.breadcrumbs.home", $this->link(":User:Main:default"),true);
        $breadcrumbs->addItem("error","base.breadcrumbs.error", $this->link(":User:Main:default"),false);
        return $breadcrumbs;
    }
}