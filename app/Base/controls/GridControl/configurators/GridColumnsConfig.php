<?php
namespace app\Base\controls\GridControl\configurators;

use app\Base\controls\GridControl\exceptions\GridBuilderException;
use Nette\Reflection\Property;
use Nette\Utils\Strings;

/**
 * FormItemConfig
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class GridColumnsConfig
{
    const CONFIG_ITEMS=[
        "type",
        "name",
        "label",
        "dbCol",
        "translate",
        "sortable",
        "href",
        "option",
        "template",
        "filter",
        "replacement",
        "templateEscaping",
        "resetPaginationAfterSorting",
        "align",
        "hidden",
        "datetimeFormat",
        "numberFormat",
        "params",
        "newTab",
        "attributes",
        "fitContent",
        "headerEscaping",
        "translatableHeader",
        "sort",
        "icon",
        "class",
        "title",
        "dataAttributes",
        "parameters",
        "options"
    ];

    /**
     * @var array
     */
    public $config=[
        "type"=>"text",
        "label"=>"",
        "dbCol"=>null,
        "translate"=>null,
        "sortable"=>null,
        "href"=>"this",
        "option"=>null,
        "template"=>null,
        "filter"=>null,
        "replacement"=>null,
        "templateEscaping"=>null,
        "resetPaginationAfterSorting"=>null,
        "align"=>null,
        "hidden"=>null,
        "datetimeFormat"=>null,
        "numberFormat"=>null,
        "params"=>null,
        "newTab"=>null,
        "attributes"=>null,
        "fitContent"=>null,
        "headerEscaping"=>null,
        "translatableHeader"=>null,
        "sort"=>null,
        "icon"=>null,
        "class"=>null,
        "title"=>null,
        "dataAttributes"=>null,
        "parameters"=>null,
        "options"=>null
    ];
    
    public $multipleConfigs=[
        "option",
    ];

    /**
     * @var Nette\Reflection\Property
     */
    protected $property;

    /**
     * @var array
     */
    protected $annotations;

    /**
     * @var string
     */
    public $name;

    /**
     * @param Property $property
     * @return void
     */
    public function __construct(Property $property)
    {
        $this->property = $property;
        $this->annotations = $property->getAnnotations();
        $this->name = $property->getName();
        return;
    }

    /**
     * name getter
     * @return string
     */
    public function getName():string
    {
        return $this->name;
    }

    /**
     * magic universal getter - getSomething()
     * @param string $name
     * @param array $arguments
     * @return array
     * @throws GridBuilderException
     */
    public function __call($name, $arguments)
    {
        if (!Strings::startsWith($name, "get")) {
            throw new GridBuilderException("Error: invalid config", GridBuilderException::INVALID_CONFIG);
        }
        return $this->columns(Strings::firstLower(str_replace("get", "", $name)));
    }

    /**
     * getter
     * @param string $name
     * @return array
     * @throws GridBuilderException
     */
    public function __get($name)
    {
        $anchor = Strings::firstLower($name);
        return $this->columns($anchor);
    }

    /**
     * @param string $anchor
     * @return mixed
     * @throws GridBuilderException
     */
    protected function columns(string $anchor)
    {
        if (!in_array($anchor, static::CONFIG_ITEMS)) {
            throw new GridBuilderException("Error: invalid column config", GridBuilderException::INVALID_COLUMN_CONFIG);
        }
        if (!isset($this->annotations[$anchor])) {
            return isset($this->config[$anchor]) ? $this->config[$anchor] : null;
        } else {
            return in_array($anchor, $this->multipleConfigs) ? $this->annotations[$anchor] : $this->annotations[$anchor][0];
        }
    }
}
