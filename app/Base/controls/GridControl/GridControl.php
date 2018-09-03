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

namespace app\Base\controls\GridControl;

use app\Base\traits\TFlashMessage;
use app\Base\controls\Control\Control;
use app\Base\controls\GridControl\builders\IGridBuilder;
use app\Base\controls\GridControl\configurators\GridConfig;
use app\Base\controls\GridControl\DataGrid;
use app\Base\controls\GridControl\exceptions\GridCallbackException;
use app\Base\controls\GridControl\exceptions\GridBuilderException;
use app\Base\controls\GridControl\builders\GridBuilder;
use app\Base\controls\GridControl\interfaces\IGridFactory;
use app\Base\controls\GridControl\interfaces\IGridEventFactory;
use app\Base\controls\GridControl\factories\GridEventFactory;
use app\Base\controls\GridControl\factories\GridRowEventFactory;
use app\Base\controls\GridControl\traits\TEntity;
use Ublaboo\DataGrid\Column\Column;
use Ublaboo\DataGrid\Column\Action;
use Ublaboo\DataGrid\Filter\Filter;
use Ublaboo\DataGrid\Components\DataGridPaginator\DataGridPaginator;
use Contributte\EventDispatcher\EventDispatcher;
use Contributte\EventDispatcher\Events\AbstractEvent as BaseEvent;
use Contributte\Cache\ICacheFactory;
use Kdyby\Translation\ITranslator;
use Nette\Utils\Strings;
use Nette\Application\UI\ITemplate;
use Nette\Utils\Callback;
use Nette\Utils\ArrayHash;

