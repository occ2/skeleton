@primaryKey string primary key of datasource (if not id)
@pagination true/false - enable/disable pagination
@itemsPerPageList array - set items per page list
@defaultSort array - key=>ASC/DESC sorting
@defaultSort bool - reset filter after sorting
@multiSort bool - enable/disable multisort
@defaultPerPage int - default number of items per page
@columnsHidable bool - enable/disable hidable columns
@summary array - array of columns summary
@summaryFormat array (key, decimals, decPoint, thousandSeparator) - set format of columns summary
@gridCustomTemplate - set custom grid template
@defaultFilter array - key=>value default filter values
@outerFilter bool - is filter rendered separatelly?
@rememberState bool is state remembered?
@strictSessionFilterValues bool
@refreshUrl bool
@autosubmitFilter bool
@happyComponents bool - use happy components in group actions?

@sortable bool
@sortableHandler string 

@action
    name
    label
    icon
    class
    href
    params
    title
    confirm
    confirmCol
    newTab
    data-attribute
    ajax
    event

@multiAction
    name
    label
    icon
    class
    text
    title

@groupAction
    type simple,select,multiselect,text,textarea
    name
    label
    class
    attributes
    event
@itemDetail array/bool - item detail settings
            template
            primaryColumn
            icon
            class
            title
            text

@toolbarButton
            name
            href
            icon
            class
            title
            text
            attributes
            event
@export
    name
    filtered
    encoding
    delimiter
    filename
    includeBom
    ajax
    icon
    class
    title
    label
    href
    event
@inlineActions
@inlineFormControl
    name
    type
    cols
    rows
    required
    readonly
@inlineFormValidator - set control validator (array with name,type, message and value) - multiple validators supported    

@inlineAdd
    topPosition
    icon
    class
    title
    text
    event
@inlineEdit
    showNonEditingColumns
    primaryWhereColumn
    icon
    class
    title
    text
    event
Column configs
* @type string - type of column (text,number,datetime,link, status)
@label string translatable column heading
@dbCol string database column assigned with grid column
@translate bool - enable/disable column content translatong (overridden by renderer callback)
@sortable bool/string - set column sortable if. You can set database sorting column as string
@template string - path to custom column template
@replacement array - array of key=>value replacements
@templateEscaping bool enable/disable escaping a content in template
@headerEscaping bool enable/disable header escaping
@resetPaginationAfterSorting bool - enable/disable reset pagination after sorting
@align string - column alignement left/center/right
@hidden - is column set as default hidden?
@options array - simple set column status options
@option array - multiple settings of column status options (text,icon,iconSecondary, class, classSecondary, title, classInDropdown)
@numberFormat array - set number format (decimals,decPoint,thousandsSeparator)
@datetimeFormat array - set datetime format for PHP and JavaScript (php, js)
@newTab bool - for link column - open link in new browser tab
@attributes array - set attributes for both th and td elements
@fitContent bool - enable/disable column content fit 
@translatableHeader bool - enable/disable translating header
@sort array - column sorting settings
@icon string - set icon before link text
@class string - set link class
@title string - set translatable link title
@parameters array - set link parameters
@dataAttributes array - set data attributes
@editableType
@editableAttributes
@filter array - set column filter
        type - type of filter (text,select,multiselect,range,date,daterange)
        value - set filter value
        placeholder - set filter placeholder
        attribute - set key=>$value atribute
        template - set custom filter template path
        size - size of input control
        
        columns - comma separated list of columns where filtered (only text filter)
        exactSearch -  (only text filter)
        splitWordsSearch -  (only text filter)

        column -  set filter column (only select & multiselect filter)
        prompt - set filter prompt (only select & multiselect filter)
        translateOptions - are options translatable? (only select & multiselect filter)

        phpFormat (only date & datetime filter)
        jsFormat (only date & datetime filter)

        nameSecond (only for range & daterange filter)
        placeholders - comma separated array of placeholders (only for range & daterange filter)
        

setLoadOptionsCallback($columnName,function($control):array{}) 
setItemDetailCallback(function($item, $control):string{})
setItemDetailConditionCallback(function($item, $control):bool{})
setToolbarButtonCallback($buttonName,function($control, $params):void{})
setColumnRendererCallback($columnName,function($item,$control):string{})
setColumnCondition($columnName,function($item,$control):bool{})
setSortColumnCallback($columnName,function($datasource,$sort,$control):void{})
setStatusChangeCallback($columnName,function($id,$value,$control):void{})
setSummaryCallback(function($item,$column,$control):float{})
setSummaryRendererCallback(function($sum,$columnName,$control):string{})
setColumnCallback($columnName,function($column, $item, $control):void{})
setFilterConditionCallback($filterName,function($datasource,$value,$control):void{})
setActionCallback($actionName,function($id,$grid,$control):void{})
setActionConfirmCallback($actionName,function($item, $control):string{})
setActionIconCallback($actionName,function($item, $control):string{})
setActionClassCallback($actionName,function($item, $control):string{})
setActionTitleCallback($actionName,function($item, $control):string{})
setSortingCallback(function($itemId, $prevId, $nextId, $control):void{})
setGroupActionOptionsCallback($actionName,function($control):array{})
setGroupActionCallback($actionName,function($ids, $control, $option | $value):mixed{})
setAllowRowsGroupActionCallback($actionName,function($item,$control):bool{})
setAllowRowsInlineEditCallback(function($item,$control):bool{})
setAllowRowsActionCallback($actionName,function($item,$control):bool{})
setAllowRowsMultiActionCallback($actionName,function($item,$control):bool{})  - key is in multiaction-action format
setAllowToolbarButtonCallback($buttonName,function($control):bool{})
setRowCallback(function($item,$tr,$control):void{})
setExportCallback($exportName,function($datasource,$grid,$control):void{})
setEditableCallback($columnName,function($id,$value,$control):void{})
setEditableValueCallback($columnName,function($row,$control):string{})

setInlineFormCallback(function($container,$control):void{})
setInlineLoadOptionsCallback($columnName,function($control):array{})
setInlineFormFillCallback(function($container,$item,$control):void{})
setInlineAddSubmitCallback(function($values,$control):void{})
setInlineEditSubmitCallback(function($id,$values,$control):void{})
setInlineCustomRedrawCallback(function ($grid,$control):void{})
