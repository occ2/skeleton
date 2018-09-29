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
use app\Base\controls\GridControl\traits\TEntity;
use app\Base\controls\GridControl\builders\IColumnGridBuilder;
use app\Base\controls\GridControl\GridControl;
use app\Base\controls\GridControl\configurators\GridColumnsConfig;
use app\Base\controls\GridControl\builders\GridBuilder;
use app\Base\controls\GridControl\builders\FilterBuilder;
use app\Base\controls\GridControl\DataGrid;
use app\Base\controls\GridControl\events\GridRowEventData;
use Ublaboo\DataGrid\Column\Column;
use Ublaboo\DataGrid\Column\ColumnStatus;
use Ublaboo\DataGrid\Column\ColumnDateTime;
use Ublaboo\DataGrid\Column\ColumnText;
use Ublaboo\DataGrid\Column\ColumnNumber;
use Ublaboo\DataGrid\Column\ColumnLink;
use Nette\Reflection\Property;
use Nette\Utils\Strings;
use Nette\Utils\ArrayHash;
use Kdyby\Translation\ITranslator;

/**
 * ColumnBuilder
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class ColumnBuilder implements IColumnGridBuilder
{
    use TCallbacks;
    use TEntity;

    /**
     * @var DataGrid
     */
    protected $grid;

    /**
     * @var Property
     */
    protected $property;

    /**
     * @var GridControl
     */
    protected $object;

    /**
     * @var ITranslator
     */
    protected $translator;

    /**
     * @param GridControl $object
     * @param DataGrid $grid
     * @param Property $property
     * @param array $callbacks
     * @return void
     */
    public function __construct(GridControl $object, DataGrid $grid, Property $property,array $callbacks)
    {
        $this->object = $object;
        $this->grid = $grid;
        $this->property = $property;
        $this->callbacks = $callbacks;
        return;
    }

    /**
     * @return void
     */
    public function build()
    {
        $this->addColumns($this->grid, $this->property);
        return;
    }

    /**
     * add columns
     * @param DataGrid $grid
     * @param Property $property
     * @return void
     */
    protected function addColumns(DataGrid $grid, Property $property)
    {
        if ($property->getName()!="name" &&
           $property->getName()!="parent" &&
           $property->getName()!="presenter" &&
           $property->getName()!="params" &&
           $property->getName()!="snippetMode" &&
           $property->getName()!="linkCurrent" &&
           $property->getName()!="template" &&
           $property->getName()!="c" &&
           !Strings::startsWith($property->getName(), "_") &&
           !Strings::startsWith($property->getName(), "on")) {
            $config = new GridColumnsConfig($property,$this->object);
            $method = GridBuilder::COLUMN_TYPES[$config->get("type")];
            $this->object->{$config->get("name")} = $this->$method(
                    $grid,
                    $config
           );
            return;
        }
    }

    /**
     * add text column
     * @param DataGrid $grid
     * @param GridColumnsConfig $config
     * @return ColumnText
     */
    protected function addColumnText(DataGrid $grid, GridColumnsConfig $config): ColumnText
    {
        $column = $grid->addColumnText(
            $config->get("name"),
            $config->get("label")==null ? $config->get("name") : $config->get("label"),
            $config->get("dbCol")
        );
        $this->setupColumn($grid, $column, $config);
        return $column;
    }

    /**
     * add datetime column
     * @param DataGrid $grid
     * @param GridColumnsConfig $config
     * @return ColumnDateTime
     */
    protected function addColumnDatetime(DataGrid $grid, GridColumnsConfig $config): ColumnDateTime
    {
        $column = $grid->addColumnDateTime(
            $config->get("name"),
            $config->get("label") == null ? $config->get("name") : $config->get("label"),
            $config->get("dbCol")
        );

        if($config->get("datetimeFormat") != null && isset($config->get("datetimeFormat")["php"])){
            $column->setFormat($config->get("datetimeFormat")["php"]);
        } else {
            $column->setFormat($this->object->getDatetimeFormat("php"));
        }

        $this->setupColumn($grid, $column, $config);
        return $column;
    }

    /**
     * add column number
     * @param DataGrid $grid
     * @param GridColumnsConfig $config
     * @return ColumnNumber
     */
    protected function addColumnNumber(DataGrid $grid, GridColumnsConfig $config): ColumnNumber
    {
        $column = $grid->addColumnNumber(
            $config->get("name"),
            $config->get("label")==null ? $config->get("name") : $config->get("label"),
            $config->get("dbCol")
        );
        $format = $config->get("numberFormat");
        $format == null ?: $column->setFormat(
                isset($format["decimals"]) ? $format["decimals"] : $this->object->getNumberFormat("decimals"),
                isset($format) ? $format["decPoint"] : $this->object->getNumberFormat("decPoint"),
                isset($format) ? $format["thousandsSeparator"] : $this->object->getNumberFormat("thousandsSeparator")
        );
        
        $this->setupColumn($grid, $column, $config);
        return $column;
    }

    /**
     * add link column
     * @param DataGrid $grid
     * @param GridColumnsConfig $config
     * @return ColumnLink
     */
    protected function addColumnLink(DataGrid $grid, GridColumnsConfig $config): ColumnLink
    {
        $column = $grid->addColumnLink(
            $config->get("name"),
            $config->get("label")==null ? $config->get("name") : $config->get("label"),
            $config->get("href"),
            $config->get("dbCol"),
            $config->get("params")
        );
        $config->get("newTab") == null ?: $column->setOpenInNewTab($config->get("newTab"));
        $config->get("icon") == null ?: $column->setIcon($config->get("icon"));
        $config->get("class") == null ?: $column->setClass($config->get("class"));
        $config->get("title") == null ?: $column->setTitle($this->object->_($config->get("title")));
        $config->get("parameters") == null ?: $column->addParameters($config->get("parameters"));
        if (is_array($config->get("dataAttribute"))) {
            foreach ($config->get("dataAttribute") as $key=>$value) {
                $column->setDataAttribute($key, $value);
            }
        }
        $this->setupColumn($grid, $column, $config);
        return $column;
    }

    /**
     * add status column
     * @param DataGrid $grid
     * @param GridColumnsConfig $config
     * @return ColumnStatus
     */
    protected function addColumnStatus(DataGrid $grid, GridColumnsConfig $config): ColumnStatus
    {
        $t = $this;
        $column = $grid->addColumnStatus(
            $config->get("name"),
            $config->get("label")==null ? $config->get("name") : $config->get("label"),
            $config->get("dbCol")
        );
        $column->setTemplate($this->object->getStatusTemplatePath());
        $config->get("options") == null ?: $column->setOptions($config->get("options"));
        
        if ($config->get("option",true) !=null) {
            foreach ($config->get("option",true) as $key=>$option) {
                $this->setupColumnStatus($column, $option,$key);
            }
        }
        
        if ($this->checkCallback(GridBuilder::STATUS_CHANGE_CALLBACK, $config->get("name"))) {
            $column->onChange[] = function ($id, $new_value) use ($t,$config) {
                return $t->invokeCallback(GridBuilder::STATUS_CHANGE_CALLBACK, $config->get("name"), $id, $new_value, $t->object);
            };
        } elseif ($config->get("event")!=null){
            $column->onChange[] = function ($id, $new_value) use ($t,$config,$grid) {
                $t->object->on(
                    $config->get("event"),
                    $t->object->getGridRowEventFactory()->create(
                        $id,
                        $new_value,
                        $grid,
                        $t->object,
                        $config->get("event")
                    )
                );
            };
        }
        
        $this->setupColumn($grid, $column, $config);
        return $column;
    }

    /**
     * setup column status
     * @param ColumnStatus $column
     * @param ArrayHash $option
     * @param string $key
     * @return ColumnStatus
     */
    protected function setupColumnStatus(ColumnStatus $column, ArrayHash $option,$key)
    {
        $key = isset($option->key) ? $option->key : $key;
        $text = !isset($option->text) ? $key : $this->object->_($option->text);
        $o = $column->addOption($key, $text);
        !isset($option->icon) ? : $o->setIcon($option->icon);
        !isset($option->iconSecondary) ? : $o->setIconSecondary($option->iconSecondary);
        !isset($option->class) ? $this->object->getGridStatusSettings("class") : $o->setClass($option->class);
        !isset($option->classSecondary) ? $o->setClassSecondary($this->object->getGridStatusSettings("classSecondary")) : $o->setClassSecondary($option->classSecondary);
        !isset($option->title) ? $o->setTitle($this->object->_($text)): $o->setTitle($this->object->_($option->title));
        !isset($option->classInDropdown) ? $o->setClassInDropdown($this->object->getGridStatusSettings("classInDropdown")): $o->setClassInDropdown($option->classInDropdown);
        $o->endOption();
        return $column;
    }

    /**
     * setup column
     * @param DataGrid $grid
     * @param Column $column
     * @param GridColumnsConfig $config
     * @return Column
     */
    protected function setupColumn(DataGrid $grid, Column $column, GridColumnsConfig $config)
    {
        $t = $this;
        // set if column as translatable
        if ($config->get("translate")!=null) {
            $column->setRenderer(function ($item) use ($config,$t) {
                return $t->object->_($t->getEntityProperty($item, $config->get("name")));
            });
        }
        
        if ($this->checkCallback(GridBuilder::EDITABLE_CALLBACK, $config->get("name"))) {
            $column->setEditableCallback(function ($id, $value) use ($t,$config) {
                return $t->invokeCallback(GridBuilder::EDITABLE_CALLBACK, $config->get("name"), $id, $value, $t->object);
            });
            if ($config->get("editableType")!==null) {
                if ($config->get("editableType")=="select") {
                    $column->setEditableInputTypeSelect(
                        $this->invokeCallback(GridBuilder::LOAD_OPTIONS_CALLBACK, $config->get("name")),
                        $config->get("editableAttributes")!==null ? $config->get("editableAttributes") : []
                    );
                } else {
                    $column->setEditableInputType(
                        $config->get("editableType"),
                        $config->get("editableAttributes")!==null ? $config->get("editableAttributes") : []
                    );
                }
            }
            
            if ($this->checkCallback(GridBuilder::EDITABLE_VALUE_CALLBACK, $config->get("name"))) {
                $column->setEditableValueCallback(function ($row) use ($t,$config) {
                    return $t->invokeCallback(GridBuilder::EDITABLE_VALUE_CALLBACK, $config->get("name"), $row, $t->object);
                });
            }
        }
        
        // set column as sortable
        $config->get("sortable") == null || $config->get("sortable")==false ?: is_string($config->get("sortable")) ? $column->setSortable($config->get("sortable")) : $column->setSortable();
        
        // set custom column template
        $config->get("template") == null ?: $column->setTemplate($config->get("template"));
        
        // custom column renderer
        $config->get("replacement") == null ?: $column->setReplacement($config->get("replacement"));
        
        // set template escaping
        $config->get("templateEscaping") == null ?: $column->setTemplateEscaping($config->get("templateEscaping"));
        
        // set reset pagination after sorting
        $config->get("resetPaginationAfterSorting") == null ?: $column->setSortableResetPagination($config->get("resetPaginationAfterSorting"));
        
        // set align
        $config->get("align") == null ?: $column->setAlign($config->get("align"));
        
        // set default hide
        $config->get("hidden") == null ?: $column->setDefaultHide($config->get("hidden"));
        
        // add additional attributes
        $config->get("attributes") == null ?: $column->addAttributes($config->get("attributes"));
        
        // set column content fit
        $config->get("fitContent") == null ?: $column->setFitContent($config->get("fitContent"));
        
        // set header escaping
        $config->get("headerEscaping") == null ?: $column->setHeaderEscaping($config->get("headerEscaping"));
        
        // set sort options
        $config->get("sort") == null ?: $column->setSort($config->get("sort"));
        
        // set translating on header
        $config->get("translatableHeader") == null ?: $column->setTranslatableHeader($config->get("translatableHeader"));
        
        $this->setupCallbacks($grid, $column, $config);
        $this->addFilters($column, $config);
        
        return $column;
    }

    /**
     * setup callbacks
     * @param DataGrid $grid
     * @param Column $column
     * @param GridColumnsConfig $config
     * @return Column
     */
    protected function setupCallbacks(DataGrid $grid, Column $column, GridColumnsConfig $config)
    {
        $t = $this;
        if ($this->checkCallback(GridBuilder::COLUMN_RENDERER_CALLBACK, $config->get("name"))) {
            if ($this->checkCallback(GridBuilder::COLUMN_CONDITION_CALLBACK, $config->get("name"))) {
                $column->setRenderer(function ($item) use ($config,$t) {
                    return $t->invokeCallback(GridBuilder::COLUMN_RENDERER_CALLBACK, $config->get("name"), $item);
                }, function ($item) use ($config,$t) {
                    return (bool) $t->invokeCallback(GridBuilder::COLUMN_CONDITION_CALLBACK, $config->get("name"), $item, $t->object);
                });
            } else {
                $column->setRenderer(function ($item) use ($config,$t) {
                    return $t->invokeCallback(GridBuilder::COLUMN_RENDERER_CALLBACK, $config->get("name"), $item, $t->object);
                });
            }
        }
        
        if ($this->checkCallback(GridBuilder::SORTABLE_CALLBACK, $config->get("name"))) {
            $column->setSortableCallback(function ($datasource, $sort) use ($config,$t) {
                return $t->invokeCallback(GridBuilder::SORTABLE_CALLBACK, $config->get("name"), $datasource, $sort, $t->object);
            });
        }
        
        if ($this->checkCallback(GridBuilder::COLUMN_CALLBACK, $config->get("name"))) {
            $grid->addColumnCallback($config->name, function ($column, $item) use ($t,$config) {
                $t->invokeCallback(GridBuilder::COLUMN_CALLBACK, $config->get("name"), $column, $item, $t->object);
            });
        }
        
        return $column;
    }

    /**
     * add filters
     * @param Column $column
     * @param GridColumnsConfig $config
     * @return void
     */
    protected function addFilters(Column $column, GridColumnsConfig $config)
    {
        $b = new FilterBuilder($this->object, $column, $config, $this->callbacks);
        $b->build();
        return;
    }
}
