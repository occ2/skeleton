<?php
namespace app\Base\controls\GridControl\builders;

use app\Base\controls\GridControl\traits\TCallbacks;
use app\Base\controls\GridControl\builders\IFilterGridBuilder;
use app\Base\controls\GridControl\GridControl;
use app\Base\controls\GridControl\configurators\GridColumnsConfig;
use app\Base\controls\GridControl\exceptions\GridBuilderException;
use app\Base\controls\GridControl\builders\GridBuilder;
use Ublaboo\DataGrid\Column\Column;
use Ublaboo\DataGrid\Filter\Filter;
use Ublaboo\DataGrid\Filter\FilterRange;
use Ublaboo\DataGrid\Filter\IFilterDate;
use Ublaboo\DataGrid\Filter\FilterSelect;
use Ublaboo\DataGrid\Filter\FilterText;
use Ublaboo\DataGrid\Filter\FilterMultiSelect;
use Ublaboo\DataGrid\Filter\FilterDate;
use Ublaboo\DataGrid\Filter\FilterDateRange;
use Nette\Utils\ArrayHash;

/**
 * FilterBuilder
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class FilterBuilder implements IFilterGridBuilder
{
    use TCallbacks;

    /**
     * @var Column
     */
    protected $column;

    /**
     * @var GridColumnsConfig
     */
    protected $config;

    /**
     * @var GridControl
     */
    protected $object;

    /**
     * @param GridControl $object
     * @param Column $column
     * @param GridColumnsConfig $config
     * @param array $callbacks
     * @return void
     */
    public function __construct(GridControl $object, Column $column, GridColumnsConfig $config, array $callbacks)
    {
        $this->object = $object;
        $this->column = $column;
        $this->config = $config;
        $this->callbacks = $callbacks;
        return;
    }

    /**
     * build filters
     * @return void
     */
    public function build()
    {
        return $this->addFilters($this->column, $this->config);
    }

    /**
     * add filters
     * @param Column $column
     * @param GridColumnsConfig $config
     * @return void
     * @throws GridBuilderException
     */
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

    /**
     * setup filter
     * @param Filter $filter
     * @param ArrayHash $config
     * @param string $name
     * @return Filter
     */
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

    /**
     * setup filter select
     * @param FilterSelect $filter
     * @param ArrayHash $config
     * @param string $name
     * @return FilterSelect
     */
    protected function setupFilterSelect(FilterSelect $filter, ArrayHash $config, string $name)
    {
        !isset($config->prompt) ?: $filter->setPrompt($config->prompt);
        !isset($config->translateOptions) ?: $filter->setTranslateOptions($config->translateOptions);
        return $filter;
    }

    /**
     * setup filter date and daterange
     * @param IFilterDate $filter
     * @param ArrayHash $config
     * @param string $name
     * @return IFilterDate
     */
    protected function setupFilterDate(IFilterDate $filter, ArrayHash $config, string $name)
    {
        $php = !isset($config->phpFormat) ? $this->object->_defaultDatetimeFormat["php"] : $config->phpFormat;
        $js = !isset($config->jsFormat) ? $this->object->_defaultDatetimeFormat["js"] : $config->jsFormat;
        $filter->setFormat($php, $js);
        !isset($config->size) ? $filter->addAttribute("size", 8) : $filter->addAttribute("size", $config->size);
        return $filter;
    }

    /**
     * setup filter range
     * @param FilterRange $filter
     * @param ArrayHash $config
     * @param string $name
     * @return FilterRange
     */
    protected function setupFilterRange(FilterRange $filter, ArrayHash $config, string $name)
    {
        !isset($config->placeholders) ? : $filter->setPlaceholder(explode(",", $config->placeholders));
        return $filter;
    }

    /**
     * add text filter
     * @param Column $column
     * @param ArrayHash $config
     * @param string $name
     * @return FilterText
     */
    protected function addFilterText(Column $column, ArrayHash $config, string $name): FilterText
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

    /**
     * add select filter
     * @param Column $column
     * @param ArrayHash $config
     * @param string $name
     * @return FilterSelect
     */
    protected function addFilterSelect(Column $column, ArrayHash $config, string $name): FilterSelect
    {
        $col = isset($config->column) ? $config->column : null;
        $options = $this->invokeCallback(GridBuilder::LOAD_OPTIONS_CALLBACK, $name, $this->object);
        $filter = $column->setFilterSelect($options, $col);
        $filter->setTemplate(!isset($config->template) ? $this->object->_gridFilterTemplatesPath["select"]: $config->template);
        $this->setupFilter($filter, $config, $name);
        $this->setupFilterSelect($filter, $config, $name);
        return $filter;
    }

    /**
     * add multiselect filter
     * @param Column $column
     * @param ArrayHash $config
     * @param string $name
     * @return FilterMultiSelect
     */
    protected function addFilterMultiSelect(Column $column, ArrayHash $config, string $name): FilterMultiSelect
    {
        $col = isset($config->column) ? $config->column : null;
        $options = $this->invokeCallback(GridBuilder::LOAD_OPTIONS_CALLBACK, $name);
        $filter = $column->setFilterMultiSelect($options, $col);
        $filter->setTemplate(!isset($config->template) ? $this->object->_gridFilterTemplatesPath["multiselect"]: $config->template);
        $this->setupFilter($filter, $config, $name);
        $this->setupFilterSelect($filter, $config, $name);
        return $filter;
    }

    /**
     * add date filter
     * @param Column $column
     * @param ArrayHash $config
     * @param string $name
     * @return FilterDate
     */
    protected function addFilterDate(Column $column, ArrayHash $config, string $name): FilterDate
    {
        $col = isset($config->column) ? $config->column : null;
        $filter = $column->setFilterDate($col);
        $filter->setTemplate(!isset($config->template) ? $this->object->_gridFilterTemplatesPath["date"]: $config->template);
        $this->setupFilter($filter, $config, $name);
        $this->setupFilterDate($filter, $config, $name);
        return $filter;
    }

    /**
     * add range filter
     * @param Column $column
     * @param ArrayHash $config
     * @param string $name
     * @return FilterRange
     */
    protected function addFilterRange(Column $column, ArrayHash $config, string $name): FilterRange
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

    /**
     * add daterange filter
     * @param Column $column
     * @param ArrayHash $config
     * @param string $name
     * @return FilterDateRange
     */
    protected function addFilterDateRange(Column $column, ArrayHash $config, string $name): FilterDateRange
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
