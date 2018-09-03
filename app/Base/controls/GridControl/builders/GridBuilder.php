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
use app\Base\controls\GridControl\builders\IGridBuilder;
use app\Base\controls\GridControl\builders\ColumnBuilder;
use app\Base\controls\GridControl\GridControl;
use app\Base\controls\GridControl\configurators\GridConfig;
use app\Base\controls\GridControl\exceptions\GridBuilderException;
use app\Base\controls\GridControl\DataGrid;
use Kdyby\Translation\ITranslator;
use Nette\Reflection\ClassType;
use Nette\Utils\Strings;

/**
 * GridBuilder
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class GridBuilder implements IGridBuilder
{
    use TCallbacks;
    
    const LOAD_OPTIONS_CALLBACK="loadOptions", // function($control):array{}
          ITEM_DETAIL_CALLBACK="itemDetail",  // function($item, $control):string{}
          ITEM_DETAIL_CONDITION_CALLBACK="itemDetailCondition", // function($item, $control):bool{}
          TOOLBAR_BUTTON_CALLBACK="toolbarButton", // function($control, $params):void{}
          COLUMN_RENDERER_CALLBACK="columnRenderer", // function($item,$control):string{}
          COLUMN_CONDITION_CALLBACK="columnCondition", // function($item,$control):bool{}
          SORTABLE_CALLBACK="sortColumn", // function($datasource,$sort,$control):void{}
          STATUS_CHANGE_CALLBACK="statusChange", // function($id,$value,$control):void{}
          SUMMARY_CALLBACK="summary", // function($item,$column,$control):float{}
          SUMMARY_RENDERER_CALLBACK="summaryRenderer", // function($sum,$columnName,$control):string{}
          COLUMN_CALLBACK="column", // function($column, $item, $control):void{}
          FILTER_CONDITION_CALLBACK="filterCondition", // function($datasource,$value,$control):void{}
          ACTION_CALLBACK="action", // function($id,$grid,$control):void{}
          ACTION_CONFIRM_CALLBACK="actionConfirm", // function($item, $control):string{}
          ACTION_ICON_CALLBACK="actionIcon", // function($item, $control):string{}
          ACTION_CLASS_CALLBACK="actionClass", // function($item, $control):string{}
          ACTION_TITLE_CALLBACK="actionTitle", // function($item, $control):string{}
          SORTING_CALLBACK="sorting", // function($itemId, $prevId, $nextId, $control):void{}
          GROUP_ACTION_OPTIONS_CALLBACK="groupActionOptions", // function($control):array{}
          GROUP_ACTION_CALLBACK="groupAction", // function($ids, $control, $option | $value):mixed{}
          ALLOW_ROWS_GROUP_ACTION_CALLBACK="allowRowsGroupAction", // function($item,$control):bool{}
          ALLOW_ROWS_INLINE_EDIT_CALLBACK="allowRowsInlineEdit", // function($item,$control):bool{}
          ALLOW_ROWS_ACTION_CALLBACK="allowRowsAction", // function($item,$control):bool{}
          ALLOW_ROWS_MULTIACTION_CALLBACK="allowRowsMultiAction", // function($item,$control):bool{}  - key is in multiaction-action format
          ALLOW_TOOLBAR_BUTTON_CALLBACK="allowToolbarButton",
          ROW_CALLBACK="row",  // function($item,$tr,$control):void{}
          EXPORT_CALLBACK="export", // function($datasource,$grid,$control):void{}
          EDITABLE_CALLBACK="editable", // function($id,$value,$control):void{}
          EDITABLE_VALUE_CALLBACK="editableValue", // function($row,$control):string{}
          INLINE_FORM_CALLBACK="inlineForm", // function($container,$control):void{}
          INLINE_LOAD_OPTIONS_CALLBACK="inlineLoadOptions", // function($control):array{}
          INLINE_FORM_FILL_CALLBACK="inlineFormFill", // function($container,$item,$control):void{}
          INLINE_FORM_ADD_SUBMIT_CALLBACK="inlineAddSubmit", // function($values,$control):void{}
          INLINE_FORM_EDIT_SUBMIT_CALLBACK="inlineEditSubmit", // function($id,$values,$control):void{}
          INLINE_CUSTOM_REDRAW_CALLBACK="inlineCustomRedraw" // function ($grid,$control):void{}
            ;
    
    const COLUMN_TYPES=[
        "text"=>"addColumnText",
        "number"=>"addColumnNumber",
        "datetime"=>"addColumnDatetime",
        "link"=>"addColumnLink",
        "status"=>"addColumnStatus"
    ];
    
    const FILTER_TYPES=[
        "text"=>"addFilterText",
        "select"=>"addFilterSelect",
        "multiselect"=>"addFilterMultiSelect",
        "date"=>"addFilterDate",
        "range"=>"addFilterRange",
        "daterange"=>"addFilterDateRange"
    ];

    /**
     * @var array
     */
    protected $additionalBuilder=[
        "action"=>ActionsBuilder::class,
        "itemDetail"=>ItemDetailBuilder::class,
        "toolbarButton"=>ToolbarButtonsBuilder::class,
        "groupAction"=>GroupActionsBuilder::class,
        "export"=>ExportBuilder::class,
        "inlineActions"=>InlineActionsBuilder::class
    ];
    
    /**
     * @var GridControl
     */
    protected $object;
    
    /**
     * @var array
     */
    protected $callbacks;
    
    /**
     * @var ITranslator | null
     */
    protected $translator;
    
    /**
     * @var ClassType
     */
    protected $classType;
    
    /**
     * @var GridConfig
     */
    protected $configurator;
    
    /**
     * set object of control
     * @param GridControl $object
     * @return $this
     */
    public function setObject(GridControl $object)
    {
        $this->object = $object;
        $this->classType = ClassType::from($object);
        return $this;
    }
    
    /**
     * build datagrid from annotations
     * @param DataGrid $grid
     * @return DataGrid
     */
    public function build(DataGrid $grid): DataGrid
    {
        $this->setup($grid);
        foreach ($this->classType->getProperties() as $property) {
            $columns = new ColumnBuilder($this->object, $grid, $property, $this->callbacks);
            $columns->build();
        }
        $this->setupGridCallbacks($grid);
        
        foreach ($this->additionalBuilder as $configKey => $builderClass) {
            if ($this->configurator->get($configKey,true)!=null) {
                $o = new $builderClass($this->object, $grid, $this->configurator, $this->callbacks);
                $o->build();
            }
        }
        return $grid;
    }
    
    /**
     * add additinal builder class
     * @param string $configKey
     * @param string $builderClass
     * @return $this
     */
    public function addAdditionalBuilder($configKey, $builderClass)
    {
        $this->additionalBuilder[$configKey]=$builderClass;
        return $this;
    }
    
    /**
     * setup datagrid parameters
     * @param DataGrid $grid
     * @return void
     * @throws GridBuilderException
     */
    protected function setup(DataGrid $grid)
    {
        $t = $this;
        if (!$this->object instanceof GridControl) {
            throw new GridBuilderException("ERROR: Grid object must be set before build", GridBuilderException::NO_PARENT_SET);
        }
        $this->callbacks = (array) $this->object->getCallbacks();
        $this->translator = $this->object->getTranslator();
        $this->configurator = $this->object->getConfigurator();
        
        $primaryKey = $this->configurator->get("primaryKey.");
        if ($primaryKey!=null) {
            $grid->setPrimaryKey($primaryKey);
        }
        
        $pagination = $this->configurator->get("pagination");
        if ($pagination!=null) {
            $grid->setPagination($pagination);
        }
 
        $itemsPerPageList = $this->configurator->get("itemsPerPageList");
        if ($itemsPerPageList!=null) {
            $grid->setItemsPerPageList($itemsPerPageList);
        }
        
        $defaultSort = $this->configurator->get("defaultSort", true);
        if ($defaultSort!=null) {
            if (count($defaultSort)==1) {
                $grid->setDefaultSort($defaultSort[0]);
            } else {
                $grid->setDefaultSort($defaultSort[0], $defaultSort[1]);
            }
        }
        
        $multiSort = $this->configurator->get("multiSort");
        if ($multiSort!=null) {
            $grid->setMultiSortEnabled($multiSort);
        }
        
        $defaultPerPage = $this->configurator->get("defaultPerPage");
        if ($defaultPerPage!=null) {
            $grid->setDefaultPerPage($defaultPerPage);
        }
        
        $columnsHidable = $this->configurator->get("columnsHidable");
        if ($columnsHidable!=null && $columnsHidable==true) {
            $grid->setColumnsHideable();
        }
        
        $summary = $this->configurator->get("summary");
        if ($summary!=null) {
            if ($this->checkCallback(static::SUMMARY_CALLBACK)) {
                $s = $grid->setColumnsSummary($summary, function ($item, $column) use ($t) {
                    return $t->invokeCallback(static::SUMMARY_CALLBACK, null, $item, $column, $t->object);
                });
            } else {
                $s = $grid->setColumnsSummary($summary);
            }
            
            $format = $this->configurator->get("summaryFormat");
            if ($format!=null) {
                $s->setFormat(
                    $format["key"],
                    isset($format["decimals"]) ? $format["decimals"] : 0,
                    isset($format["decPoint"]) ? $format["decPoint"] : ".",
                    isset($format["thousandSeparator"]) ? $format["thousandSeparator"] : " "
                );
            }
            if ($this->checkCallback(self::SUMMARY_RENDERER_CALLBACK)) {
                $s->setRenderer(function ($sum, string $column) use ($t) : string {
                    return (string) $t->invokeCallback(self::SUMMARY_RENDERER_CALLBACK, null, $sum, $column, $t->object);
                });
            }
        }
        
        $gridTpl = $this->configurator->get("gridCustomTemplate");
        if ($gridTpl==null) {
            $grid->setTemplateFile($this->object->getGridTemplatePath());
        } else {
            $grid->setTemplateFile($gridTpl);
        }
        
        $defaultFilters = $this->configurator->get("defaultFilters");
        if ($defaultFilters!=null) {
            $grid->setDefaultFilter($defaultFilters);
        }
        
        $outerFilter = $this->configurator->get("outerFilter");
        if ($outerFilter!=null && $outerFilter==true) {
            $grid->setOuterFilterRendering();
        }
        
        $rememberState = $this->configurator->get("rememberState");
        if ($rememberState!=null) {
            $grid->setRememberState($rememberState);
        }
        
        $strictSession = $this->configurator->get("strictSessionFilterValues");
        if ($strictSession!=null) {
            $grid->setStrictSessionFilterValues($strictSession);
        }
        
        $refreshUrl = $this->configurator->get("refreshUrl");
        if ($refreshUrl!=null) {
            $grid->setRefreshUrl($refreshUrl);
        }
        
        $autosubmit = $this->configurator->get("autosubmitFilter");
        if ($autosubmit==null) {
            $grid->setAutoSubmit(true);
        } else {
            $grid->setAutoSubmit($autosubmit);
        }
        
        $sortable = $this->configurator->get("sortable");
        if ($sortable!=null) {
            $grid->setSortable($sortable);
            $handler = $this->configurator->get("sortableHandler");
            $grid->setSortableHandler($handler==null ? $this->object->getName() . ":" . $this->object->getSortableHandler() : $handler);
            $sortableEvent = $this->configurator->get("onReSort");
            if($sortableEvent!=null){
                $this->object->setEvent("onReSort",$sortableEvent);
            }
        }
        
        $happy = $this->configurator->get("happyComponents");
        if ($happy!=null) {
            $grid->useHappyComponents($happy);
        }
        
        return;
    }

    /**
     * setup grid callbacks
     * @param DataGrid $grid
     * @return void
     */
    protected function setupGridCallbacks(DataGrid $grid)
    {
        $t = $this;
        if ($this->checkCallback(static::ALLOW_ROWS_GROUP_ACTION_CALLBACK)) {
            $grid->allowRowsGroupAction(function ($item) use ($t) {
                return $t->invokeCallback(static::ALLOW_ROWS_GROUP_ACTION_CALLBACK, null, $item, $t->object);
            });
        }
        
        if ($this->checkCallback(static::ALLOW_ROWS_INLINE_EDIT_CALLBACK)) {
            $grid->allowRowsInlineEdit(function ($item) use ($t) {
                return $t->invokeCallback(static::ALLOW_ROWS_INLINE_EDIT_CALLBACK, null, $item, $t->object);
            });
        }
        
        if ($this->checkCallback(static::ROW_CALLBACK)) {
            $grid->setRowCallback(function ($item, $tr) use ($t) {
                return $t->invokeCallback(static::ROW_CALLBACK, null, $item, $tr, $t->object);
            });
        }
        return;
    }
}
