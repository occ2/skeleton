<?php
namespace occ2\GridControl;

/**
 * GridBuilderException
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class GridBuilderException extends \Exception
{
    const INVALID_DATASET=1,
          NO_PARENT_SET=2,
          INVALID_COLUMN_CONFIG=3,
          INVALID_FILTER_TYPE=4,
          INVALID_FILTER_CONFIG=5,
          INVALID_CONFIG=6,
          ACTION_NAME_NOT_SET=7,
          UNDEFINED_BUTTON_NAME=8,
          UNDEFINED_GROUP_ACTION_NAME=9,
          UNDEFINED_GROUP_ACTION_OPTION_CALLBACK=10,
          UNDEFINED_EXPORT_NAME=11,
          INVALID_INLINE_FORM_CONTROL_TYPE=12,
          INVALID_INLINE_FORM_CONTROL_NAME=13,
          INVALID_INLINE_FORM_VALIDATOR_NAME=14,
          INVALID_LOAD_OPTIONS_CALLBACK=15,
          INVALID_INLINE_ADD_SUBMIT_CALLBACK=16,
          INVALID_INLINE_EDIT_SUBMIT_CALLBACK=17,
          INVALID_INLINE_FORM_FILL_CALLBACK=18;
}
