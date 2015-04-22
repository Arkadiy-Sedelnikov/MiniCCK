<?php

// No direct access
defined( '_JEXEC' ) or die;

/**
 * Controller for edit current element
 * @author Arkadiy
 */

require_once(JPATH_ROOT. '/administrator/components/com_minicckimport/lib/uploadfile.class.php');
require_once(JPATH_ROOT. '/administrator/components/com_minicckimport/lib/csv.io.class.php');
require_once(JPATH_ROOT . '/administrator/components/com_minicckimport/lib/PHPExcel/PHPExcel/IOFactory.php');

/**
 * FLEXIcontent Component Item Controller
 *
 * @package Joomla
 * @subpackage FLEXIcontent
 * @since 1.0
 */

class MinicckimportControllerMain extends JControllerLegacy
{
    var $view_list,
        $cols_delimiter,
        $rows_delimiter;
	/**
	 * Class constructor
	 * @param array $config
	 */
	function __construct( $config = array() )
	{
		$this->view_list = 'mains';
        $params = JComponentHelper::getParams('com_minicckimport');
        $this->cols_delimiter = $params->get('cols_delimiter', '::');
        $rows_delimiter = $params->get('rows_delimiter', "\n");
        $this->rows_delimiter = ($rows_delimiter === '\n') ? "\n" : $rows_delimiter;
		parent::__construct( $config );
	}

    function upload()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit('Invalid Token');

        $dir = JPATH_ROOT.'/tmp/';
        $link = 'index.php?option=com_minicckimport&view=main';

        $input = JFactory::getApplication()->input;

        $language = $input->getCmd('language', '');
        $type_id = $input->getCmd('type_id', 0);
        $maincat = $input->getInt('maincat', 0);
        $second_cat = $input->get('second_cat', array(), 'array');
        $maincat_col = $input->getInt('maincat_col', 0);
        $seccats_col = $input->getInt('seccats_col', 0);
        $field_separator = $input->getString('field_separator', '');
        $enclosure_char = $input->getString('enclosure_char', '');
        $num_headers = $input->getInt('num_headers', '');
        $num_content = $input->getInt('num_content', '');



        if (!$type_id) {
            // Check for the required Content Type Id
            $this->setRedirect($link, JText::_('COM_MINICCKIMPORT_SELECT_TYPE'));
            return;
        }

        if (!$maincat && !$maincat_col) {
            // Check for the required main category
            $this->setRedirect($link, JText::_('COM_MINICCKIMPORT_SELECT_CAT'));
            return;
        }

        $upload = new UploadFile($_FILES['csvfile']);
        $upload->setAllowFile(array('csv', 'xls', 'xlsx'));
        $upload->setDir($dir);

        if (!$upload->upload())
        {
            $this->setRedirect($link, JText::_('COM_MINICCKIMPORT_ERROR_UPLOAD_FILE').$upload->getErrorUploadMsg($upload->error), 'error');
        }

        $name = $upload->getName();
        $filename = $dir . $name;
        $fileData = $upload->parseNameFile($name);

        $uploaddata = array(
            'file' => $filename,
            'type_id' => $type_id,
            'language' => $language,
            'maincat' => $maincat,
            'seccats' => $second_cat,
            'field_separator' => $field_separator,
            'enclosure_char' => $enclosure_char,
            'num_headers' => $num_headers,
            'num_content' => $num_content,
            'file_ext' => $fileData["ext"],
            'maincat_col' => $maincat_col,
            'seccats_col' => $seccats_col,
        );

        $app = JFactory::getApplication();

        $app->setUserState('com_minicckimport.uploaddata', $uploaddata);