/**
 * GridControl parent of autogenerated datagrids
 *
 * depends on
 * ublaboo/datagrid
 * nette/reflection
 * contribute/event-dispatcher
 * contribute/utils
 * contributte/cache
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
abstract class GridControl extends Control
{
    use TFlashMessage;
    use TEntity;
    
    const CALLBACK_CATALOG=[
        "loadOptions",
        "action",
        "itemDetail",
        "itemDetailCondition",
        "toolbarButton",
        "columnRenderer",
        "columnConfition",
        "sortColumn",
        "statusChange",
        "summary",
        "summaryRenderer",
        "column",
        "filterCondition",
        "actionIcon",
        "actionClass",
        "actionTitle",
        "actionConfirm",
        "sorting",
        "groupActionOptions",
        "groupAction",
        "allowRowsGroupAction",
        "allowRowsInlineEdit",
        "allowRowsAction",
        "allowRowsMultiAction",
        "row",
        "export",
        "editable",
        "editableValue",
        "inlineForm",
        "inlineLoadOptions",
        "inlineFormFill",
        "inlineAddSubmit",
        "inlineEditSubmit",
        "inlineCustomRedraw",
        "allowToolbarButton"
    ];
    //const EVENT_PREFIX=static::class;
    const GRID_CONTROL="grid";
    const DEFAULT_ICON_PREFIX="fas fa-";
    const DEFAULT_TOOLBAR_HANDLER="toolbar!";
    const DEFAULT_TEMPLATE_PATH=__DIR__ . "/templates/gridControl.latte";
    const DEFAULT_GRID_TEMPLATE_PATH=__DIR__ . "/templates/datagrid.latte";
    const DEFAULT_GRID_PAGINATOR_TEMPLATE_PATH=__DIR__ . "/templates/data_grid_paginator.latte";
    const DEFAULT_STATUS_TEMPLATE_PATH=__DIR__ . "/templates/column_status.latte";
    const DEFAULT_DETAIL_TEMPLATE_PATH=__DIR__ . "/templates/detail.latte";
    const DEFAULT_FILTERS_TEMPLATES_PATH=[
        "text"=>__DIR__ . "/templates/datagrid_filter_text.latte",
        "select"=>__DIR__ . "/templates/datagrid_filter_select.latte",
        "multiselect"=>__DIR__ . "/templates/column_status.latte",
        "date"=>__DIR__ . "/templates/datagrid_filter_date.latte",
        "range"=>__DIR__ . "/templates/datagrid_filter_range.latte",
        "daterange"=>__DIR__ . "/templates/datagrid_filter_daterange.latte"
    ];
    const DEFAULT_GRID_STATUS_SETTINGS=[
        "class"=>"btn-info",
        "classSecondary"=>"btn-sm",
        "classInDropdown"=>"dropdown-item",
    ];
    const DEFAULT_NUMBER_FORMAT=[
        "decimals"=>0,
        "decPoint"=>".",
        "thousandsSeparator"=>" "
    ];
    const DEFAULT_DATETIME_FORMAT=[
      "php"=>'j.n.Y H:i:s',
      "js"=>'d. m. yyyy'
    ];
    const DEFAULT_SORTABLE_HANDLER="sort!";

    /**
     * @param IGridFactory $gridFactory
     * @param EventDispatcher $eventDispatcher
     * @param ICacheFactory $cacheFactory
     * @param ITranslator $translator
     * @param string $gridEventFactoryClass
     * @param string $gridRowEventFactoryClass
     * @return void
     */
    public function __construct(
        IGridFactory $gridFactory,
        EventDispatcher $eventDispatcher,
        ICacheFactory $cacheFactory,
        ITranslator $translator = null,
        string $gridEventFactoryClass=GridEventFactory::class,
        string $gridRowEventFactoryClass=GridRowEventFactory::class
    )
    {
        parent::__construct($eventDispatcher, $cacheFactory, $translator);

        $this->c->configurator = new GridConfig($this);
        $this->setLinks($this->getConfigurator()->get("links",true));
        $this->setIconPrefix(static::DEFAULT_ICON_PREFIX);
        $this->setToolbarHandler(static::DEFAULT_TOOLBAR_HANDLER);
        $this->setTemplatePath(static::DEFAULT_TEMPLATE_PATH);
        $this->setGridTemplatePath(static::DEFAULT_GRID_TEMPLATE_PATH);
        $this->setGridPaginatorTemplatePath(static::DEFAULT_GRID_PAGINATOR_TEMPLATE_PATH);
        $this->setStatusTemplatePath(static::DEFAULT_STATUS_TEMPLATE_PATH);
        $this->setDetailTemplatePath(static::DEFAULT_DETAIL_TEMPLATE_PATH);
        $this->setSortableHandler(static::DEFAULT_SORTABLE_HANDLER);
        $this->c->filtersTemplatesPath = static::DEFAULT_FILTERS_TEMPLATES_PATH;
        $this->c->gridStatusSettings = static::DEFAULT_GRID_STATUS_SETTINGS;
        $this->c->numberFormat = static::DEFAULT_NUMBER_FORMAT;
        $this->c->datetimeFormat = static::DEFAULT_DATETIME_FORMAT;
        if(!isset($this->c->callbacks)){
            $this->c->callbacks = new ArrayHash();
        }
        $this->c->gridFactory = $gridFactory;
        $this->c->gridClass = $gridFactory->getClass();
        $this->c->gridEventFactory = new $gridEventFactoryClass;
        $this->c->gridRowEventFactory = new $gridRowEventFactoryClass;
        return;
    }
    
    /**
     * before render - to override
     * @param DataGrid $grid
     */
    public function beforeRender(DataGrid $grid)
    {}

    /**
     * after render to override
     * @param DataGrid $grid
     */
    public function afterRender(DataGrid $grid)
    {
    }

    /**
     * before build to override
     * @param DataGrid $grid
     */
    public function beforeBuild(DataGrid $grid)
    {
    }

    /**
     * after build to override
     * @param DataGrid $grid
     */
    public function afterBuild(DataGrid $grid)
    {
    }

    /**
     * sort handler to override
     * @param DataGrid $grid
     * @param mixed $itemId
     * @param mixed $prevId
     * @param mixed $nextId
     * @param mixed $parentId
     */
    public function sort(DataGrid $grid, $itemId, $prevId, $nextId, $parentId)
    {
    }
    
    /**
     * universal callback setter
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call($name, $args)
    {
        if (Strings::startsWith($name, "set")) {
            if (Strings::endsWith($name, "Callback")) {
                $anchor = Strings::firstLower(str_replace("Callback", "", str_replace("set", "", $name)));
                if (!in_array($anchor, static::CALLBACK_CATALOG)) {
                    throw new GridCallbackException("ERROR: Unknown Callback.", GridCallbackException::UNKNOWN_CALLBACK);
                }

                if (count($args)==1)
                {
                    $this->c->callbacks->$anchor = $args[0];
                } else {
                    $this->c->callbacks->$anchor[$args[0]] = $args[1];
                }
                return;
            }
        }
        return parent::__call($name, $args);
    }
    
    /**
     * enable access to grid direct thu $control->grid
     * @param string $name
     * @return $this
     */
    public function &__get($name)
    {
        if ($name==self::GRID_CONTROL) {
            return $this[$name];
        }
        return parent::__get($name);
    }

    /**
     * enable direct access to grid columns
     * @param string $name
     * @return Column
     */
    public function getColumn($name)
    {
        return $this[self::GRID_CONTROL]->getColumn($name);
    }

    /**
     * enable direct access to grid actions
     * @param string $name
     * @return Action
     */
    public function getAction($name)
    {
        return $this[self::GRID_CONTROL]->getAction($name);
    }
    
    /**
     * enable direct access to grid filters
     * @param string $name
     * @return Filter
     */
    public function getFilter($name)
    {
        return $this[self::GRID_CONTROL]->getFilter($name);
    }
    
    /**
     * datasource setter
     * @param mixed $datasource
     * @return $this
     */
    public function setDatasource($datasource)
    {
        $this->c->datasource = $datasource;
        return $this;
    }

    public function getDatasource()
    {
        return isset($this->c->datasource) ? $this->c->datasource : null;
    }
    
    /**
     * custom template setter
     * @param string $template
     * @return $this
     */
    public function setTemplatePath(string $template)
    {
        $this->c->templatePath = $template;
        return $this;
    }

    public function getTemplatePath()
    {
        return $this->c->templatePath;
    }    

    /**
     * overide form title (usable by ajax)
     * @param string $text
     * @return $this
     */
    public function setTitle(string $text)
    {
        $this->c->title = $this->_($text);
        return $this;
    }

    /**
     * overide form comment (usable by ajax)
     * @param string $text
     * @return $this
     */
    public function setComment(string $text)
    {
        $this->c->comment = $this->_($text);
        return $this;
    }

    /**
     * overide form footer (usable by ajax)
     * @param string $text
     * @return $this
     */
    public function setFooter(string $text)
    {
        $this->c->footer = $this->_($text);
        return $this;
    }

    /**
     * get control title (if set)
     * @return string
     */
    public function getTitle()
    {
        if (isset($this->c->title) && !empty($this->c->title)) {
            return $this->c->title;
        } else {
            return $this->getConfigurator()->get("title");
        }
    }

    /**
     * get control comment (if set)
     * @return string
     */
    public function getComment()
    {
        if (isset($this->c->comment) && !empty($this->c->comment)){
            return $this->c->comment;
        } else {
            return $this->getConfigurator()->get("comment");
        }
    }

    /**
     * get control footer (if set)
     * @return string
     */
    public function getFooter()
    {
        if (isset($this->c->footer) && !empty($this->c->footer)) {
            return $this->c->footer;
        } else {
            return $this->getConfigurator()->get("footer");
        }
    }

    /**
     * set grid without Bootstrap Card encapsulation
     * @param bool $simple
     * @return $this
     */
    public function setSimple(bool $simple=true)
    {
        $this->c->simple = $simple;
        return $this;
    }
    
    /**
     * disable annotation builder
     * @param bool $disable
     * @return $this
     */
    public function disableBuilder(bool $disable=true)
    {
        $this->c->disableBuilder = $disable;
        return $this;
    }

    public function isBuilderEnabled()
    {
        if(isset($this->c->disableBuilder) && $this->c->disableBuilder==true){
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * set grid builder
     * @param IGridBuilder $builder
     * @return $this
     */
    public function setBuilder(IGridBuilder $builder)
    {
        $this->c->builder = $builder;
        return $this;
    }

    /**
     * get grid builder
     * @return IGridBuilder
     */
    public function getBuilder(): ?IGridBuilder
    {
        return isset($this->c->builder) ? $this->c->builder : null;
    }
    
    /**
     * reload grid
     * @return void
     */
    public function reload()
    {
        $this->redrawControl(self::GRID_CONTROL);
        return;
    }

    /**
     * redraw one row
     * @param mixed $id
     * @return void
     */
    public function redrawItem($id)
    {
        $this[self::GRID_CONTROL]->redrawItem($id);
        return;
    }

    public function getStyles()
    {
        if(isset($this->c->styles) && !empty($this->c->styles)){
            return $this->c->styles;
        } else {
            return $this->getConfigurator()->get("styles");
        }
    }

    public function setStyle(string $key,string $value)
    {
        $this->c->styles[$key] = $value;
        return $this;
    }
    
    /**
     * render control
     * @return void
     */
    public function render()
    {
        $this->beforeRender($this[self::GRID_CONTROL]);
        $this[self::GRID_CONTROL]->setDataSource($this->getDatasource());
        if($this->template instanceof ITemplate){
            $this->template->styles = $this->getStyles();
            $this->template->title = $this->getTitle();
            $this->template->comment = $this->getComment();
            $this->template->footer = $this->getFooter();
            $this->template->simple = $this->getSimple();
            $this->template->name = $this->getName();
            $this->template->setFile($this->getTemplatePath());
            $this->template->render();
        }

        $this->afterRender($this[self::GRID_CONTROL]);
        return;
    }
    
    /**
     * datagrid factory
     * @param string $name
     * @return DataGrid
     */
    public function createComponentGrid(string $name): DataGrid
    {
        if ($this->getDatasource()==null) {
            throw new GridBuilderException("ERROR: Dataset is null !!", GridBuilderException::INVALID_DATASET);
        }
        $grid = $this->getGridFactory()->create();
        $class = $this->getGridFactory()->getClass();
        $grid->setDataSource($this->getDatasource());
        $class::$icon_prefix =  $this->getIconPrefix();
        $this->addComponent($grid, $name);
        $this->beforeBuild($grid);
        $this->configGrid($grid);
        $this->buildGrid($grid);
        $this->afterBuild($grid);
        $paginator = $grid->getPaginator();
        if($paginator!=null && $paginator instanceof DataGridPaginator){
            $paginator->setTemplateFile($this->getGridPaginatorTemplatePath());
        }
        
        return $grid;
    }

    /**
     * config grid
     * @param DataGrid $grid
     * @return DataGrid
     */
    protected function configGrid(DataGrid $grid): DataGrid
    {
        if ($this->getTranslator() instanceof ITranslator) {
            $grid->setTranslator($this->getTranslator());
        }
        return $grid;
    }

    /**
     * build datagrid
     * @param DataGrid $grid
     * @return DataGrid
     */
    protected function buildGrid(DataGrid $grid):DataGrid
    {
        if ($this->getBuilder() instanceof IGridBuilder) {
            $grid = $this->getBuilder()
                         ->setObject($this)
                         ->build($grid);
        } elseif ($this->isBuilderEnabled()) {
            $this->setBuilder(new GridBuilder());
            $grid = $this->getBuilder()
                         ->setObject($this)
                         ->build($grid);
        } else {}
        return $grid;
    }
    
    /**
     * check if callback is valid
     * @param string $callback
     * @param string $column
     * @return boolean
     */
    protected function checkCallback(string $callback, string $column=null)
    {
        if ($column==null) {
            if (isset($this->getCallbacks()->{$callback})) {
                Callback::check($this->getCallbacks()->{$callback});
                return true;
            } else {
                return false;
            }
        } else {
            if (isset($this->getCallbacks()->{$callback}[$column])) {
                Callback::check($this->getCallbacks()->{$callback}[$column]);
                return true;
            } else {
                return false;
            }
        }
    }
    
    /**
     * invoke callback
     * @param string $callback
     * @param mixed | null $column
     * @param mixed | null $param1
     * @param mixed | null $param2
     * @param mixed | null $param3
     * @param mixed | null $param4
     * @param mixed | null $param5
     * @return mixed | null
     * @throws GridCallbackException
     */
    protected function invokeCallback(string $callback, $column=null, $param1=null, $param2=null, $param3=null, $param4=null, $param5=null)
    {
        if (!$this->checkCallback($callback, $column)) {
            throw new GridCallbackException("ERROR: Invalid callback", GridCallbackException::INVALID_CALLBACK);
        }
        if ($column==null) {
            $method = $this->getCallbacks()->$callback;
            return $method($param1, $param2, $param3, $param4, $param5);
        } else {
            $method = $this->getCallbacks()->$callback[$column];
            return $method($param1, $param2, $param3, $param4, $param5);
        }
    }
    
    /**
     * get all grid callbacks
     * @return array
     */
    public function getCallbacks()
    {
        return $this->c->callbacks;
    }

    /**
     * handle toolbar btn callback
     * @param string $name
     * @param mixed $params
     * @return mixed
     */
    public function handleToolbar(string $name, $event,$params)
    {
        if($event==false){
            return $this->invokeCallback(GridBuilder::TOOLBAR_BUTTON_CALLBACK, $name, $this, unserialize($params));
        } elseif(!empty($event)){
            $data = $this->getGridEventFactory()->create($this[self::GRID_CONTROL],$this,null,$params,$event);
            $this->on($event, $data);
            return;
        } else {
            $this->toolbar($name, $params);
            return;
        }
    }

    public function toolbar(string $name,$params)
    {}

    /**
     * handle sort 
     * @param mixed $item_id
     * @param mixed $prev_id
     * @param mixed $next_id
     * @param mixed $parent_id
     * @return mixed
     */
    public function handleSort($item_id, $prev_id, $next_id, $parent_id=null)
    {
        if ($this->checkCallback(GridBuilder::SORTING_CALLBACK)) {
            $this->invokeCallback(GridBuilder::SORTING_CALLBACK, null, $item_id, $prev_id, $next_id, $parent_id, $this);
            return;
        } elseif($this->getEvent("onReSort")!=null){
            $this->on(
                $this->getEvent("onReSort"),
                $this->getGridEventFactory()->create(
                    $this[static::GRID_CONTROL],
                    $this,
                    null,
                    [
                        "itemId"=>$item_id,
                        "prevId"=>$prev_id,
                        "nextId"=>$next_id,
                        "parentId"=>$parent_id
                    ],
                    $this->getEvent("onReSort")
                )
            );
            return;
        } else {
            $this->sort($this[self::GRID_CONTROL], $item_id, $prev_id, $next_id, $parent_id);
            return;
        }
    }

    /**
     * fire event
     * @param string $eventName
     * @param BaseEvent $data
     * @return void
     */
    public function on(string $eventName,BaseEvent $data)
    {
        $this->getEventDispatcher()->dispatch($eventName, $data);
        return;
    }

    public function getIconPrefix()
    {
        return $this->c->iconPrefix;
    }

    public function setIconPrefix($iconPrefix)
    {
        $this->c->iconPrefix = $iconPrefix;
        return $this;
    }

    public function setLinks(?array $links)
    {
        $this->c->links = $links;
        return $this;
    }

    public function getLinks()
    {
        return isset($this->c->links) ? $this->c->links : [];
    }

    public function getGridFactory()
    {
        return $this->c->gridFactory;
    }

    public function getGridClass()
    {
        return $this->c->gridClass;
    }

    /**
     * @return IGridEventFactory
     */
    public function getGridEventFactory()
    {
        return $this->c->gridEventFactory;
    }

    public function getGridRowEventFactory()
    {
        return $this->c->gridRowEventFactory;
    }

    public function getSimple()
    {
        return isset($this->c->simple) ? $this->c->simple : false;
    }

    public function setToolbarHandler(string $handler)
    {
        $this->c->toolbarHandler = $handler;
        return $this;
    }

    public function getToolbarHandler()
    {
        return $this->c->toolbarHandler;
    }

    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function setGridTemplatePath(string $path)
    {
        $this->c->gridTemplatePath = $path;
        return $this;
    }

    public function getGridTemplatePath()
    {
        return $this->c->gridTemplatePath;
    }

    public function setGridPaginatorTemplatePath(string $path)
    {
        $this->c->gridPaginatorTemplatePath = $path;
        return $this;
    }

    public function getGridPaginatorTemplatePath()
    {
        return $this->c->gridPaginatorTemplatePath;
    }

    public function setStatusTemplatePath(string $path)
    {
        $this->c->statusTemplatePath = $path;
        return $this;
    }

    public function getStatusTemplatePath()
    {
        return $this->c->statusTemplatePath;
    }

    public function setDetailTemplatePath(string $path)
    {
        $this->c->detailTemplatePath = $path;
        return $this;
    }

    public function getDetailTemplatePath()
    {
        return $this->c->detailTemplatePath;
    }

    public function setFilterTemplatePath(string $filter,string $path)
    {
        $this->c->filtersTemplatesPath[$filter] = $path;
        return $this;
    }

    public function getFilterTemplatePath(string $filter)
    {
        return isset($this->c->filtersTemplatesPath[$filter]) ? $this->c->filtersTemplatesPath[$filter] : null;
    }

    public function getGridStatusSettings(string $key)
    {
        return isset($this->c->gridStatusSettings[$key]) ? $this->c->gridStatusSettings[$key] : null;
    }

    public function setGridStatusSettings(string $key,string $value)
    {
        $this->c->gridStatusSettings[$key] = $value;
        return $this;
    }

    public function getNumberFormat(string $key)
    {
        return isset($this->c->numberFormat[$key]) ? $this->c->numberFormat[$key] : null;
    }

    public function setNumberFormat(string $key,string $value)
    {
        $this->c->numberFormat[$key]=$value;
        return $this;
    }

    public function getDatetimeFormat(string $key)
    {
        return isset($this->c->datetimeFormat[$key]) ? $this->c->datetimeFormat[$key] : null;
    }

    public function setDatetimeFormat(string $key,string $value)
    {
        $this->c->datetimeFormat[$key] = $value;
        return $this;
    }

    public function setSortableHandler(string $handler)
    {
        $this->c->sortableHandler = $handler;
        return $this;
    }

    public function getSortableHandler()
    {
        return $this->c->sortableHandler;
    }

    public function setEvent(string $key,string $event)
    {
        $this->c->events[$key] = $event;
        return $this;
    }

    public function getEvent(string $key) : ?string
    {
        return isset($this->c->events[$key]) ? $this->c->events[$key] : null;
    }
}
