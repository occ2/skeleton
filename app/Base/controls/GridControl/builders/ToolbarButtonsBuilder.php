<?php
/*
 * The MIT License
 *
 * Copyright 2018 Milan Onderka <milan_onderka@occ2.cz>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace app\Base\controls\GridControl\builders;

use app\Base\controls\GridControl\traits\TCallbacks;
use app\Base\controls\GridControl\builders\IAdditionalGridBuilder;
use app\Base\controls\GridControl\GridControl;
use app\Base\controls\GridControl\configurators\GridConfig;
use app\Base\controls\GridControl\builders\GridBuilder;
use app\Base\controls\GridControl\exceptions\GridBuilderException;
use app\Base\controls\GridControl\DataGrid;
use Ublaboo\DataGrid\Toolbar\ToolbarButton;
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;

/**
 * ToolbarButtonsBuilder
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class ToolbarButtonsBuilder implements IAdditionalGridBuilder
{
    use TCallbacks;

    /**
     * @var DataGrid
     */
    protected $grid;

    /**
     * @var GridControl
     */
    protected $object;

    /**
     * @var GridConfig
     */
    protected $configurator;

    /**
     * @param GridControl $object
     * @param DataGrid $grid
     * @param GridConfig $configurator
     * @param array $callbacks
     * @return void
     */
    public function __construct(GridControl $object, DataGrid $grid, GridConfig $configurator, array $callbacks)
    {
        $this->object = $object;
        $this->grid = $grid;
        $this->callbacks = $callbacks;
        $this->configurator = $configurator;
        return;
    }

    /**
     * build toolbar btns
     * @return void
     */
    public function build()
    {
        $this->addToolbarButtons($this->grid);
        return;
    }

    /**
     * add toolbar buttons
     * @param DataGrid $grid
     * @return void
     */
    protected function addToolbarButtons(DataGrid $grid)
    {
        $buttons = $this->configurator->getToolbarButton(true);
        
        foreach ($buttons as $config) {
            $this->addButton($grid, $config);
        }
        return;
    }

    /**
     * add toolbar button
     * @param DataGrid $grid
     * @param ArrayHash $config
     * @return ToolbarButton | null
     * @throws GridBuilderException
     */
    protected function addButton(DataGrid $grid, ArrayHash $config)
    {
        if (!isset($config->name)) {
            throw new GridBuilderException("ERROR: Toolbar button name must be set", GridBuilderException::UNDEFINED_BUTTON_NAME);
        }
        if($this->checkCallback(GridBuilder::ALLOW_TOOLBAR_BUTTON_CALLBACK, $config->name)){
            if(!$this->invokeCallback(GridBuilder::ALLOW_TOOLBAR_BUTTON_CALLBACK, $config->name, $this->object)){
                return null;
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
                ["name"=>$config->name,"event"=>false,"params"=> serialize($params)]
            );
        } elseif (array_key_exists("toolbarButton" . Strings::firstUpper($config->name), $this->object->_symfonyEvents)) {
            $button = $grid->addToolbarButtonForBuilder(
                $config->name,
                $this->object->_toolbarHandler,
                isset($config->text) ? $this->object->text($config->text) : "",
                ["name"=>$config->name,"event"=>$this->object->_symfonyEvents["toolbarButton" . Strings::firstUpper($config->name)],"params"=> serialize($params)]
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
        return $button;
    }

    /**
     * setup toolbar button
     * @param ToolbarButton $button
     * @param ArrayHash $config
     * @return ToolbarButton
     */
    protected function setupButton(ToolbarButton $button, ArrayHash $config)
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
