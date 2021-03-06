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
use Ublaboo\DataGrid\GroupAction\GroupAction;
use Ublaboo\DataGrid\GroupAction\GroupSelectAction;
use Ublaboo\DataGrid\GroupAction\GroupMultiSelectAction;
use Ublaboo\DataGrid\GroupAction\GroupTextAction;
use Ublaboo\DataGrid\GroupAction\GroupTextareaAction;
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;

/**
 * GroupActionsBuilder
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class GroupActionsBuilder implements IAdditionalGridBuilder
{
    use TCallbacks;
    
    const TYPES=[
        "simple"=>"addSimple",
        "select"=>"addSelect",
        "multiselect"=>"addMultiSelect",
        "text"=>"addText",
        "textarea"=>"addTextArea"
    ];

    /**
     * @var DataGrid
     */
    protected $grid;

    /**
     * @var GridConfig
     */
    protected $configurator;

    /**
     * @var GridControl
     */
    protected $object;

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
        $this->configurator = $configurator;
        $this->callbacks = $callbacks;
        return;
    }

    /**
     * build multiactions
     * @return void
     */
    public function build()
    {
        $this->addActions($this->grid);
        return;
    }

    /**
     * add actions
     * @param DataGrid $grid
     * @return void
     */
    protected function addActions(DataGrid $grid)
    {
        $configs = $this->configurator->get("groupAction",true);
        foreach ($configs as $config) {
            $this->addAction($grid, $config);
        }
        return;
    }

    /**
     * add one group action
     * @param DataGrid $grid
     * @param ArrayHash $config
     * @return GroupAction
     * @throws GridBuilderException
     */
    protected function addAction(DataGrid $grid, ArrayHash $config): GroupAction
    {
        if (!isset($config->name) || empty($config->name)) {
            throw new GridBuilderException("ERROR: Undefined group action name", GridBuilderException::UNDEFINED_GROUP_ACTION_NAME);
        }
        if (!isset($config->type) || !array_key_exists($config->type, static::TYPES)) {
            $config->type="simple";
        }
        $m = static::TYPES[$config->type];
        $action = $this->$m($grid, $config);
        $this->setupAction($action, $config);
        return $action;
    }

    /**
     * add simple group action
     * @param DataGrid $grid
     * @param ArrayHash $config
     * @return GroupAction
     */
    protected function addSimple(DataGrid $grid, ArrayHash $config): GroupAction
    {
        $t = $this;
        $action = $grid->addGroupAction(isset($config->label) ? $this->object->_($config->label) : $config->name);
        if ($this->checkCallback(GridBuilder::GROUP_ACTION_CALLBACK, $config->name)) {
            $action->onSelect[] = function ($ids) use ($t,$config) {
                return $t->invokeCallback(GridBuilder::GROUP_ACTION_CALLBACK, $config->name, $ids, $t->object);
            };
        } elseif (isset($config->event)) {
            $action->onSelect[] = function($ids) use ($t,$grid,$config) {
                $eventName = $config->event;
                $data = $t->object->getGridEventFactory()->create(
                    $grid,
                    $t->object,
                    null,
                    ["ids"=>$ids],
                    $eventName    
                );
                $t->object->on($eventName, $data);
                return;
            };
        }
        return $action;
    }

    /**
     * add group select action
     * @param DataGrid $grid
     * @param ArrayHash $config
     * @return GroupAction
     * @throws GridBuilderException
     */
    protected function addSelect(DataGrid $grid, ArrayHash $config): GroupAction
    {
        $t = $this;
        if (!$this->checkCallback(GridBuilder::GROUP_ACTION_OPTIONS_CALLBACK, $config->name)) {
            throw new GridBuilderException("ERROR: Group option callback not set", GridBuilderException::UNDEFINED_GROUP_ACTION_OPTION_CALLBACK);
        }
        
        $action = $grid->addGroupSelectAction(
                isset($config->label) ? $this->object->_($config->label) : $config->name,
                $this->invokeCallback(GridBuilder::GROUP_ACTION_OPTIONS_CALLBACK, $config->name, $this->object)
        );
        if ($this->checkCallback(GridBuilder::GROUP_ACTION_CALLBACK, $config->name)) {
            $action->onSelect[] = function ($ids, $option) use ($t,$config) {
                return $t->invokeCallback(GridBuilder::GROUP_ACTION_CALLBACK, $config->name, $ids, $t->object, $option);
            };
        } elseif (isset($config->event)) {
            $action->onSelect[] = function($ids,$option) use ($t,$grid,$config) {
                $eventName = $config->event;
                $data = $t->object->getGridEventFactory()->create(
                    $grid,
                    $t->object,
                    null,
                    ["ids"=>$ids,"option"=>$option],
                    $eventName
                );
                $t->object->on($eventName, $data);
                return;
            };
        }
        return $action;
    }

    /**
     * add group multiselect action
     * @param DataGrid $grid
     * @param ArrayHash $config
     * @return GroupAction
     * @throws GridBuilderException
     */
    protected function addMultiSelect(DataGrid $grid, ArrayHash $config): GroupAction
    {
        $t = $this;
        if (!$this->checkCallback(GridBuilder::GROUP_ACTION_OPTIONS_CALLBACK, $config->name)) {
            throw new GridBuilderException("ERROR: Group option callback not set", GridBuilderException::UNDEFINED_GROUP_ACTION_OPTION_CALLBACK);
        }
        
        $action = $grid->addGroupMultiSelectAction(
                isset($config->label) ? $this->object->_($config->label) : $config->name,
                $this->invokeCallback(GridBuilder::GROUP_ACTION_OPTIONS_CALLBACK, $config->name, $this->object)
        );
        if ($this->checkCallback(GridBuilder::GROUP_ACTION_CALLBACK, $config->name)) {
            $action->onSelect[] = function ($ids, $options) use ($t,$config) {
                return $t->invokeCallback(GridBuilder::GROUP_ACTION_CALLBACK, $config->name, $ids, $t->object, $options);
            };
        } elseif (isset($config->event)) {
            $action->onSelect[] = function($ids,$options) use ($t,$grid,$config) {
                $eventName = $config->event;
                $data = $t->object->getGridEventFactory()->create(
                    $grid,
                    $t->object,
                    null,
                    ["ids"=>$ids,"options"=>$options],
                    $eventName
                );
                $t->object->on($eventName, $data);
                return;
            };
        }
        return $action;
    }

    /**
     * add group text action
     * @param DataGrid $grid
     * @param ArrayHash $config
     * @return GroupAction
     */
    protected function addText(DataGrid $grid, ArrayHash $config): GroupAction
    {
        $t = $this;
        $action = $grid->addGroupTextAction(
             isset($config->label) ? $this->object->_($config->label) : $config->name
         );
        if ($this->checkCallback(GridBuilder::GROUP_ACTION_CALLBACK, $config->name)) {
            $action->onSelect[] = function ($ids, $value) use ($t,$config) {
                return $t->invokeCallback(GridBuilder::GROUP_ACTION_CALLBACK, $config->name, $ids, $t->object, $value);
            };
        } elseif (isset($config->event)) {
            $action->onSelect[] = function($ids,$value) use ($t,$grid,$config) {
                $eventName = $config->event;
                $data = $t->object->getGridEventFactory()->create(
                    $grid,
                    $t->object,
                    null,
                    ["ids"=>$ids,"value"=>$value],
                    $eventName
                );
                $t->object->on($eventName, $data);
                return;
            };
        }
        return $action;
    }

    /**
     * add group textarea action
     * @param DataGrid $grid
     * @param ArrayHash $config
     * @return GroupAction
     */
    protected function addTextArea(DataGrid $grid, ArrayHash $config): GroupAction
    {
        $t = $this;
        $action = $grid->addGroupTextareaAction(
             isset($config->label) ? $this->object->_($config->label) : $config->name
         );
        if ($this->checkCallback(GridBuilder::GROUP_ACTION_CALLBACK, $config->name)) {
            $action->onSelect[] = function ($ids, $value) use ($t,$config) {
                return $t->invokeCallback(GridBuilder::GROUP_ACTION_CALLBACK, $config->name, $ids, $t->object, $value);
            };
        } elseif (isset($config->event)) {
            $action->onSelect[] = function($ids,$value) use ($t,$grid,$config) {
                $eventName = $config->event;
                $data = $t->object->getGridEventFactory()->create(
                    $grid,
                    $t->object,
                    null,
                    ["ids"=>$ids,"value"=>$value],
                    $eventName
                );
                $t->object->on($eventName, $data);
                return;
            };
        }
        return $action;
    }

    /**
     * setup action
     * @param GroupAction $action
     * @param ArrayHash $config
     * @return GroupAction
     */
    protected function setupAction(GroupAction $action, ArrayHash $config)
    {
        !isset($config->class) ?: $action->setClass($config->class);
        if (isset($config->attribute)) {
            $a = explode(";", $config->attribute);
            foreach ($a as $line) {
                $d = explode("=>", $line);
                $action->setAttribute($d[0], $d[1]);
            }
        }
        return $action;
    }
}
