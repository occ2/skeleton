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
use app\Base\controls\GridControl\builders\IAdditionalGridBuilder;
use app\Base\controls\GridControl\GridControl;
use app\Base\controls\GridControl\configurators\GridConfig;
use app\Base\controls\GridControl\builders\GridBuilder;
use app\Base\controls\GridControl\exceptions\GridBuilderException;
use app\Base\controls\GridControl\DataGrid;
use Ublaboo\DataGrid\InlineEdit\InlineEdit;
use Nette\Utils\ArrayHash;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Application\UI\Form;

/**
 * InlineActionsBuilder
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class InlineActionsBuilder implements IAdditionalGridBuilder
{
    use TCallbacks;

    public static $formControls=[
        "text"=>"addItemText",
        "textarea"=>"addItemTextarea",
        "select"=>"addItemSelect",
        "multiselect"=>"addItemMultiSelect",
        "checkbox"=>"addItemCheckbox",
        "checkboxlist"=>"addItemCheckboxList",
        "radiolist"=>"addItemRadioList"
    ];

    /**
     * @var GridControl
     */
    protected $object;

    /**
     * @var DataGrid
     */
    protected $grid;

    /**
     * @var GridConfig
     */
    protected $configurator;

    /**
     * @var array
     */
    protected $callbacks;

    /**
     * @param GridControl $object
     * @param DataGrid $grid
     * @param GridConfig $configurator
     * @param array $callbacks
     * @return void
     */
    public function __construct(GridControl $object, DataGrid $grid, GridConfig $configurator, array $callbacks)
    {
        $this->object = $object;
        $this->grid = $grid;
        $this->configurator = $configurator;
        $this->callbacks = $callbacks;
        return;
    }

    /**
     * build
     * @return void
     */
    public function build()
    {
        if($this->checkCallback(GridBuilder::ALLOW_INLINE_ADD_CALLBACK)==true){
            if($this->invokeCallback(GridBuilder::ALLOW_INLINE_ADD_CALLBACK)==true){
                $this->setupInlineAdd($this->grid);
            }
        } else{
            $this->setupInlineAdd($this->grid);
        }
       
        $this->setupInlineEdit($this->grid);
        return;
    }

    /**
     * @param DataGrid $grid
     * @return void
     * @throws GridBuilderException
     */
    protected function setupInlineAdd(DataGrid $grid)
    {
        $config = $this->configurator->get("inlineAdd");
        $t = $this;
        if($config!=null){
            $inline = $grid->addInlineAdd();
            if($this->checkCallback(GridBuilder::INLINE_FORM_CALLBACK)){
                $inline->onControlAdd[]=function(Container $form) use ($t) {
                    $this->invokeCallback(
                        GridBuilder::INLINE_FORM_CALLBACK,
                        null,
                        $form,
                        $t->object
                    );
                };
            } else {
                $inline->onControlAdd[]=function(Container $form) use ($t) {
                    $t->setupInlineForm($form);
                };
            }
            if($this->checkCallback(GridBuilder::INLINE_FORM_ADD_SUBMIT_CALLBACK)){
                $inline->onSubmit[] = function(ArrayHash $values) use ($t) {
                    $this->invokeCallback(GridBuilder::INLINE_FORM_ADD_SUBMIT_CALLBACK, null,$values,$t->object);
                };
            } elseif (isset($config->event)) {
                $inline->onSubmit[] = function(ArrayHash $values) use ($t,$grid,$config) {
                    $eventName = $config->event;
                    $data = $t->object->getGridRowEventFactory()->create(null,$values,$grid,$t->object,$eventName);
                    $t->object->on($eventName, $data);
                    return;
                };
            } else {
                throw new GridBuilderException("ERROR: Invalid or undefined inline add submit callback or event",GridBuilderException::INVALID_INLINE_ADD_SUBMIT_CALLBACK);
            }
            $this->setupCustomRedraw($inline,$grid);
            !isset($config->topPosition) ?: $inline->setPositionTop($config->topPosition);
            !isset($config->icon) ?: $inline->setIcon($config->icon);
            !isset($config->class) ? $inline->setClass("btn btn-xs btn-success") : $inline->setClass($config->class);
            !isset($config->text) ?: $inline->setText($this->object->_($config->text));
            !isset($config->title) ?: $inline->setTitle($this->object->_($config->title));
        }
        return;
    }

    /**
     * @param DataGrid $grid
     * @return void
     * @throws GridBuilderException
     */
    protected function setupInlineEdit(DataGrid $grid)
    {
        $config = $this->configurator->get("inlineEdit");
        if($config!=null){
            $t = $this;
            $inline = $grid->addInlineEdit(
                isset($config->primaryWhereColumn) ? $config->primaryWhereColumn : null
            );
            if($this->checkCallback(GridBuilder::INLINE_FORM_CALLBACK)){
                $inline->onControlAdd[]=function(Container $form) use ($t) {
                    $this->invokeCallback(
                        GridBuilder::INLINE_FORM_CALLBACK,
                        null,
                        $form,
                        $t->object
                    );
                };
            } else {
                $inline->onControlAdd[]=function(Container $form) use ($t) {
                    $t->setupInlineForm($form);
                };
            }

            if(!$this->checkCallback(GridBuilder::INLINE_FORM_FILL_CALLBACK)){
                throw new GridBuilderException("ERROR: Invalid or undefined inline form fill callback", GridBuilderException::INVALID_INLINE_FORM_FILL_CALLBACK);
            } else {
                $inline->onSetDefaults[] = function($container, $item) use ($t) {
                    $this->invokeCallback(GridBuilder::INLINE_FORM_FILL_CALLBACK, null, $container,$item,$t->object);
                };
            }

            if(!$this->checkCallback(GridBuilder::INLINE_FORM_EDIT_SUBMIT_CALLBACK)){
                $inline->onSubmit[] = function($id, ArrayHash $values) use ($t) {
                    $this->invokeCallback(GridBuilder::INLINE_FORM_EDIT_SUBMIT_CALLBACK, null,$id,$values,$t->object);
                };
            } elseif ($config->event) {
                $inline->onSubmit[] = function($id, ArrayHash $values) use ($t,$grid,$config) {
                    $eventName = $config->event;
                    $data = $t->object->getGridRowEventFactory()->create($id,$values,$grid,$t->object,$eventName);
                    $t->object->on($eventName, $data);
                    return;
                };
            } else {
                throw new GridBuilderException("ERROR: Invalid or undefined inline edit submit callback",GridBuilderException::INVALID_INLINE_EDIT_SUBMIT_CALLBACK);
            }
            $this->setupCustomRedraw($inline,$grid);
            !isset($config->icon) ? $inline->setIcon("edit") : $inline->setIcon($config->icon);
            !isset($config->class) ? $inline->setClass("btn btn-xs ajax btn-dark") : $inline->setClass($config->class);
            !isset($config->text) ?: $inline->setText($this->object->_($config->text));
            !isset($config->title) ?: $inline->setTitle($this->object->_($config->title));
            !isset($config->showNonEditingColumns) ?: $inline->setShowNonEditingColumns($config->showNonEditingColumns);
        }
        return;
    }

    /**
     * @param InlineEdit $inline
     * @param DataGrid $grid
     * @return void
     */
    protected function setupCustomRedraw(InlineEdit $inline, DataGrid $grid)
    {
        $t = $this;
        if($this->checkCallback(GridBuilder::INLINE_CUSTOM_REDRAW_CALLBACK)){
            $inline->onCustomRedraw[] = function() use ($t) {
                $t->invokeCallback(GridBuilder::INLINE_CUSTOM_REDRAW_CALLBACK, null,$t->object["grid"],$t->object);
            };
        } else {
            $inline->onCustomRedraw[] = function() use ($grid) {
                $grid->redrawControl();
            };
        }
        return;
    }

    /**
     * @param Container $container
     * @return void
     * @throws GridBuilderException
     */
    protected function setupInlineForm(Container $container)
    {
        $itemsConfig = $this->configurator->get("inlineFormControl",true);
        $itemsValidators = [];
        $vv = $this->configurator->get("inlineFormValidator",true)==null ? [] : $this->configurator->get("inlineFormValidator",true);
        foreach ($vv as $v){
            if(!isset($v->name)){
                throw new GridBuilderException("ERROR: Invalid inline form validator name", GridBuilderException::INVALID_INLINE_FORM_VALIDATOR_NAME);
            }
            $itemsValidators[$v->name] = $v;
        }
        foreach($itemsConfig as $itemConfig){
            if(!isset($itemConfig->name)){
                throw new GridBuilderException("ERROR: Invalid form control name",GridBuilderException::INVALID_INLINE_FORM_CONTROL_NAME);
            }
            $this->addFormItem(
                $container,
                $itemConfig,
                array_key_exists($itemConfig->name, $itemsValidators) ? $itemsValidators[$itemConfig->name] : null
            );
        }
        return;
    }

    /**
     * @param Container $container
     * @param object $config
     * @param ArrayHash $validator
     * @return BaseControl
     * @throws GridBuilderException
     */
    protected function addFormItem(Container $container,$config,ArrayHash $validator=null)
    {
        if(!isset($config->type) && !array_key_exists($config->type, self::$formControls)){
            throw new GridBuilderException("ERROR: Invalid inline form control type",GridBuilderException::INVALID_INLINE_FORM_CONTROL_TYPE);
        }

        $method = self::$formControls[$config->type];
        return $this->$method($container,$config,$validator);
    }

    /**
     * @param BaseControl $control
     * @param ArrayHash $config
     * @return void
     */
    protected function setupItem(BaseControl $control,ArrayHash $config)
    {
        if(isset($config->required) && $config->required == true){
            $control->setRequired(true);
        } else {
            $control->setRequired(false);
        }
        if(isset($config->readonly) && $config->readonly==true){
            $control->setAttribute('readonly');
        }
        return;
    }

    /**
     * @param BaseControl $control
     * @param ArrayHash $config
     * @return void
     */
    protected function setupValidators(BaseControl $control,ArrayHash $config = null)
    {
        if($config!=null && $config instanceof ArrayHash){
            if ($config->type==":equal") {
                $control->addRule(Form::EQUAL, isset($config->message) ? $config->message : null, $this->{$config->value});
            } else {
                $control->addRule(
                    $config->type,
                    isset($config->message) ? $config->message : null,
                    isset($config->value) ? !is_array($config->value) ? explode(";", $config->value) : $config->value : null
                );
            }
        }
        return;
    }

    /**
     * @param Container $container
     * @param ArrayHash $config
     * @param ArrayHash $validator
     * @return void
     */
    protected function addItemText(Container $container, ArrayHash $config,ArrayHash $validator=null)
    {
        $control = $container->addText(
            $config->name,
            '',
            isset($config->cols) ? $config->cols : null,
            isset($config->maxLength) ? $config->maxLength : null
        );
        $this->setupItem($control, $config);
        $this->setupValidators($control,$validator);
        return;
    }

    /**
     * @param Container $container
     * @param ArrayHash $config
     * @param ArrayHash $validator
     * @return void
     */
    protected function addItemTextarea(Container $container, ArrayHash $config,ArrayHash $validator=null)
    {
        $control = $container->addTextArea(
            $config->name,
            '',
            isset($config->cols) ? $config->cols : null,
            isset($config->rows) ? $config->rows : null
         );
        $this->setupItem($control, $config);
        $this->setupValidators($control,$validator);
        return;
    }

    /**
     * @param Container $container
     * @param ArrayHash $config
     * @param ArrayHash $validator
     * @return void
     * @throws GridBuilderException
     */
    protected function addItemSelect(Container $container, ArrayHash $config,ArrayHash $validator=null)
    {
        if(!$this->checkCallback(GridBuilder::INLINE_LOAD_OPTIONS_CALLBACK, $config->name)){
            throw new GridBuilderException("ERROR: Load options callback not set", GridBuilderException::INVALID_LOAD_OPTIONS_CALLBACK);
        } else {
            $options = $this->invokeCallback(GridBuilder::INLINE_LOAD_OPTIONS_CALLBACK, $config->name,$this->object);
        }
        $control = $container->addSelect(
            $config->name,
            '',
            $options,
            isset($config->size) ? $config->size :null
        );
        $this->setupValidators($control,$validator);
        return;
    }

    /**
     * @param Container $container
     * @param ArrayHash $config
     * @param ArrayHash $validator
     * @return void
     * @throws GridBuilderException
     */
    protected function addItemMultiSelect(Container $container, ArrayHash $config,ArrayHash $validator=null)
    {
        if(!$this->checkCallback(GridBuilder::INLINE_LOAD_OPTIONS_CALLBACK, $config->name)){
            throw new GridBuilderException("ERROR: Load options callback not set", GridBuilderException::INVALID_LOAD_OPTIONS_CALLBACK);
        } else {
            $options = $this->invokeCallback(GridBuilder::INLINE_LOAD_OPTIONS_CALLBACK, $config->name,$this->object);
        }
        $control = $container->addMultiSelect(
            $config->name,
            '',
            $options,
            isset($config->size) ? $config->size :null
        );
        $this->setupValidators($control,$validator);
        return;
    }

    /**
     * @param Container $container
     * @param ArrayHash $config
     * @param ArrayHash $validator
     * @return void
     */
    protected function addItemCheckbox(Container $container, ArrayHash $config,ArrayHash $validator=null)
    {
        $container->addCheckbox(
            $config->name,
            isset($config->caption) ? $config->caption : null
            );
        return;
    }

    /**
     * @param Container $container
     * @param ArrayHash $config
     * @param ArrayHash $validator
     * @return void
     * @throws GridBuilderException
     */
    protected function addItemCheckboxList(Container $container, ArrayHash $config,ArrayHash $validator=null)
    {
        if(!$this->checkCallback(GridBuilder::INLINE_LOAD_OPTIONS_CALLBACK, $config->name)){
            throw new GridBuilderException("ERROR: Load options callback not set", GridBuilderException::INVALID_LOAD_OPTIONS_CALLBACK);
        } else {
            $options = $this->invokeCallback(GridBuilder::INLINE_LOAD_OPTIONS_CALLBACK, $config->name,$this->object);
        }
        $control = $container->addCheckboxList($config->name, '', $options);
        $this->setupValidators($control,$validator);
        return;
    }

    /**
     * @param Container $container
     * @param ArrayHash $config
     * @param ArrayHash $validator
     * @return void
     * @throws GridBuilderException
     */
    protected function addItemRadioList(Container $container, ArrayHash $config,ArrayHash $validator=null)
    {
        if(!$this->checkCallback(GridBuilder::INLINE_LOAD_OPTIONS_CALLBACK, $config->name)){
            throw new GridBuilderException("ERROR: Load options callback not set", GridBuilderException::INVALID_LOAD_OPTIONS_CALLBACK);
        } else {
            $options = $this->invokeCallback(GridBuilder::INLINE_LOAD_OPTIONS_CALLBACK, $config->name,$this->object);
        }
        $control = $container->addRadioList($config->name, '', $options);
        $this->setupValidators($control,$validator);
        return;
    }
}
