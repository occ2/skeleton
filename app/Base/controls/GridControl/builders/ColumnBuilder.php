<?php
namespace occ2\GridControl;

use Ublaboo\DataGrid\DataGrid;
use Nette\Reflection\Property;
use Nette\Utils\Strings;
use Nette\Utils\ArrayHash;
use Ublaboo\DataGrid\Column\Column;
use Ublaboo\DataGrid\Column\ColumnStatus;

/**
 * ColumnBuilder
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class ColumnBuilder
{
    use TCallbacks;
    
    protected $grid;
    
    protected $property;
    
    protected $object;
    
    protected $translator;
    
    public function __construct(GridControl $object, DataGrid $grid, Property $property, $callbacks)
    {
        $this->object = $object;
        $this->grid = $grid;
        $this->property = $property;
        $this->callbacks = $callbacks;
        return;
    }
    
    public function build()
    {
        return $this->addColumns($this->grid, $this->property);
    }
    
    protected function addColumns(DataGrid $grid, Property $property)
    {
        if ($property->getName()!="name" &&
           $property->getName()!="parent" &&
           $property->getName()!="presenter" &&
           $property->getName()!="params" &&
           $property->getName()!="snippetMode" &&
           $property->getName()!="linkCurrent" &&
           $property->getName()!="template" &&
           !Strings::startsWith($property->getName(), "_") &&
           !Strings::startsWith($property->getName(), "on")) {
            $config = new GridColumnsConfig($property);
            $this->object->{$config->name} = $this->{GridBuilder::COLUMN_TYPES[$config->type]}(
                    $grid,
                    $config
           );
            return;
        }
    }
    
    protected function addColumnText(DataGrid $grid, GridColumnsConfig $config)
    {
        $column = $grid->addColumnText(
            $config->name,
            $config->label,
            $config->dbCol
        );
        $this->setupColumn($grid, $column, $config);
        return $column;
    }
    
    protected function addColumnDatetime(DataGrid $grid, GridColumnsConfig $config)
    {
        $column = $grid->addColumnDateTime(
            $config->name,
            $config->label,
            $config->dbCol
        );
        $config->datetimeFormat == null ? $column->setFormat($this->object->_defaultDatetimeFormat["php"]) : $column->setFormat(
                isset($config->datetimeFormat["php"]) ? $config->datetimeFormat["php"] : $this->object->_defaultDatetimeFormat["php"]
        );
        $this->setupColumn($grid, $column, $config);
        return $column;
    }
    
    protected function addColumnNumber(DataGrid $grid, GridColumnsConfig $config)
    {
        $column = $grid->addColumnNumber(
            $config->name,
            $config->label,
            $config->dbCol
        );
        $config->numberFormat == null ?: $column->setFormat(
                isset($config->numberFormat["decimals"]) ? $config->numberFormat["decimals"] : $this->object->_defaultNumberFormat["decimals"],
                isset($config->numberFormat["decPoint"]) ? $config->numberFormat["decPoint"] : $this->object->_defaultNumberFormat["decPoint"],
                isset($config->numberFormat["thousandsSeparator"]) ? $config->numberFormat["thousandsSeparator"] : $this->object->_defaultNumberFormat["thousandsSeparator"]
        );
        
        $this->setupColumn($grid, $column, $config);
        return $column;
    }
    
    protected function addColumnLink(DataGrid $grid, GridColumnsConfig $config)
    {
        $column = $grid->addColumnLink(
            $config->name,
            $config->label,
            $config->href,
            $config->dbCol,
            $config->params
        );
        $config->newTab == null ?: $column->setOpenInNewTab($config->newTab);
        $config->icon == null ?: $column->setIcon($config->icon);
        $config->class == null ?: $column->setClass($config->class);
        $config->title == null ?: $column->setTitle($this->object->text($config->title));
        $config->parameters == null ?: $column->addParameters($config->parameters);
        if (is_array($config->dataAttribute)) {
            foreach ($config->dataAttribute as $key=>$value) {
                $column->setDataAttribute($key, $value);
            }
        }
        $this->setupColumn($grid, $column, $config);
        return $column;
    }
    
    protected function addColumnStatus(DataGrid $grid, GridColumnsConfig $config)
    {
        $t = $this;
        $column = $grid->addColumnStatus(
            $config->name,
            $config->label,
            $config->dbCol
        );
        $column->setTemplate($this->object->_gridStatusTemplatePath);
        $config->options == null ?: $column->setOptions($config->options);
        
        if ($config->option !=null) {
            foreach ($config->option as $key=>$option) {
                $this->setupColumnStatus($column, $option,$key);
            }
        }
        
        if ($this->checkCallback(GridBuilder::STATUS_CHANGE_CALLBACK, $config->name)) {
            $column->onChange[] = function ($id, $new_value) use ($t,$config) {
                return $t->invokeCallback(GridBuilder::STATUS_CHANGE_CALLBACK, $config->name, $id, $new_value, $t->object);
            };
        }
        
        $this->setupColumn($grid, $column, $config);
        return $column;
    }
    
    protected function setupColumnStatus(ColumnStatus $column, ArrayHash $option,$key)
    {
        $key = isset($option->key) ? $option->key : $key;
        $text = !isset($option->text) ? $key : $this->object->text($option->text);
        $o = $column->addOption($key, $text);
        !isset($option->icon) ? : $o->setIcon($option->icon);
        !isset($option->iconSecondary) ? : $o->setIconSecondary($option->iconSecondary);
        !isset($option->class) ? $this->object->_defaultGridStatusSettings["class"] : $o->setClass($option->class);
        !isset($option->classSecondary) ? $o->setClassSecondary($this->object->_defaultGridStatusSettings["classSecondary"]) : $o->setClassSecondary($option->classSecondary);
        !isset($option->title) ? $o->setTitle($this->object->text($text)): $o->setTitle($this->object->text($option->title));
        !isset($option->classInDropdown) ? $o->setClassInDropdown($this->object->_defaultGridStatusSettings["classInDropdown"]): $o->setClassInDropdown($option->classInDropdown);
        $o->endOption();
        return $column;
    }
    
    protected function setupColumn(DataGrid $grid, Column $column, GridColumnsConfig $config)
    {
        $t = $this;
        // set if column as translatable
        if ($config->translate!=null) {
            $column->setRenderer(function ($item) use ($config,$t) {
                return $t->object->text($item->{$config->name});
            });
        }
        
        if ($this->checkCallback(GridBuilder::EDITABLE_CALLBACK, $config->name)) {
            $column->setEditableCallback(function ($id, $value) use ($t,$config) {
                return $t->invokeCallback(GridBuilder::EDITABLE_CALLBACK, $config->name, $id, $value, $t->object);
            });
            if (isset($config->editableType)) {
                if ($config->editableInputType!="select") {
                    $column->setEditableInputTypeSelect(
                        $this->invokeCallback(GridBuilder::LOAD_OPTIONS_CALLBACK, $config->name),
                        isset($config->editableAttributes) ? $config->editableAttributes : []
                    );
                } else {
                    $column->setEditableInputType(
                        $config->editableType,
                        isset($config->editableAttributes) ? $config->editableAttributes : []
                    );
                }
            }
            
            if ($this->checkCallback(GridBuilder::EDITABLE_VALUE_CALLBACK, $config->name)) {
                $column->setEditableValueCallback(function ($row) use ($t,$config) {
                    return $t->invokeCallback(GridBuilder::EDITABLE_VALUE_CALLBACK, $config->name, $row, $t->object);
                });
            }
        }
        
        // set column as sortable
        $config->sortable == null || $config->sortable==false ?: is_string($config->sortable) ? $column->setSortable($config->sortable) : $column->setSortable();
        
        // set custom column template
        $config->template == null ?: $column->setTemplate($config->template);
        
        // custom column renderer
        $config->replacement == null ?: $column->setReplacement($config->replacement);
        
        // set template escaping
        $config->templateEscaping == null ?: $column->setTemplateEscaping($config->templateEscaping);
        
        // set reset pagination after sorting
        $config->resetPaginationAfterSorting == null ?: $column->setSortableResetPagination($config->resetPaginationAfterSorting);
        
        // set align
        $config->align == null ?: $column->setAlign($config->align);
        
        // set default hide
        $config->hidden == null ?: $column->setDefaultHide($config->hidden);
        
        // add additional attributes
        $config->attributes == null ?: $column->addAttributes($config->attributes);
        
        // set column content fit
        $config->fitContent == null ?: $column->setFitContent($config->fitContent);
        
        // set header escaping
        $config->headerEscaping == null ?: $column->setHeaderEscaping($config->headerEscaping);
        
        // set sort options
        $config->sort == null ?: $column->setSort($config->sort);
        
        // set translating on header
        $config->translatableHeader == null ?: $column->setTranslatableHeader($config->translatableHeader);
        
        $this->setupCallbacks($grid, $column, $config);
        $this->addFilters($column, $config);
        
        return $column;
    }
    
    protected function setupCallbacks(DataGrid $grid, Column $column, $config)
    {
        $t = $this;
        if ($this->checkCallback(GridBuilder::COLUMN_RENDERER_CALLBACK, $config->name)) {
            if ($this->checkCallback(GridBuilder::COLUMN_CONDITION_CALLBACK, $config->name)) {
                $column->setRenderer(function ($item) use ($config,$t) {
                    return $t->invokeCallback(GridBuilder::COLUMN_RENDERER_CALLBACK, $config->name, $item);
                }, function ($item) use ($config,$t) {
                    return (bool) $t->invokeCallback(GridBuilder::COLUMN_CONDITION_CALLBACK, $config->name, $item, $t->object);
                });
            } else {
                $column->setRenderer(function ($item) use ($config,$t) {
                    return $t->invokeCallback(GridBuilder::COLUMN_RENDERER_CALLBACK, $config->name, $item, $t->object);
                });
            }
        }
        
        if ($this->checkCallback(GridBuilder::SORTABLE_CALLBACK, $config->name)) {
            $column->setSortableCallback(function ($datasource, $sort) use ($config,$t) {
                return $t->invokeCallback(GridBuilder::SORTABLE_CALLBACK, $config->name, $datasource, $sort, $t->object);
            });
        }
        
        if ($this->checkCallback(GridBuilder::COLUMN_CALLBACK, $config->name)) {
            $grid->addColumnCallback($config->name, function ($column, $item) use ($t,$config) {
                $t->invokeCallback(GridBuilder::COLUMN_CALLBACK, $config->name, $column, $item, $t->object);
            });
        }
        
        return $column;
    }
    
    protected function addFilters($column, $config)
    {
        $b = new FilterBuilder($this->object, $column, $config, $this->callbacks);
        return $b->build();
    }
}