        switch($fileData["ext"])
        {
            case 'xls':
            case 'xlsx':
            case 'csv':
                $link = 'index.php?option=com_minicckimport&view=compareheaders';
                $this->setRedirect($link);
                break;
            default:
                $this->setRedirect($link, JText::_('COM_MINICCKIMPORT_ERROR_EXT_FILE').$fileData["ext"], 'error');
                break;
        }
    }

    /**
     * Logic to importcsv of the items
     *
     * @access public
     * @return void
     * @since 1.5
     */
    function import()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit('Invalid Token');

        $app = JFactory::getApplication();
        $data = $app->getUserState('com_minicckimport.uploaddata');

        $this->addModelPath(JPATH_ROOT.'/administrator/components/com_content/models');
        // Get item model
        $model = $this->getModel('Article', 'ContentModel');

        $model->addTablePath(JPATH_ADMINISTRATOR . '/components/com_content/tables');
        JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_content/models/forms');
        JForm::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_content/models/fields');

        $plugin = JPluginHelper::getPlugin('system', 'minicck');
        $pluginParams = json_decode($plugin->params);

        $typeForName = array();
        foreach($pluginParams->customfields as $v){
            $typeForName[$v->name] = $v->type;
        }

        // Set some variables
        $link = 'index.php?option=com_minicckimport&view=main';
        $debug = JRequest::getInt('debug', 0);
        $mainframe = JFactory::getApplication();
        $db = JFactory::getDBO();

        $file = $data['file'];
        $type_id = $data['type_id'];
        $language = $data['language'];
        $maincat = $data['maincat'];
        $seccats = $data['seccats'];
        $field_separator = $data['field_separator'];
        $enclosure_char = $data['enclosure_char'];
        $num_headers = $data['num_headers'];
        $num_content = $data['num_content'];
        $file_ext = $data['file_ext'];
        $maincat_col = $data['maincat_col'];
        $seccats_col = $data['seccats_col'];

        $fieldsAssoc = $app->input->get('fields', array(), 'array');
        $content_key = $app->input->getString('content_key', '');
        $file_key = $app->input->getString('file_key', '');

        $onlyNewItems = false;
        if($content_key === '' || $file_key === '')
        {
            $onlyNewItems = true;
        }


        $cckFields = $fieldsAssoc['cck'];
        unset($fieldsAssoc['cck']);

        //очищаем соответствие полей контента и файла от пустых значений.
        foreach($fieldsAssoc as $k => $v)
        {
            if($v == '')
            {
                unset($fieldsAssoc[$k]);
            }
        }

        foreach($cckFields as $k => $v)
        {
            if($v == '')
            {
                unset($cckFields[$k]);
            }
        }

        // Get field names (from the header line (row 0), and remove it form the data array
        $columns = array_keys($fieldsAssoc);

        // Check for the (required) title column and other columns
        if (!in_array('title', $columns)) {
            $this->setRedirect($link, JText::_('COM_MINICCKIMPORT_ERROR_NOT_SELECT_TITLE_FIELD'));
            return;
        }
        if ($maincat_col && !in_array('catid', $columns)) {
            $this->setRedirect($link, JText::_('COM_MINICCKIMPORT_ERROR_NOT_SELECT_CAT_FIELD'));
            return;
        }

        if (!$language && !in_array('language', $columns)) {
            $this->setRedirect($link, JText::_('COM_MINICCKIMPORT_ERROR_NOT_SELECT_LANG'));
            return;
        }

        //чтение файла
        if($file_ext == 'csv'){
            $contents = $this->load_csv();
        }
        else{
            $contents = $this->load_xls();
        }

        //удаляем файл
        unlink($file);
        //удаляем инфо о загрузке
        $mainframe->setUserState('com_minicckimport.uploaddata', array());

        // Basic error checking, for empty data
        if (count($contents) <= 0) {
            $this->setRedirect($link, JText::_('COM_MINICCKIMPORT_ERROR_READ_FILE'));
            return;
        }

        $headers = $app->getUserState('com_minicckimport.headers');
        $app->setUserState('com_minicckimport.headers', null);
        $headers = array_flip($headers);

        // Handle each row (item) using store() method of the item model to create the items
        $cnt = 1;
        foreach ($contents as $fields)
        {
            //Подготовка данных для com_content
            $data = array();
            $data['language'] = $language;
            $data['catid'] = $maincat;
            $data['state'] = 0;

            // Handle each field of the item
            foreach ($fieldsAssoc as $fieldname => $col_name)
            {
                $col_id = $headers[$col_name];
                $dataCell = $fields[$col_id];

                $data[$fieldname] = $this->encodeContentFieldData($fieldname, $dataCell);
            }

            //пропускаем запись если заголовок пустой
            if(empty($data['title'])){
                continue;
            }

            if(!isset($data['alias'])){
                $data['alias'] = JApplicationHelper::stringURLSafe($data['title']);
            }
            else{
                $data['alias'] = JApplicationHelper::stringURLSafe($data['alias']);
            }

            // Set/Force id to zero to indicate creation of new item
            if($onlyNewItems)
            {
                $data['id'] = 0;
            }
            else if(empty($data['id']))
            {
                $query = $db->getQuery(true);
                $query->select('id');
                $query->from('#__content');
                $query->where($db->quoteName($content_key).' = '.$db->quote($fields[$headers[$file_key]]));
                $db->setQuery($query, 0, 1);
                $id = $db->loadResult();
                $data['id'] = !empty($id) ? (int)$id : 0;
            }

            // Validate the posted data.
            // Sometimes the form needs some posted data, such as for plugins and modules.
            $form = $model->getForm($data, false);

            if (!$form)
            {
                $msg = $cnt . JText::sprintf('COM_MINICCKIMPORT_ERROR_IMPORT_CONTENT', $data['title']) . " " . $model->getError();
                throw new Exception($msg, 500);
            }

            // Test whether the data is valid.
            $validData = $model->validate($form, $data);

            // Check for validation errors.
            if ($validData === false)
            {
                // Get the validation messages.
                $errors = $model->getErrors();

                // Push up to three validation messages out to the user.
                for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
                {
                    if ($errors[$i] instanceof Exception)
                    {
                        $errors .= ' '.$errors[$i]->getMessage();
                    }
                    else
                    {
                        $errors .= ' '.$errors;
                    }
                }

                $msg = $cnt . JText::sprintf('COM_MINICCKIMPORT_ERROR_IMPORT_CONTENT', $data['title']);
                throw new Exception($msg . " " . $errors, 500);
            }

            // Finally try to create the item by using Item Model's store() method
            if (!$model->save($data))
            {
                $msg = $cnt . JText::sprintf('COM_MINICCKIMPORT_ERROR_IMPORT_CONTENT', $data['title']);
                throw new Exception($msg . " " . $model->getError(), 500);
            }
            else
            {
                $contentId = ($data['id'] == 0) ? $model->getState('article.id') : $data['id'];

                if($contentId > 0)
                {
                    //Дополнительные поля

                    $query = $db->getQuery(true);
                    $query->delete('#__minicck')
                        ->where('content_id = '.$db->quote($contentId));
                    $db->setQuery($query)->execute();

                    $object = new stdClass();
                    $object->content_id = $contentId;

                    $col_id = isset($headers['content_type']) ? $headers['content_type'] : 0;
                    $object->content_type = ($col_id > 0 && !empty($fields[$col_id])) ? $fields[$col_id] : $type_id;

                    foreach ($cckFields as $fieldname => $col_name)
                    {

                        if($fieldname == 'cid' || $fieldname == 'content_type')
                            continue;

                        $col_id = $headers[$col_name];
                        $dataCell = $fields[$col_id];
                        $type = $typeForName[$fieldname];

                        $object->$fieldname = $this->encodeMinicckFieldData($type, $dataCell);
                    }

                    if(!$db->insertObject('#__minicck', $object))
                    {
                        $msg = $cnt . JText::sprintf('COM_MINICCKIMPORT_ERROR_IMPORT_FIELDS', $data['title']);
                        $mainframe->enqueueMessage($msg, 'error');
                    }

                    //Дополнительные категории

                    $query = $db->getQuery(true);
                    $query->delete('#__minicck_categories')
                        ->where('article_id = '.$db->quote($contentId));
                    $db->setQuery($query)->execute();

                    if(!empty($headers[$cckFields['cid']]))
                    {
                        $col_id =  $headers[$cckFields['cid']];
                        $catdata = array();
                        if(!empty($fields[$col_id])){
                            $catdata = explode(',', $fields[$col_id]);
                        }

                    }
                    else{
                        $catdata = $seccats;
                    }

                    if(is_array($catdata) && count($catdata))
                    {
                        foreach ($catdata as $v)
                        {
                            $object = new stdClass();
                            $object->article_id = $contentId;
                            $object->category_id = $v;
                            if(!$db->insertObject('#__minicck_categories', $object))
                            {
                                $msg = $cnt . JText::sprintf('COM_MINICCKIMPORT_ERROR_IMPORT_SECOND_CATS', $data['title']);
                                $mainframe->enqueueMessage($msg, 'error');
                            }
                        }
                    }
                }
                else
                {
                    $msg = $cnt . JText::sprintf('COM_MINICCKIMPORT_ERROR_IMPORT_FIELDS', $data['title']);
                    $mainframe->enqueueMessage($msg, 'error');
                }
                $msg = $cnt . JText::sprintf('COM_MINICCKIMPORT_IMPORT_CONTENT_SUCCESS', $data['title']);
                $mainframe->enqueueMessage($msg);
                $cnt++;
            }
        }

        $cache = JFactory::getCache('com_content');
        $cache->clean();

        $this->setRedirect($link);
    }

    function load_headers_csv()
    {
        $app = JFactory::getApplication();
        $data = $app->getUserState('com_minicckimport.uploaddata');

        $csv = new csv();
        if(!empty($data['field_separator'])){
            $csv->setDelimit($data['field_separator']);
        }
        if(!empty($data['enclosure_char'])){
            $csv->setTextQualifier($data['enclosure_char']);
        }
        return $csv->readHeaders($data['file'], $data['num_headers']);
    }

    function load_csv()
    {
        $app = JFactory::getApplication();
        $data = $app->getUserState('com_minicckimport.uploaddata');

        $csv = new csv();
        if(!empty($data['field_separator'])){
            $csv->setDelimit($data['field_separator']);
        }
        if(!empty($data['enclosure_char'])){
            $csv->setTextQualifier($data['enclosure_char']);
        }
        return $csv->read($data['file'], $data['num_content']);
    }


    function load_headers_xls()
    {
        $app = JFactory::getApplication();
        $data = $app->getUserState('com_minicckimport.uploaddata');

        set_include_path(JPATH_BASE . '/components/com_minicckimport/lib/PHPExcel');


        if($data['file_ext'] == 'xls'){
            $inputFileType = 'Excel5';
        }
        else if($data['file_ext'] == 'xlsx'){
            $inputFileType = 'Excel2007';
        }
        else{
            return array();
        }

        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setReadDataOnly(true);

        $chunkFilter = new chunkReadFilter();
        $chunkFilter->setRows(($data['num_headers']),1);
        $objReader->setReadFilter($chunkFilter);

        $objPHPExcel = $objReader->load($data['file']);
        $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true);

        foreach($sheetData as $v){
            $return = $v;
        }

        foreach($return as $k => $v){
            if($v == ''){
                unset($return[$k]);
            }
        }
        return $return;
    }

    function load_xls()
    {
        $app = JFactory::getApplication();
        $data = $app->getUserState('com_minicckimport.uploaddata');

        set_include_path(JPATH_BASE . '/components/com_minicckimport/lib/PHPExcel');


        if($data['file_ext'] == 'xls'){
            $inputFileType = 'Excel5';
        }
        else if($data['file_ext'] == 'xlsx'){
            $inputFileType = 'Excel2007';
        }
        else{
            return array();
        }

        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setReadDataOnly(true);

        $chunkFilter = new chunkReadFilter();
        $chunkFilter->setRows(($data['num_content']),10000);
        $objReader->setReadFilter($chunkFilter);

        $objPHPExcel = $objReader->load($data['file']);
        $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true);

        return $sheetData;
    }

    private function encodeContentFieldData($fieldname, $dataCell)
    {
        switch($fieldname)
        {
            case 'images':
                $image = json_decode('{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}');
                if(!empty($dataCell)){
                    $dataCell = explode($this->rows_delimiter, $dataCell);
                    foreach($dataCell as $k => $v){
                        $dataCell[$k] = explode($this->cols_delimiter, $v);
                    }
                    if(isset($dataCell[0])){
                        $image->image_intro = (isset($dataCell[0][0])) ? $dataCell[0][0] : '';
                        $image->image_intro_alt = (isset($dataCell[0][1])) ? $dataCell[0][1] : '';
                        $image->float_intro = (isset($dataCell[0][2])) ? $dataCell[0][2] : '';
                    }
                    if(isset($dataCell[1])){
                        $image->image_fulltext = (isset($dataCell[1][0])) ? $dataCell[1][0] : '';
                        $image->image_fulltext_alt = (isset($dataCell[1][1])) ? $dataCell[1][1] : '';
                        $image->float_fulltext = (isset($dataCell[1][2])) ? $dataCell[1][2] : '';
                    }
                }
                $data = json_encode($image);
                break;
            case 'urls' :
                $url = json_decode('{"urla":false,"urlatext":"","targeta":"","urlb":false,"urlbtext":"","targetb":"","urlc":false,"urlctext":"","targetc":""}');
                if(!empty($dataCell)){
                    $dataCell = explode($this->rows_delimiter, $dataCell);
                    foreach($dataCell as $k => $v){
                        $dataCell[$k] = explode($this->cols_delimiter, $v);
                    }
                    if(isset($dataCell[0])){
                        $url->urla = (isset($dataCell[0][0])) ? $dataCell[0][0] : false;
                        $url->urlatext = (isset($dataCell[0][1])) ? $dataCell[0][1] : '';
                    }
                    if(isset($dataCell[1])){
                        $url->urlb = (isset($dataCell[1][0])) ? $dataCell[1][0] : false;
                        $url->urlbtext = (isset($dataCell[1][1])) ? $dataCell[1][1] : '';
                    }
                    if(isset($dataCell[2])){
                        $url->urlc = (isset($dataCell[2][0])) ? $dataCell[2][0] : false;
                        $url->urlctext = (isset($dataCell[2][1])) ? $dataCell[2][1] : '';
                    }
                }
                $data = json_encode($url);
                break;
            default :
                $data = $dataCell;
                break;
        }
        return $data;
    }

    private function encodeMinicckFieldData($type, $dataCell)
    {
        switch($type)
        {
            case 'mccheckbox' :
            case 'mcselect' :
                $dataCell = trim($dataCell);
                $dataCell = str_replace(array(' ', '.'), array('', ','), $dataCell);
                break;
            case 'table' :
                if(!empty($dataCell))
                {
                    $dataCell = trim($dataCell);
                    $dataCell = explode($this->rows_delimiter, $dataCell);
                    $newData = array();
                    foreach($dataCell as $key => $val){
                        $val = explode($this->cols_delimiter, $val);
                        foreach ($val as $k => $v) {
                            $newData[$key][$k] = $v;
                        }
                    }
                    $dataCell = json_encode($newData);
                }
                break;
            case 'slider':
            case 'minigallery':
                if(!empty($dataCell))
                {
                    $dataCell = trim($dataCell);
                    $dataCell = explode($this->rows_delimiter, $dataCell);
                    $newData = array();
                    foreach($dataCell as $key => $val){
                        $val = explode($this->cols_delimiter, $val);
                        $newData[$key]['image'] = $val[0];
                        $newData[$key]['alt'] = isset($val[1]) ? $val[1] : '';
                    }
                    $dataCell = json_encode($newData);
                }
                break;

            default :
                $dataCell = trim($dataCell);
                break;
        }

        return $dataCell;
    }

}



class chunkReadFilter implements PHPExcel_Reader_IReadFilter
{
    private $_startRow = 0;
    private $_endRow = 0;

    public function setRows($startRow, $chunkSize) {
        $this->_startRow    = $startRow;
        $this->_endRow      = $startRow + $chunkSize;
    }

    public function readCell($column, $row, $worksheetName = '') {
        if ($row >= $this->_startRow && $row < $this->_endRow) {
            return true;
        }
        return false;
    }
}
