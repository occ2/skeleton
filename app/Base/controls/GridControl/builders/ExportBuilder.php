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
use app\Base\controls\GridControl\exceptions\GridBuilderException;
use app\Base\controls\GridControl\builders\GridBuilder;
use app\Base\controls\GridControl\DataGrid;
use Ublaboo\DataGrid\Export\Export;
use Nette\Utils\ArrayHash;

/**
 * ExportBuilder
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class ExportBuilder implements IAdditionalGridBuilder
{
    use TCallbacks;

    /**
     * @var DataGrid
     */
    protected $grid;

    /**
     * @var GridConfig
     */
    protected $configurator;

    /**
     * @var GridControl
     */
    protected $object;

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
     * build exports
     * @return void
     */
    public function build()
    {
        $this->addExports($this->grid);
        return;
    }

    /**
     * add one export
     * @param DataGrid $grid
     * @return void
     */
    protected function addExports(DataGrid $grid)
    {
        $configs = $this->configurator->get("export",true);
        foreach ($configs as $config) {
            $this->addExport($grid, $config);
        }
        return;
    }

    /**
     * add one export
     * @param DataGrid $grid
     * @param ArrayHash $config
     * @return Export
     * @throws GridBuilderException
     */
    protected function addExport(DataGrid $grid, ArrayHash $config): Export
    {
        if (!isset($config->name) || empty($config->name)) {
            throw new GridBuilderException("ERROR: Export name must be set", GridBuilderException::UNDEFINED_EXPORT_NAME);
        }
        $filtered = isset($config->filtered) ? $config->filtered : false;
        if ($this->checkCallback(GridBuilder::EXPORT_CALLBACK, $config->name)) {
            return $this->addCallbackExport($grid, $config, $filtered);
        } elseif(isset($config->event)){
            return $this->addExportEvent($grid, $config, $filtered);
        } else {
            return $this->addCSVExport($grid, $config, $filtered);
        }
    }

    /**
     * add export event support
     * @param DataGrid $grid
     * @param ArrayHash $config
     * @param bool $filtered
     * @return mixed
     */
    protected function addExportEvent(DataGrid $grid, ArrayHash $config, bool $filtered){
        $t = $this;
        $export = $grid->addExportCallback(
            isset($config->label) ? $this->object->_($config->label) : "",
            function ($data_source, $grid) use ($t,$config) {
                $eventName = $config->event;
                $data = $t->object->getGridEventFactory()->create(
                    $grid,
                    $t->object,
                    null,
                    $data_source,
                    $eventName
                );
                $t->object->on($eventName, $data);
                return;
            },
            $filtered
        );
        $this->setupExport($export, $config);
        return $export;
    }

    /**
     * add callback export
     * @param DataGrid $grid
     * @param ArrayHash $config
     * @param bool $filtered
     * @return Export
     */
    protected function addCallbackExport(DataGrid $grid, ArrayHash $config, bool $filtered):Export
    {
        $t = $this;
        $export = $grid->addExportCallback(
            isset($config->label) ? $this->object->_($config->label) : "",
            function ($data_source, $grid) use ($t,$config) {
                return $t->invokeCallback(GridBuilder::EXPORT_CALLBACK, $config->name, $data_source, $grid, $t->object);
            },
            $filtered
        );
        $this->setupExport($export, $config);
        return $export;
    }

    /**
     * add CSV export
     * @param DataGrid $grid
     * @param ArrayHash $config
     * @param bool $filtered
     * @return Export
     */
    protected function addCSVExport(DataGrid $grid, ArrayHash $config, bool $filtered): Export
    {
        if ($filtered) {
            $export = $grid->addExportCsvFiltered(
                isset($config->label) ? $this->object->_($config->label) : "",
                isset($config->filename) ? $config->filename : "export_filtered.csv",
                isset($config->encoding) ? $config->encoding : null,
                isset($config->delimiter) ? $config->delimiter : null,
                isset($config->includeBom) ? $config->includeBom : false
            );
        } else {
            $export = $grid->addExportCsv(
                isset($config->label) ? $this->object->_($config->label) : "",
                isset($config->filename) ? $config->filename : "export_all.csv",
                isset($config->encoding) ? $config->encoding : null,
                isset($config->delimiter) ? $config->delimiter : null,
                isset($config->includeBom) ? $config->includeBom : false
            );
        }
        $this->setupExport($export, $config);
        return $export;
    }

    /**
     * setup export
     * @param Export $export
     * @param ArrayHash $config
     * @return void
     */
    protected function setupExport(Export $export, ArrayHash $config)
    {
        !isset($config->class) ?: $export->setClass($config->class);
        !isset($config->icon) ?: $export->setIcon($config->icon);
        !isset($config->ajax) ?: $export->setAjax($config->ajax);
        !isset($config->href) ?: $export->setLink($config->href);
        !isset($config->title) ?: $export->setTitle($this->object->_($config->title));
        return;
    }
}
