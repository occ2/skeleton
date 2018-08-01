<?php
namespace occ2\GridControl;

use Ublaboo\DataGrid\DataGrid;
use Nette\Utils\ArrayHash;
use Ublaboo\DataGrid\GroupAction\GroupAction;

/**
 * GroupActionsBuilder
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
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
    
    protected $grid;
    
    protected $configurator;
    
    protected $object;
    
    protected $callbacks;
    
    public function __construct($object, DataGrid $grid, GridConfig $configurator, ArrayHash $callbacks)
    {
        $this->object = $object;
        $this->grid = $grid;
        $this->configurator = $configurator;
        $this->callbacks = $callbacks;
        return;
    }
    
    public function build()
    {
        $this->addActions($this->grid);
        return;
    }
    
    protected function addActions(DataGrid $grid)
    {
        $configs = $this->configurator->getGroupAction(true);
        foreach ($configs as $config) {
            $this->addAction($grid, $config);
        }
        return;
    }
    
    protected function addAction(DataGrid $grid, ArrayHash $config)
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
    
    protected function addSimple(DataGrid $grid, ArrayHash $config)
    {
        $t = $this;
        $action = $grid->addGroupAction(isset($config->label) ? $this->object->text($config->label) : $config->name);
        if ($this->checkCallback(GridBuilder::GROUP_ACTION_CALLBACK, $config->name)) {
            $action->onSelect[] = function ($ids) use ($t,$config) {
                return $t->invokeCallback(GridBuilder::GROUP_ACTION_CALLBACK, $config->name, $ids, $t->object);
            };
        }
        return $action;
    }
    
    protected function addSelect(DataGrid $grid, ArrayHash $config)
    {
        $t = $this;
        if (!$this->checkCallback(GridBuilder::GROUP_ACTION_OPTIONS_CALLBACK, $config->name)) {
            throw new GridBuilderException("ERROR: Group option callback not set", GridBuilderException::UNDEFINED_GROUP_ACTION_OPTION_CALLBACK);
        }
        
        $action = $grid->addGroupSelectAction(
                isset($config->label) ? $this->object->text($config->label) : $config->name,
                $this->invokeCallback(GridBuilder::GROUP_ACTION_OPTIONS_CALLBACK, $config->name, $this->object)
        );
        if ($this->checkCallback(GridBuilder::GROUP_ACTION_CALLBACK, $config->name)) {
            $action->onSelect[] = function ($ids, $option) use ($t,$config) {
                return $t->invokeCallback(GridBuilder::GROUP_ACTION_CALLBACK, $config->name, $ids, $t->object, $option);
            };
        }
        return $action;
    }
    
    protected function addMultiSelect(DataGrid $grid, ArrayHash $config)
    {
        $t = $this;
        if (!$this->checkCallback(GridBuilder::GROUP_ACTION_OPTIONS_CALLBACK, $config->name)) {
            throw new GridBuilderException("ERROR: Group option callback not set", GridBuilderException::UNDEFINED_GROUP_ACTION_OPTION_CALLBACK);
        }
        
        $action = $grid->addGroupMultiSelectAction(
                isset($config->label) ? $this->object->text($config->label) : $config->name,
                $this->invokeCallback(GridBuilder::GROUP_ACTION_OPTIONS_CALLBACK, $config->name, $this->object)
        );
        if ($this->checkCallback(GridBuilder::GROUP_ACTION_CALLBACK, $config->name)) {
            $action->onSelect[] = function ($ids, $options) use ($t,$config) {
                return $t->invokeCallback(GridBuilder::GROUP_ACTION_CALLBACK, $config->name, $ids, $t->object, $options);
            };
        }
        return $action;
    }
    
    protected function addText(DataGrid $grid, ArrayHash $config)
    {
        $t = $this;
        $action = $grid->addGroupTextAction(
             isset($config->label) ? $this->object->text($config->label) : $config->name
         );
        if ($this->checkCallback(GridBuilder::GROUP_ACTION_CALLBACK, $config->name)) {
            $action->onSelect[] = function ($ids, $value) use ($t,$config) {
                return $t->invokeCallback(GridBuilder::GROUP_ACTION_CALLBACK, $config->name, $ids, $t->object, $value);
            };
        }
        return $action;
    }
    
    protected function addTextArea(DataGrid $grid, ArrayHash $config)
    {
        $t = $this;
        $action = $grid->addGroupTextareaAction(
             isset($config->label) ? $this->object->text($config->label) : $config->name
         );
        if ($this->checkCallback(GridBuilder::GROUP_ACTION_CALLBACK, $config->name)) {
            $action->onSelect[] = function ($ids, $value) use ($t,$config) {
                return $t->invokeCallback(GridBuilder::GROUP_ACTION_CALLBACK, $config->name, $ids, $t->object, $value);
            };
        }
        return $action;
    }
    
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
