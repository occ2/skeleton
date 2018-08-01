<?php
namespace occ2\GridControl;

use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Toolbar\ToolbarButton;
use Nette\Utils\ArrayHash;

/**
 * ToolbarButtonsBuilder
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class ToolbarButtonsBuilder implements IAdditionalGridBuilder
{
    use TCallbacks;
    
    protected $grid;
    protected $object;
    protected $configurator;
    
    public function __construct($object, DataGrid $grid, GridConfig $configurator, ArrayHash $callbacks)
    {
        $this->object = $object;
        $this->grid = $grid;
        $this->callbacks = $callbacks;
        $this->configurator = $configurator;
        return;
    }
    
    public function build()
    {
        return $this->addToolbarButtons($this->grid);
    }
    
    protected function addToolbarButtons(DataGrid $grid)
    {
        $buttons = $this->configurator->getToolbarButton(true);
        
        foreach ($buttons as $config) {
            $this->addButton($grid, $config);
        }
        return;
    }
    
    protected function addButton(DataGrid $grid, ArrayHash $config)
    {
        if (!isset($config->name)) {
            throw new GridBuilderException("ERROR: Toolbar button name must be set", GridBuilderException::UNDEFINED_BUTTON_NAME);
        }
        if($this->checkCallback(GridBuilder::ALLOW_TOOLBAR_BUTTON_CALLBACK, $config->name)){
            if(!$this->invokeCallback(GridBuilder::ALLOW_TOOLBAR_BUTTON_CALLBACK, $config->name, $this->object)){
                return;
            }
        }
        if (isset($config->params)) {
            $params = [];
            $c = explode(";", $config->params);
            foreach ($c as $param) {
                $arg = explode("=>", $param);
                $params[$arg[0]] = $arg[1];
            }
        } else {
            $params = [];
        }
        
        if ($this->checkCallback(GridBuilder::TOOLBAR_BUTTON_CALLBACK, $config->name)) {
            $button = $grid->addToolbarButtonForBuilder(
                $config->name,
                $this->object->_toolbarHandler,
                isset($config->text) ? $this->object->text($config->text) : "",
                ["name"=>$config->name,"params"=> serialize($params)]
            );
        } else {
            $button = $grid->addToolbarButtonForBuilder(
                $config->name,
                isset($config->href) ? $config->href : $config->name,
                isset($config->text) ? $this->object->text($config->text) : "",
                $params
            );
        }
        $this->setupButton($button, $config);
        return;
    }
    
    protected function setupButton(ToolbarButton $button, $config)
    {
        !isset($config->icon) ?: $button->setIcon($config->icon);
        !isset($config->class) ?: $button->setClass($config->class);
        !isset($config->title) ?: $button->setTitle($this->object->text($config->title));
        if (isset($config->attributes)) {
            $attrs = [];
            $c = explode(";", $config->attributes);
            foreach ($c as $attr) {
                $arg = explode("=>", $attr);
                $attrs[$arg[0]] = $arg[1];
            }
            $button->addAttributes($attrs);
        }
        return $button;
    }
}
