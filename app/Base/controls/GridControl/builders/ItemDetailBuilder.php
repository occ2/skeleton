<?php
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
     * @param type $callbacks
     * @return void
     */
    public function __construct(GridControl $object, DataGrid $grid, GridConfig $configurator, $callbacks)
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
        return $this->addDetails($this->grid);
    }

    /**
     * add item details
     * @param DataGrid $grid
     * @return void
     */
    protected function addDetails(DataGrid $grid)
    {
        $this->addItemDetail($grid, $this->configurator->getItemDetail());
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

    /**
     *
     * @param ItemDetail $detail
     * @param ArrayHash $config
     * @return void
     */
    protected function setupItemDetail(ItemDetail $detail, $config)
    {
        !($config instanceof ArrayHash && isset($config->text)) ?: $detail->setText($this->object->text($config->text));
        !($config instanceof ArrayHash && isset($config->title)) ?: $detail->setTitle($this->object->text($config->title));
        !($config instanceof ArrayHash && isset($config->class)) ?: $detail->setClass($config->class);
        !($config instanceof ArrayHash && isset($config->icon)) ?: $detail->setIcon($config->icon);
        return;
    }
}
