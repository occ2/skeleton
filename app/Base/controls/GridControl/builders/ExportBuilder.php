<?php
namespace occ2\GridControl;

use Ublaboo\DataGrid\DataGrid;
use Nette\Utils\ArrayHash;
use Ublaboo\DataGrid\Export\Export;

/**
 * ExportBuilder
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class ExportBuilder implements IAdditionalGridBuilder
{
    use TCallbacks;
    
    protected $grid;
    protected $configurator;
    protected $object;
    protected $callbacks;
    
    public function __construct($object, DataGrid $grid, GridConfig $configurator, ArrayHash $callbacks)
    {
        $this->object = $object;
        $this->grid = $grid;
        $this->configurator = $configurator;
        $this->callbacks = $callbacks;
        return;
    }
    
    public function build()
    {
        return $this->addExports($this->grid);
    }
    
    protected function addExports(DataGrid $grid)
    {
        $configs = $this->configurator->getExport(true);
        foreach ($configs as $config) {
            $this->addExport($grid, $config);
        }
    }
    
    protected function addExport(DataGrid $grid, ArrayHash $config)
    {
        if (!isset($config->name) || empty($config->name)) {
            throw new GridBuilderException("ERROR: Export name must be set", GridBuilderException::UNDEFINED_EXPORT_NAME);
        }
        $filtered = isset($config->filtered) ? $config->filtered : false;
        if ($this->checkCallback(GridBuilder::EXPORT_CALLBACK, $config->name)) {
            return $this->addCallbackExport($grid, $config, $filtered);
        } else {
            return $this->addCSVExport($grid, $config, $filtered);
        }
    }
    
    protected function addCallbackExport(DataGrid $grid, ArrayHash $config, $filtered)
    {
        $t = $this;
        $export = $grid->addExportCallback(
            isset($config->label) ? $this->object->text($config->label) : "",
            function ($data_source, $grid) use ($t,$config) {
                return $t->invokeCallback(GridBuilder::EXPORT_CALLBACK, $config->name, $data_source, $grid, $t->object);
            },
            $filtered
        );
        $this->setupExport($export, $config);
        return $export;
    }
    
    protected function addCSVExport(DataGrid $grid, ArrayHash $config, $filtered)
    {
        if ($filtered) {
            $export = $grid->addExportCsvFiltered(
                isset($config->label) ? $this->object->text($config->label) : "",
                isset($config->filename) ? $config->filename : "export_filtered.csv",
                isset($config->encoding) ? $config->encoding : null,
                isset($config->delimiter) ? $config->delimiter : null,
                isset($config->includeBom) ? $config->includeBom : false
            );
        } else {
            $export = $grid->addExportCsv(
                isset($config->label) ? $this->object->text($config->label) : "",
                isset($config->filename) ? $config->filename : "export_all.csv",
                isset($config->encoding) ? $config->encoding : null,
                isset($config->delimiter) ? $config->delimiter : null,
                isset($config->includeBom) ? $config->includeBom : false
            );
        }
        $this->setupExport($export, $config);
        return $export;
    }
    
    protected function setupExport(Export $export, ArrayHash $config)
    {
        !isset($config->class) ?: $export->setClass($config->class);
        !isset($config->icon) ?: $export->setIcon($config->icon);
        !isset($config->ajax) ?: $export->setAjax($config->ajax);
        !isset($config->href) ?: $export->setLink($config->href);
        !isset($config->title) ?: $export->setTitle($this->object->text($config->title));
        return;
    }
}
