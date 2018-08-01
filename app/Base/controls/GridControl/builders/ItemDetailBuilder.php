<?php
namespace occ2\GridControl;

use Ublaboo\DataGrid\DataGrid;
use Nette\Utils\ArrayHash;
use Ublaboo\DataGrid\Column\ItemDetail;

/**
 * ItemDetailBuilder
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class ItemDetailBuilder implements IAdditionalGridBuilder
{
    use TCallbacks;
    
    protected $grid;
    protected $object;
    protected $configurator;
    
    public function __construct($object, DataGrid $grid, GridConfig $configurator, $callbacks)
    {
        $this->grid = $grid;
        $this->object = $object;
        $this->configurator = $configurator;
        $this->callbacks = $callbacks;
        return;
    }
    
    public function build()
    {
        return $this->addDetails($this->grid);
    }
    
    protected function addDetails(DataGrid $grid)
    {
        $this->addItemDetail($grid, $this->configurator->getItemDetail());
        return;
    }
    
    protected function addItemDetail(DataGrid $grid, $config)
    {
        $t = $this;
        if ($config instanceof ArrayHash && isset($config->template)) {
            $detail = $grid->setItemsDetail(__DIR__ . $config->template, !isset($config->primaryColumn) ? null : $config->primaryColumn);
        } elseif ($this->checkCallback(GridBuilder::ITEM_DETAIL_CALLBACK)) {
            $detail = $grid->setItemsDetail(function ($item) use ($t) {
                return $t->invokeCallback(GridBuilder::ITEM_DETAIL_CALLBACK, null, $item, $t->object);
            }, !isset($config->primaryColumn) ? null : $config->primaryColumn);
        } else {
            $detail = $grid->setItemsDetail($this->object->_gridDetailTemplatePath);
        }
        
        if ($this->checkCallback(GridBuilder::ITEM_DETAIL_CONDITION_CALLBACK)) {
            $detail->setRenderCondition(function ($item) use ($t) {
                return $this->invokeCallback(GridBuilder::ITEM_DETAIL_CONDITION_CALLBACK, null, $item, $t->object);
            });
        }
        $this->setupItemDetail($detail, $config);
        return $detail;
    }
    
    protected function setupItemDetail(ItemDetail $detail, $config)
    {
        !($config instanceof ArrayHash && isset($config->text)) ?: $detail->setText($this->object->text($config->text));
        !($config instanceof ArrayHash && isset($config->title)) ?: $detail->setTitle($this->object->text($config->title));
        !($config instanceof ArrayHash && isset($config->class)) ?: $detail->setClass($config->class);
        !($config instanceof ArrayHash && isset($config->icon)) ?: $detail->setIcon($config->icon);
    }
}
