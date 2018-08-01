<?php
namespace occ2\GridControl;

use Ublaboo\DataGrid\DataGrid as UGrid;
use Ublaboo\DataGrid\Toolbar\ToolbarButton;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * DataGrid
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class DataGrid extends UGrid
{
	/**
	 * Add toolbar button
	 * @param  string  $href
	 * @param  string  $text
	 * @param  array   $params
	 * @return ToolbarButton
	 * @throws DataGridException
	 */
	public function addToolbarButtonForBuilder($key,$href, $text = '', $params = [])
	{
		if (isset($this->toolbar_buttons[$key])) {
			throw new DataGridException("There is already toolbar button at key [$key] defined.");
		}

		return $this->toolbar_buttons[$key] = new ToolbarButton($this, $href, $text, $params);
	}

        public function handleInlineEdit($id)
        {
            parent::handleInlineEdit($id);
            $this->redrawControl();
        }
}