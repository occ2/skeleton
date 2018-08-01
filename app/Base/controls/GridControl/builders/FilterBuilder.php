<?php
namespace occ2\GridControl;

use Ublaboo\DataGrid\Column\Column;
use Ublaboo\DataGrid\Filter\Filter;
use Ublaboo\DataGrid\Filter\FilterRange;
use Ublaboo\DataGrid\Filter\IFilterDate;
use Ublaboo\DataGrid\Filter\FilterSelect;
use Nette\Utils\ArrayHash;

/**
 * FilteBuilder
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class FilterBuilder
{
    use TCallbacks;
    
    protected $column;
    
    protected $config;
    
    protected $object;
    
    public function __construct(GridControl $object, Column $column, GridColumnsConfig $config, $callbacks)
    {
        $this->object = $object;
        $this->column = $column;
        $this->config = $config;
        $this->callbacks = $callbacks;
        return;
    }
    
    public function build()
    {
        return $this->addFilters($this->column, $this->config);
    }
    
    protected function addFilters(Column $column, GridColumnsConfig $config)
    {
        if ($config->filter!=null && isset($config->filter["type"])) {
            if (!array_key_exists($config->filter["type"], GridBuilder::FILTER_TYPES)) {
                throw new GridBuilderException("ERROR: Invalid filter type", GridBuilderException::INVALID_FILTER_TYPE);
            }
            $filterAdder = GridBuilder::FILTER_TYPES[$config->filter["type"]];
            $this->$filterAdder($column, $config->filter, $config->name);
        }
        return;
    }
    
    protected function setupFilter(Filter $filter, ArrayHash $config, string $name)
    {
        $t = $this;
        !isset($config->value) ?: $filter->setValue($config->value);
        !isset($config->placeholder) ?: $filter->setPlaceholder($config->placeholder);
        if (isset($config->attribute)) {
            $attr = explode("=>", $config->attribute);
            $filter->setAttribute($attr[0], $attr[1]);
        }
        
        if ($this->checkCallback(GridBuilder::FILTER_CONDITION_CALLBACK, $name)) {
            $filter->setCondition(function ($dataSource, $value) use ($t,$name) {
                return $this->invokeCallback(GridBuilder::FILTER_CONDITION_CALLBACK, $name, $dataSource, $value, $t->object);
            });
        }
        return $filter;
    }
    
    protected function setupFilterSelect(FilterSelect $filter, ArrayHash $config, string $name)
    {
        !isset($config->prompt) ?: $filter->setPrompt($config->prompt);
        !isset($config->translateOptions) ?: $filter->setTranslateOptions($config->translateOptions);
        return $filter;
    }
    
    protected function setupFilterDate(IFilterDate $filter, ArrayHash $config, string $name)
    {
        $php = !isset($config->phpFormat) ? $this->object->_defaultDatetimeFormat["php"] : $config->phpFormat;
        $js = !isset($config->jsFormat) ? $this->object->_defaultDatetimeFormat["js"] : $config->jsFormat;
        $filter->setFormat($php, $js);
        !isset($config->size) ? $filter->addAttribute("size", 8) : $filter->addAttribute("size", $config->size);
        return $filter;
    }
    
    protected function setupFilterRange(FilterRange $filter, ArrayHash $config, string $name)
    {
        !isset($config->placeholders) ? : $filter->setPlaceholder(explode(",", $config->placeholders));
        return $filter;
    }
    
    protected function addFilterText(Column $column, ArrayHash $config, string $name)
    {
        $columns = isset($config->columns) ? explode(",", $config->columns): null;
        $filter = $column->setFilterText($columns);
        !isset($config->exactSearch) ?: $filter->setExactSearch($config->exactSearch);
        !isset($config->splitWordsSearch) ?: $filter->setSplitWordsSearch($config->splitWordsSearch);
        $filter->setTemplate(!isset($config->template) ? $this->object->_gridFilterTemplatesPath["text"]: $config->template);
        $this->setupFilter($filter, $config, $name);
        !isset($config->size) ? $filter->addAttribute("size", 16) : $filter->addAttribute("size", $config->size);
        return $filter;
    }
    
    protected function addFilterSelect(Column $column, ArrayHash $config, string $name)
    {
        $col = isset($config->column) ? $config->column : null;
        $options = $this->invokeCallback(GridBuilder::LOAD_OPTIONS_CALLBACK, $name, $this->object);
        $filter = $column->setFilterSelect($options, $col);
        $filter->setTemplate(!isset($config->template) ? $this->object->_gridFilterTemplatesPath["select"]: $config->template);
        $this->setupFilter($filter, $config, $name);
        $this->setupFilterSelect($filter, $config, $name);
        return $filter;
    }
    
    protected function addFilterMultiSelect(Column $column, ArrayHash $config, string $name)
    {
        $col = isset($config->column) ? $config->column : null;
        $options = $this->invokeCallback(GridBuilder::LOAD_OPTIONS_CALLBACK, $name);
        $filter = $column->setFilterMultiSelect($options, $col);
        $filter->setTemplate(!isset($config->template) ? $this->object->_gridFilterTemplatesPath["multiselect"]: $config->template);
        $this->setupFilter($filter, $config, $name);
        $this->setupFilterSelect($filter, $config, $name);
        return $filter;
    }
    
    protected function addFilterDate(Column $column, ArrayHash $config, string $name)
    {
        $col = isset($config->column) ? $config->column : null;
        $filter = $column->setFilterDate($col);
        $filter->setTemplate(!isset($config->template) ? $this->object->_gridFilterTemplatesPath["date"]: $config->template);
        $this->setupFilter($filter, $config, $name);
        $this->setupFilterDate($filter, $config, $name);
        return $filter;
    }
    
    protected function addFilterRange(Column $column, ArrayHash $config, string $name)
    {
        $col = isset($config->column) ? $config->column : null;
        $filter = $column->setFilterRange(
                $col,
                isset($config->nameSecond) ? $config->nameSecond : "-"
        );
        $filter->setTemplate(!isset($config->template) ? $this->object->_gridFilterTemplatesPath["range"]: $config->template);
        $this->setupFilter($filter, $config, $name);
        $this->setupFilterRange($filter, $config, $name);
        !isset($config->size) ? $filter->addAttribute("size", 6) : $filter->addAttribute("size", $config->size);
        return $filter;
    }
    
    protected function addFilterDateRange(Column $column, ArrayHash $config, string $name)
    {
        $col = isset($config->column) ? $config->column : null;
        $filter = $column->setFilterDateRange(
                $col,
                isset($config->nameSecond) ? $config->nameSecond : "-"
        );
        $filter->setTemplate(!isset($config->template) ? $this->object->_gridFilterTemplatesPath["daterange"]: $config->template);
        $this->setupFilter($filter, $config, $name);
        $this->setupFilterRange($filter, $config, $name);
        $this->setupFilterDate($filter, $config, $name);
        return $filter;
    }
}
