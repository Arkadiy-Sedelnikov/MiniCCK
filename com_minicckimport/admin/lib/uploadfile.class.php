<?php
/**
* Uplpad file & Upload Image
* @version      1.6.1.1 29.09.2012
* @author       MAXXmarketing GmbH
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL 
*/

/**
ERROR UPLOAD
0 - File Upload Finished
1 - File Error size ini
2 - UPLOAD_ERR_FORM_SIZE
3 - UPLOAD_ERR_PARTIAL
4 - UPLOAD_ERR_NO_FILE (file not upload)
11 - File not allow
12 - File forbid
13 - File copy Error
14 - File Error size class
15 - Error array $_FILES
16 - filesize > post_max_size
*/

class UploadFile{

    /* File parametr from $_FILES */
    var $name = null;
    var $tmp_name = null;
    var $type = null;
    var $size = null;
    var $error = null;
    
    var $uploaded_real_name_file = "";

    /*Upload Dir*/
    var $dir = ".";
    var $new_dir_access = 0777;

    /*Config*/
    var $auto_rename_file = 1;
    var $auto_create_dir = 1;
    var $file_upload_ok = 0;
    var $file_name_md5 = 1;
    var $file_name_filter = 0;

    /*install allow or forbid files ext*/
    var $allow_file = array();
    var $forbid_file = array('php','php2','php3','php4','php5','js','html','htm');

    /*set upload max file size (kb)*/
    var $maxSizeFile = 0;

    /**
    * constructor
    * @param $file - $_FILES
    */
    function UploadFile($file){
        if (!is_array($file)){
            $this->error = 15;
            return 0;    
        }
        $this->name = $file['name'];
        $this->tmp_name = $file['tmp_name'];
        $this->type = $file['type'];
        $this->size = $file['size'];
        $this->error = $file['error'];
    }

    function setName($name){
        $this->name = $name;
    }

    function getName(){
        return $this->name;
    }
    
    function setDir($val){
        $this->dir = $val;
    }

    function getDir(){
        return $this->dir;
    }
    
    function setAutoRenameFile($val){
        $this->auto_rename_file = $val;
    }
    
    function setNameWithoutExt($name){
        $tmp = $this->parseNameFile($this->name);
        if ($tmp['ext']!='') $ext = ".".$tmp['ext']; else $ext = "";
        $this->name = $name.$ext;
    }

    /**
    * $size int - max size upload file in (Kb)
    */
    function setMaxSizeFile($size){
        $this->maxSizeFile = $size;
    }
    
    /**
    * set to md5 name file
    */
    function setFileNameMd5($val){
        $this->file_name_md5=$val;
    }
    
    /**
    * set filter name (enable, disable)
    */    
    function setFilterName($val){
        $this->file_name_filter = $val;
    }

    /**
    * set array allow file upload
    */
    function setAllowFile($file){
        $this->allow_file = array_map('strtolower', $file);
        $this->forbid_file = array();
    }
    
    /**
    * set array forbid file upload
    */
    function setForbidFile($file){
        $this->forbid_file = array_map('strtolower',$file);
        $this->allow_file = array();
    }
    
    /**
    * after upload
    */
    function getError(){
        return $this->error;
    }

    /**
    * @param string name file
    * @return array("name","ext","dir")
    */
    function parseNameFile($name){
        $pathinfo=pathinfo($name);
        $ext=$pathinfo['extension'];
        $name=$pathinfo['basename'];
        $dir=$pathinfo['dirname'];
        if ($ext!="") $b_name=substr($name,0,strlen($name)-strlen($ext)-1); else $b_name=$name;
    return array('name'=>$b_name, "ext"=>$ext, "dir"=>$dir);
    }
        
    /**
    * rename file md5 name
    */
    function renameFileMd5($name){
        $m=$this->parseNameFile($name);
        $m['name']=md5(mktime().$m['name']);
        if ($m['ext']!="") $m['ext']='.'.$m['ext'];
        $name=$m['name'].$m['ext'];
    return $name;
    }

    /**
    * rename existented file
    */
    function renameExistingFile($dir, $name){
        if (is_file($dir."/".$name)) {
            $m=$this->parseNameFile($name);
            if ($m['ext']!="") $m['ext']='.'.$m['ext'];
            $i=1;
            $name=$m['name'].$i.$m['ext'];
            while (is_file($dir."/".$name)){
                $name=$m['name'].$i.$m['ext'];
                $i++;
            }
        }
    return $name;
    }
    
    /**
    * rename file from filter
    */
    function renameFileFilter($name){
        $filters = array();
        $filters["ü"] = "u";
        $filters["ä"] = "a";
        $filters["ö"] = "o";
        $filters["Ü"] = "U";
        $filters["Ä"] = "A";
        $filters["Ö"] = "O";
        $filters["ß"] = "ss";
        foreach($filters as $k=>$v){
            $name = str_replace($k, $v, $name);
        }
        $name = preg_replace("/[^a-zA-Z0-9\.]/", "_", $name);
    return $name;
    }

    /**
    * get test file allow
    */
    function getTestFileAllow(){
        $mas=pathinfo($this->name);
        $ext=strtolower($mas['extension']);

        if (count($this->allow_file)>0){
             if (!in_array($ext,$this->allow_file)) {
                 $this->error=11;
                 return 0;
             }
        }

        if (count($this->forbid_file)>0){
             if (in_array($ext,$this->forbid_file)) {
                 $this->error=12;
                 return 0;
             }
        }
        
        if ($this->maxSizeFile!=0 && $this->size > $this->maxSizeFile*1024){
             $this->error=14;
            return 0;
        }

    return 1;
    }

    /**
    * start upload
    */
    function upload(){
        if ($this->error!==0) return 0;
        if (!$this->getTestFileAllow()) return 0;
        if ($this->auto_create_dir && !is_dir($this->dir)) mkdir($this->dir, $this->new_dir_access);
        if ($this->file_name_md5) $this->name = $this->renameFileMd5($this->name);
        if ($this->file_name_filter) $this->name = $this->renameFileFilter($this->name);
        if ($this->auto_rename_file) $this->name = $this->renameExistingFile($this->dir, $this->name);
        $this->uploaded_real_name_file = $this->name;
        if (move_uploaded_file($this->tmp_name, $this->dir."/".$this->name)) {
            $this->file_upload_ok=1;
            @chmod($this->dir."/".$this->name, 0777);
            return 1;
        }else{
            $this->file_upload_ok=0;
            $this->error=13;
            return 0;
        }
    }

    function getErrorUploadMsg($key){
        $errors = array(
            0 => 'File Upload Finished',
            1 => 'File Error size ini',
            2 => 'UPLOAD_ERR_FORM_SIZE',
            3 => 'UPLOAD_ERR_PARTIAL',
            4 => 'UPLOAD_ERR_NO_FILE (file not upload)',
            11 => 'File not allow',
            12 => 'File forbid',
            13 => 'File copy Error',
            14 => 'File Error size class',
            15 => 'Error array $_FILES',
            16 => 'filesize > post_max_size'
        );
        return $errors[$key];
    }
}
?>