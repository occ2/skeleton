<?php
namespace occ2\GridControl;

use Ublaboo\DataGrid\DataGrid;
use Nette\Utils\ArrayHash;
use Ublaboo\DataGrid\Column\MultiAction;
use Nette\Utils\Callback;

/**
 * ActionsBuilder
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class ActionsBuilder implements IAdditionalGridBuilder
{
    use TCallbacks;
    
    protected $object;
    
    protected $grid;
    
    protected $configurator;
    
    protected $multiactionRegistry=[];
    
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
        $this->addMultiActions($this->grid);
        return;
    }
    
    public function addActions(DataGrid $grid)
    {
        $actions = $this->configurator->getAction(true);
        foreach ($actions as $config) {
            if (!isset($config->multiaction)) {
                $this->addAction($grid, $config);
            } else {
                $this->multiactionRegistry[$config->multiaction][] = $config;
            }
        }
        return;
    }
    
    protected function addAction(DataGrid $grid, ArrayHash $config)
    {
        $t = $this;
        if (!isset($config->name) || empty($config->name)) {
            throw new GridBuilderException("ERROR: Action name must be set.", GridBuilderException::ACTION_NAME_NOT_SET);
        }
        
        if ($this->checkCallback(GridBuilder::ACTION_CALLBACK, $config->name)) {
            $action = $grid->addActionCallback(
                    $config->name,
                    !isset($config->label) ? "" : $config->label
            );
            $action->onClick[] = function ($id) use ($t,$grid,$config) {
                $this->invokeCallback(
                    GridBuilder::ACTION_CALLBACK,
                    $config->name,
                    $id,
                    $grid,
                    $t->object
                );
            };
        } else {
            $action = $grid->addAction(
                    $config->name,
                    !isset($config->label) ? "" : $config->label,
                    !isset($config->href) ? $config->name : $config->href,
                    !isset($config->params) ? null : $config->params
            );
        }
        $this->setupAction($action, $config);
        
        $this->setupAllowCallback($grid, $config);
        
        return $action;
    }
    
    protected function setupAction($action, ArrayHash $config)
    {
        $t = $this;
        if ($this->checkCallback(GridBuilder::ACTION_ICON_CALLBACK, $config->name)) {
            $action->setIcon(function ($item) use ($t,$config) {
                return $this->invokeCallback(GridBuilder::ACTION_ICON_CALLBACK, $config->name, $item, $t->object);
            });
        } else {
            !isset($config->icon) ?: $action->setIcon($config->icon);
        }
              
        (isset($config->ajax) && $config->ajax==true) ? $ajax="" : $ajax="ajax ";
        
        if ($this->checkCallback(GridBuilder::ACTION_CLASS_CALLBACK, $config->name)) {
            $action->setIcon(function ($item) use ($t,$config) {
                return $this->invokeCallback(GridBuilder::ACTION_CLASS_CALLBACK, $config->name, $item, $t->object);
            });
        } else {
            !isset($config->class) ? $ajax=="" ?: $action->setClass($ajax) : $action->setClass($ajax . "btn btn-xs " . $config->class);
        }
        
        if ($this->checkCallback(GridBuilder::ACTION_TITLE_CALLBACK, $config->name)) {
            $action->setTitle(function ($item) use ($t,$config) {
                return $this->invokeCallback(GridBuilder::ACTION_TITLE_CALLBACK, $config->name, $item, $t->object);
            });
        } else {
            !isset($config->title) ?: $action->setTitle($config->title);
        }
        
        !isset($config->newTab) ?: $action->setOpenInNewTab($config->newTab);
        if (isset($config->dataAttribute)) {
            $a = explode("=>", $this->dataAttribute);
            $action->setDataAttribute($a[0], $a[1]);
        }
        if ($this->checkCallback(GridBuilder::ACTION_CONFIRM_CALLBACK, $config->name)) {
            $action->setConfirm(function ($item) use ($config,$t) {
                return $this->invokeCallback(GridBuilder::ACTION_CONFIRM_CALLBACK, $config->name, $item, $t->object);
            });
        }
        !isset($config->confirm) ?: $action->setConfirm($config->confirm, isset($config->confirmCol) ? $config->confirmCol : null);
    }
    
    protected function setupAllowCallback(DataGrid $grid, ArrayHash $config, $multiaction=null)
    {
        $t = $this;
        if ($multiaction==null) {
            if ($this->checkCallback(GridBuilder::ALLOW_ROWS_ACTION_CALLBACK, $config->name)) {
                $grid->allowRowsAction($config->name, function ($item) use ($t,$config) {
                    return $t->invokeCallback(GridBuilder::ALLOW_ROWS_ACTION_CALLBACK, $config->name, $item, $t->object);
                });
            }
        } else {
            if ($this->checkCallback(GridBuilder::ALLOW_ROWS_MULTIACTION_CALLBACK, $multiaction . "-" . $config->action)) {
                $grid->allowRowsMultiAction($multiaction, $config->name, function ($item) use ($t,$config,$multiaction) {
                    return $t->invokeCallback(GridBuilder::ALLOW_ROWS_MULTIACTION_CALLBACK, $multiaction . "-" . $config->action, $item, $t->object);
                });
            }
        }
    }
    
    protected function addMultiActions(DataGrid $grid)
    {
        $actions = $this->configurator->getMultiAction(true);
        if ($actions==null) {
            return;
        }
        foreach ($actions as $config) {
            $this->addMultiAction($grid, $config);
        }
        return;
    }
    
    protected function addMultiAction(DataGrid $grid, ArrayHash $config)
    {
        $multiaction = $grid->addMultiAction(
            $config->name,
            !isset($config->label) ? $config->name : $config->label
        );
        $this->setupMultiAction($grid, $multiaction, $config);
        return $multiaction;
    }
    
    protected function setupMultiAction(DataGrid $grid, MultiAction $multiaction, ArrayHash $config)
    {
        !isset($config->icon) ?: $multiaction->setIcon($config->icon);
        !isset($config->class) ?: $multiaction->setClass("btn btn-xs " . $config->class);
        !isset($config->title) ?: $multiaction->setTitle($config->title);
        !isset($config->text) ?: $multiaction->setTitle($config->text);
        !isset($config->caret) ?: $multiaction->setCaret($config->caret);
        if (isset($this->multiactionRegistry[$config->name]) && is_array($this->multiactionRegistry[$config->name])) {
            foreach ($this->multiactionRegistry[$config->name] as $configItem) {
                $this->setupMultiActionItems($multiaction, $configItem);
                $this->setupAllowCallback($grid, $configItem, $config->name);
            }
        }
    }
    
    protected function setupMultiActionItems(MultiAction $multiaction, ArrayHash $config)
    {
        $multiaction->addAction(
                    $config->name,
                    !isset($config->label) ? $config->name : $config->label,
                    !isset($config->href) ? $config->name : $config->href,
                    !isset($config->params) ? null : $config->params
            );
        $action = $multiaction->getAction($config->name);
        $this->setupAction($action, $config);
        return $action;
    }
}
