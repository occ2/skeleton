<?php
namespace app\Base\controls\Navbar;

use Nette\Application\UI\Control;

/**
 * Navbar control
 *
 * @author Milan Onderka
 * @property array $config
 * @version 1.1.0
 */
class Navbar extends Control
{     
    /**
     * @var array
     */
    public $config=[
        "expand"=>"md",
        "title"=>"base.application.name",
        "text"=>null,
        "imageWidth"=>30,
        "imageHeight"=>30,
        "imageSrc"=>"/images/logo.svg",
        "backgroundScheme"=>"dark",
        "colorScheme"=>"dark"
    ];
    
    /**
     * @var array
     */
    protected $data=[
        ""
    ];
    
    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }
    
    /**
     * render navigation
     * @return void
     */
    public function render()
    {
        $this->template->data = $this->data;
        $this->template->config = $this->config;
        $this->template->setFile(__DIR__ . '/navbar.latte');
        $this->template->render();
        return;
    }
}
