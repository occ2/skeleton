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

namespace app\Base\controls\FormControl\builders;

use app\Base\controls\FormControl\exceptions\FormBuilderException;
use app\Base\controls\FormControl\configurators\FormItemConfig;
use app\Base\controls\FormControl\FormControl;
use Nette\Application\UI\Form;
use Nette\Reflection\ClassType;
use Nette\Reflection\Property;
use Nette\Utils\Strings;
use Nette\Utils\Html;
use Nette\Forms\Controls\TextBase;
use Nette\Forms\Controls\BaseControl;

/**
 * EntityFormBuilder
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class FormBuilder implements IFormBuilder
{
    const COLUMN_TYPES=[
        "hidden"=>"addHidden",
        "text"=>"addText",
        "email"=>"addEmail",
        "number"=>"addNumber",
        "password"=>"addPassword",
        "textarea"=>"addTextarea",
        "select"=>"addSelect",
        "multiselect"=>"addMultiselect",
        "checkbox"=>"addCheckbox",
        "checkboxlist"=>"addCheckboxList",
        "radiolist"=>"addRadioList",
        "upload"=>"addUpload",
        "multiUpload"=>"addMultiUpload",
        "submit"=>"addSubmit",
        "recaptcha"=>"addReCaptcha"
    ];

    /**
     * @var ClassType
     */
    protected $classType;

    /**
     * @var FormControl
     */
    public $object;

    /**
     * @var array
     */
    protected $metadata;

    /**
     * @var array
     */
    protected $validators;

    /**
     * @var array | null
     */
    protected $loadOptionsCallback;

    /**
     * @var \Nette\Localization\ITranslator
     */
    protected $translator=null;

    /**
     * magic metadata getter
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return isset($this->object->{$name}) ? $this->object->{$name} : false;
    }

    /**
     * translator setter
     * @param \Nette\Localization\ITranslator $translator
     * @return $this
     */
    public function setTranslator(\Nette\Localization\ITranslator $translator)
    {
        $this->translator = $translator;
        return $this;
    }

    /**
     * text
     * @param string $text
     * @return string
     * @deprecated since version 1.1.0
     */
    protected function text(string $text)
    {
        return $this->translator instanceof \Nette\Localization\ITranslator ?  $this->translator->translate($text): $text;
    }

    /**
     * simplifier of translation
     * @param string $text
     * @return string
     */
    public function _(string $text)
    {
        return $this->translator instanceof \Nette\Localization\ITranslator ?  $this->translator->translate($text): $text;
    }

    /**
     * set form control object
     * @param FormControl $object
     */
    public function setObject(FormControl $object)
    {
        $this->object = $object;
        $this->classType = ClassType::from($object);
        return $this;
    }

    /**
     * set load options callbacks
     * @param array | null $optionsCallbacks
     * @return $this
     */
    public function setOptionsCallbacks(array $optionsCallbacks=null)
    {
        $this->loadOptionsCallback = $optionsCallbacks;
        return $this;
    }

    /**
     * invoke callback
     * @param callable $callback
     * @param mixed $params
     * @return mixed
     * @throws FormBuilderException
     */
    protected function invokeCallback(callable $callback, $params=null)
    {
        return $callback($params);
    }

    /**
     * build form from entity
     * @param Form $form
     * @return Form
     */
    public function build(Form $form):Form
    {
        foreach ($this->classType->getProperties() as $property) {
            $this->add($form, $property);
        }
        
        return $form;
    }

    /**
     * @param Form $form
     * @param Property $property
     * @return void
     */
    protected function add(Form $form, Property $property)
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
            $config = new FormItemConfig($property,$this->object);
            $this->object->{$config->name} = $this->{self::COLUMN_TYPES[$config->type]}(
                    $form,
                    $config
           );
            return;
        }
    }

    /**
     * @param TextBase $element
     * @param FormItemConfig $config
     * @return TextBase
     */
    protected function setupText(TextBase $element, FormItemConfig $config):TextBase
    {
        $config->leftIcon==null ?: $element->setOption("left-addon", Html::el("i")->setAttribute("class", FormControl::$_iconPrefix . $config->leftIcon));
        $config->rightIcon==null?: $element->setOption("right-addon", Html::el("i")->setAttribute("class", FormControl::$_iconPrefix . $config->rightIcon));
        $config->leftAddon==null ?: $element->setOption("left-addon", $this->text($config->leftAddon));
        $config->rightAddon==null ?: $element->setOption("right-addon", $this->text($config->rightAddon));
        $config->placeholder==null ?: $element->setAttribute('placeholder', $this->text($config->placeholder));
        $config->description==null ?: $element->setOption("description", $config->description);
        return $element;
    }

    /**
     * add text element
     */
    protected function addText(Form $form, FormItemConfig $config): BaseControl
    {
        $element = $form->addText(
            $config->name,
                                  $config->label,
                                  $config->cols,
                                  $config->maxlength
                    );
        $this->setupText($element, $config);
        $validators = $config->validator;
        empty($validators) ?: $this->setValidators($element, $validators);
        return $element;
    }

    /**
     * add email element
     */
    protected function addEmail(Form $form, FormItemConfig $config): BaseControl
    {
        $element = $form->addEmail(
            $config->name,
                                   $config->label
                    );
        $validators = $config->validator;
        empty($validators) ?: $this->setValidators($element, $validators);
        $this->setupText($element, $config);
        return $element;
    }

    /**
     * add integer element
     */
    protected function addNumber(Form $form, FormItemConfig $config): BaseControl
    {
        $element = $form->addInteger(
            $config->name,
                                     $config->label
                    );
        $validators = $config->validator;
        empty($validators) ?: $this->setValidators($element, $validators);
        $this->setupText($element, $config);
        return $element;
    }

    /**
     * add password element
     */
    protected function addPassword(Form $form, FormItemConfig $config): BaseControl
    {
        $element = $form->addPassword(
            $config->name,
                                      $config->label,
                                      $config->cols,
                                      $config->maxlength
                    );
        $validators = $config->validator;
        empty($validators) ?: $this->setValidators($element, $validators);
        $this->setupText($element, $config);
        return $element;
    }

    /**
     * add textarea element
     */
    protected function addTextarea(Form $form, FormItemConfig $config): BaseControl
    {
        $element = $form->addTextArea(
            $config->name,
                                      $config->label,
                                      $config->cols,
                                      $config->rows
                    );
        $validators = $config->validator;
        empty($validators) ?: $this->setValidators($element, $validators);
        $config->description==null ?: $element->setOption("description", $this->text($config->description));
        return $element;
    }

    /**
     * add select element
     */
    protected function addSelect(Form $form, FormItemConfig $config): BaseControl
    {
        $items = (array) $this->invokeCallback($this->loadOptionsCallback[$config->name]);
        $element = $form->addSelect(
            $config->name,
                                    $config->label,
                                    $items,
                                    $config->size
                    );
        $validators = $config->validator;
        empty($validators) ?: $this->setValidators($element, $validators);
        $config->description==null ?: $element->setOption("description", $this->text($config->description));
        return $element;
    }

    /**
     * add multiselect element
     */
    protected function addMultiselect(Form $form, FormItemConfig $config): BaseControl
    {
        $items = (array) $this->invokeCallback($this->loadOptionsCallback[$config->name]);
        $element = $form->addMultiSelect(
            $config->name,
                                         $config->label,
                                         $items,
                                         $config->size
                    );
        $validators = $config->validator;
        empty($validators) ?: $this->setValidators($element, $validators);
        $config->description==null ?: $element->setOption("description", $this->text($config->description));
        return $element;
    }

    /**
     * add checkbox element
     */
    protected function addCheckbox(Form $form, FormItemConfig $config): BaseControl
    {
        $element = $form->addCheckbox(
            $config->name,
                                      $config->caption
                    );
        $validators = $config->validator;
        empty($validators) ?: $this->setValidators($element, $validators);
        $config->description==null ?: $element->setOption("description", $this->text($config->description));
        return $element;
    }

    /**
     * add checkboxlist element
     */
    protected function addCheckboxList(Form $form, FormItemConfig $config): BaseControl
    {
        $items = (array) $this->invokeCallback($this->loadOptionsCallback[$config->name]);
        $element = $form->addCheckboxList(
            $config->name,
                                          $config->label,
                                          $items
                    );
        $validators = $config->validator;
        empty($validators) ?: $this->setValidators($element, $validators);
        $config->description==null ?: $element->setOption("description", $this->text($config->description));
        return $element;
    }

    /**
     * add radiolist element
     */
    protected function addRadioList(Form $form, FormItemConfig $config): BaseControl
    {
        $items = (array) $this->invokeCallback($this->loadOptionsCallback[$config->name]);
        $element = $form->addRadioList(
            $config->name,
                                       $config->label,
                                       $items
                    );
        $validators = $config->validator;
        empty($validators) ?: $this->setValidators($element, $validators);
        $config->description==null ?: $element->setOption("description", $this->text($config->description));
        return $element;
    }

    /**
     * add upload element
     */
    protected function addUpload(Form $form, FormItemConfig $config): BaseControl
    {
        $element = $form->addUpload(
            $config->name,
                                    $config->label,
                                    $config->multiple
                    );
        $validators = $config->validator;
        empty($validators) ?: $this->setValidators($element, $validators);
        $config->description==null ?: $element->setOption("description", $this->text($config->description));
        return $element;
    }

    /**
     * add multiupload element
     */
    protected function addMultiUpload(Form $form, FormItemConfig $config): BaseControl
    {
        $element = $form->addMultiUpload(
            $config->name,
                                         $config->label
                );
        $validators = $config->validator;
        empty($validators) ?: $this->setValidators($element, $validators);
        $config->description==null ?: $element->setOption("description", $this->text($config->description));
        return $element;
    }

    /**
     * add hidden lement
     */
    protected function addHidden(Form $form, FormItemConfig $config): BaseControl
    {
        return $form->addHidden($config->name);
    }

    /**
     * add submit
     */
    protected function addSubmit(Form $form, FormItemConfig $config): BaseControl
    {
        return $form->addSubmit($config->name, $config->label);
    }

    /**
     * add recaptcha control
     * @param Form $form
     * @param FormItemConfig $config
     * @return BaseControl
     */
    protected function addRecaptcha(Form $form, FormItemConfig $config): BaseControl
    {
        if(method_exists($form, "addReCaptcha")){
            $element = $form->addReCaptcha(
                    $config->name,
                    $config->label == null ? "": $config->label,
                    $config->required == null ? true : $config->required,
                    $config->message == null ? "Are you bot?" : $config->message
            );
            return $element;
        }
    }

    /**
     * set validators
     * @param BaseControl $element
     * @param array $validators
     * @return void
     */
    protected function setValidators(BaseControl $element, array $validators)
    {
        //bdump($validators);
        foreach ($validators as $validator) {
            //bdump($validator);
            if ($validator->type==":equal") {
                $element->addRule(Form::EQUAL, isset($validator->message) ? $validator->message : null, $this->{$validator->value});
            } else {
                $element->addRule(
                        $validator->type,
                        isset($validator->message) ? $validator->message : null,
                        isset($validator->value) ? is_array($validator->value) ? implode(";", $validator->value) : $validator->value : null
                    );
            }
        }
        return;
    }
}
