<?php
namespace occ2\breadcrumbs;

use Nette\Application\UI\Control,
    Nette\Utils\ArrayHash;

/**
 * Breadcrumbs
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class Breadcrumbs extends Control
{
    const TEMPLATE=__DIR__ . "/breadcrumbs.latte";

    /**
     * @var array
     */
    protected $data=[];

    /**
     * render control
     * @return void
     */
    public function render()
    {
        $this->template->data = $this->data;
        return $this->template->render(self::TEMPLATE);
    }

    /**
     * set item active
     * @param string $key
     * @return $this
     */
    public function active(string $key)
    {
        $this->data[$key]->active=true;
        return $this;
    }

    /**
     * set item inactive
     * @param string $key
     * @return $this
     */
    public function inactive(string $key)
    {
        $this->data[$key]->active=false;
        return $this;
    }

    /**
     * add item
     * @param string $key
     * @param string $name
     * @param string $href
     * @param bool $active
     * @return $this
     */
    public function addItem(string $key, string $name,string $href="#",bool $active=false,bool $ajax=true)
    {
        $item = new ArrayHash;
        $item->name = $name;
        $item->href= $href;
        $item->active = $active;
        $item->ajax = $ajax;
        $this->data[$key] = $item;
        return $this;
    }

    /**
     * remove item
     * @param string $key
     * @return $this
     */
    public function removeItem(string $key)
    {
        unset($this->data[$key]);
        return $this;
    }
}