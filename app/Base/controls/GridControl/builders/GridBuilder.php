<?php
namespace app\Base\controls\GridControl\builders;

use app\Base\controls\GridControl\traits\TCallbacks;
use app\Base\controls\GridControl\builders\IGridBuilder;
use app\Base\controls\GridControl\builders\ColumnBuilder;
use app\Base\controls\GridControl\GridControl;
use app\Base\controls\GridControl\configurators\GridConfig;
use app\Base\controls\GridControl\exceptions\GridBuilderException;
use Nette\Utils\ArrayHash;
use Nette\Localization\ITranslator;
use Nette\Reflection\ClassType;
use Nette\Utils\Strings;
use Ublaboo\DataGrid\DataGrid;

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
        "action"=>"\app\Base\controls\GridControl\builders\ActionsBuilder",
        "itemDetail"=>"\app\Base\controls\GridControl\builders\ItemDetailBuilder",
        "toolbarButton"=>"\app\Base\controls\GridControl\builders\ToolbarButtonsBuilder",
        "groupAction"=>"\app\Base\controls\GridControl\builders\GroupActionsBuilder",
        "export"=>"\app\Base\controls\GridControl\builders\ExportBuilder",
        "inlineActions"=>"\app\Base\controls\GridControl\builders\InlineActionsBuilder"
    ];
    
    /**
     * @var GridControl
     */
    protected $object;
    
    /**
     * @var ArrayHash
     */
    protected $callbacks;
    
    /**
     * @var ITranslator
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
            $configLoader = "get" . Strings::firstUpper($configKey);
            if ($this->configurator->$configLoader(true)!=null) {
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
        $this->callbacks = $this->object->getCallbacks();
        $this->translator = $this->object->getTranslator();
        $this->configurator = $this->object->getConfigurator();
        
        $primaryKey = $this->configurator->getPrimaryKey();
        if ($primaryKey!=null) {
            $grid->setPrimaryKey($primaryKey);
        }
        
        $pagination = $this->configurator->getPagination();
        if ($pagination!=null) {
            $grid->setPagination($pagination);
        }
 
        $itemsPerPageList = $this->configurator->getItemsPerPageList();
        if ($itemsPerPageList!=null) {
            $grid->setItemsPerPageList($itemsPerPageList);
        }
        
        $defaultSort = $this->configurator->getDefaultSort(true);
        if ($defaultSort!=null) {
            if (count($defaultSort)==1) {
                $grid->setDefaultSort($defaultSort[0]);
            } else {
                $grid->setDefaultSort($defaultSort[0], $defaultSort[1]);
            }
        }
        
        $multiSort = $this->configurator->getMultiSort();
        if ($multiSort!=null) {
            $grid->setMultiSortEnabled($multiSort);
        }
        
        $defaultPerPage = $this->configurator->getDefaultPerPage();
        if ($defaultPerPage!=null) {
            $grid->setDefaultPerPage($defaultPerPage);
        }
        
        $columnsHidable = $this->configurator->getColumnsHidable();
        if ($columnsHidable!=null && $columnsHidable==true) {
            $grid->setColumnsHideable();
        }
        
        $summary = $this->configurator->getSummary();
        if ($summary!=null) {
            if ($this->checkCallback(static::SUMMARY_CALLBACK)) {
                $s = $grid->setColumnsSummary($summary, function ($item, $column) use ($t) {
                    return $t->invokeCallback(static::SUMMARY_CALLBACK, null, $item, $column, $t->object);
                });
            } else {
                $s = $grid->setColumnsSummary($summary);
            }
            
            $format = $this->configurator->getSummaryFormat();
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
        
        $gridTpl = $this->configurator->getGridCustomTemplate();
        if ($gridTpl==null) {
            $grid->setTemplateFile($this->object->_gridTemplatePath);
        } else {
            $grid->setTemplateFile($gridTpl);
        }
        
        $defaultFilters = $this->configurator->getDefaultFilters();
        if ($defaultFilters!=null) {
            $grid->setDefaultFilter($defaultFilters);
        }
        
        $outerFilter = $this->configurator->getOuterFilter();
        if ($outerFilter!=null && $outerFilter==true) {
            $grid->setOuterFilterRendering();
        }
        
        $rememberState = $this->configurator->getRememberState();
        if ($rememberState!=null) {
            $grid->setRememberState($rememberState);
        }
        
        $strictSession = $this->configurator->getStrictSessionFilterValues();
        if ($strictSession!=null) {
            $grid->setStrictSessionFilterValues($strictSession);
        }
        
        $refreshUrl = $this->configurator->getRefreshUrl();
        if ($refreshUrl!=null) {
            $grid->setRefreshUrl($refreshUrl);
        }
        
        $autosubmit = $this->configurator->getAutosubmitFilter();
        if ($autosubmit==null) {
            $grid->setAutoSubmit(true);
        } else {
            $grid->setAutoSubmit($autosubmit);
        }
        
        $sortable = $this->configurator->getSortable();
        if ($sortable!=null) {
            $grid->setSortable($sortable);
            $handler = $this->configurator->getSortableHandler();
            $grid->setSortableHandler($handler==null ? $this->object->getName() . ":" . $this->object->_defaultSortableHandler : $handler);
        }
        
        $happy = $this->configurator->getHappyComponents();
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
