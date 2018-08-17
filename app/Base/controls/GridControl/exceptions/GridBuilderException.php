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

namespace app\Base\controls\GridControl\exceptions;

/**
 * GridBuilderException
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
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
