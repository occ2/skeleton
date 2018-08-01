<?php
namespace occ2\FormControl;

use Nette\Reflection\Property;
use Nette\Utils\Strings;

/**
 * FormItemConfig
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class FormItemConfig
{
    const CONFIG_ITEMS=[
        "label",
        "type",
        "cols",
        "maxlength",
        "rows",
        "validator",
        "size",
        "caption",
        "multiple",
        "leftAddon",
        "rightAddon",
        "placeholder",
        "description",
        "required",
        "message",
        "leftIcon",
        "rightIcon"
    ];

    /**
     * @var array
     */
    public $config=[
        "label"=>"",
        "type"=>"text",
        "cols"=>10,
        "maxlength"=>255,
        "rows"=>5,
        "size"=>1,
        "validator"=>[],
        "caption"=>null,
        "multiple"=>null,
        "leftAddon"=>null,
        "rightAddon"=>null,
        "placeholder"=>null,
        "description"=>null,
        "required"=>null,
        "message"=>null,
        "leftIcon"=>null,
        "rightIcon"=>null
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
     * @throws FormBuilderException
     */
    public function __call($name, $arguments)
    {
        if (!Strings::startsWith($name, "get")) {
            throw new FormBuilderException("Error: invalid config method", FormBuilderException::INVALID_METHOD);
        };
        $anchor = Strings::firstLower(str_replace("get", "", $name));
        if (!in_array($anchor, static::CONFIG_ITEMS)) {
            throw new FormBuilderException("Error: invalid config item", FormBuilderException::INVALID_CONFIG_ITEM);
        }
        if (!isset($this->annotations[$anchor])) {
            return isset($this->config[$anchor]) ? $this->config[$anchor] : null;
        } else {
            return $anchor=="validator" ? $this->annotations[$anchor] :  $this->annotations[$anchor][0];
        }
    }

    /**
     * getter
     * @param string $name
     * @return array
     * @throws FormBuilderException
     */
    public function __get($name)
    {
        $anchor = Strings::firstLower($name);
        if (!in_array($anchor, static::CONFIG_ITEMS)) {
            throw new FormBuilderException("Error: invalid config item", FormBuilderException::INVALID_CONFIG_ITEM);
        }
        if (!isset($this->annotations[$anchor])) {
            return isset($this->config[$anchor]) ? $this->config[$anchor] : null;
        } else {
            return $anchor=="validator" ? $this->annotations[$anchor] :  $this->annotations[$anchor][0];
        }
    }
}
