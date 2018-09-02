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
use app\Base\controls\GridControl\DataGrid;
use Ublaboo\DataGrid\Column\ItemDetail;
use Nette\Utils\ArrayHash;

/**
 * ItemDetailBuilder
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class ItemDetailBuilder implements IAdditionalGridBuilder
{
    use TCallbacks;

    /**
     * @var DataGrid
     */
    protected $grid;

    /**
     * @var GridControl
     */
    protected $object;

    /**
     * @var GridConfig
     */
    protected $configurator;

    /**
     * @param GridControl $object
     * @param DataGrid $grid
     * @param GridConfig $configurator
     * @param array $callbacks
     * @return void
     */
    public function __construct(GridControl $object, DataGrid $grid, GridConfig $configurator, array $callbacks)
    {
        $this->grid = $grid;
        $this->object = $object;
        $this->configurator = $configurator;
        $this->callbacks = $callbacks;
        return;
    }

    /**
     * build details
     * @return void
     */
    public function build()
    {
        $this->addDetails($this->grid);
        return;
    }

    /**
     * add item details
     * @param DataGrid $grid
     * @return void
     */
    protected function addDetails(DataGrid $grid)
    {
        $config = $this->configurator->get("itemDetail");
        if($config!=null){
            $this->addItemDetail($grid, $config);
        }
        return;
    }

    /**
     * add one item detail
     * @param DataGrid $grid
     * @param ArrayHash $config
     * @return ItemDetail
     */
    protected function addItemDetail(DataGrid $grid, $config): ItemDetail
    {
        $t = $this;
        if (isset($config->template)) {
            $detail = $grid->setItemsDetail(__DIR__ . $config->template, !isset($config->primaryColumn) ? null : $config->primaryColumn);
        } elseif ($this->checkCallback(GridBuilder::ITEM_DETAIL_CALLBACK)) {
            $detail = $grid->setItemsDetail(function ($item) use ($t) {
                return $t->invokeCallback(GridBuilder::ITEM_DETAIL_CALLBACK, null, $item, $t->object);
            }, !isset($config->primaryColumn) ? null : $config->primaryColumn);
        } else {
            $detail = $grid->setItemsDetail($this->object->getDetailTemplatePath());
        }
        
        if ($this->checkCallback(GridBuilder::ITEM_DETAIL_CONDITION_CALLBACK)) {
            $detail->setRenderCondition(function ($item) use ($t) {
                return $this->invokeCallback(GridBuilder::ITEM_DETAIL_CONDITION_CALLBACK, null, $item, $t->object);
            });
        }
        $this->setupItemDetail($detail, $config);
        return $detail;
    }

    /**
     *
     * @param ItemDetail $detail
     * @param ArrayHash $config
     * @return void
     */
    protected function setupItemDetail(ItemDetail $detail, $config)
    {
        !(isset($config->text)) ?: $detail->setText($this->object->_($config->text));
        !(isset($config->title)) ?: $detail->setTitle($this->object->_($config->title));
        !(isset($config->class)) ?: $detail->setClass($config->class);
        !(isset($config->icon)) ?: $detail->setIcon($config->icon);
        return;
    }
}
