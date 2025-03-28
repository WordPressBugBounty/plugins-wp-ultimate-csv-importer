<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class ValidateFile {
    private static $validate_instance = null;

    public static function getInstance() {
		
		if (ValidateFile::$validate_instance == null) {
			ValidateFile::$validate_instance = new ValidateFile;
			return ValidateFile::$validate_instance;
		}
		return ValidateFile::$validate_instance;
    }


    /**
	 * Checks whether given file is in supported format.
	 * @param  string $filename - file name
	 * @return string
	 */
    public function validate_file_format($filename){
        $supported_file = array('csv' , 'xml', 'zip' , 'txt' , 'xlsx' , 'xls', 'tsv');
        $extension = explode(".", $filename);
        $file_extension = end($extension);
        if(empty($file_extension)){
            $file_extension = 'xml';
        }
        if(!in_array($file_extension , $supported_file)){       
            $message = "Unsupported File Format";    
        }else{
            $message = "yes";
        }
        return $message;
    }


    /**
	 * Validates the uploaded file for certain requirements.
	 * @param  string $filepath - path to file
     * @param  string $file_extension - extension of file
	 * @return string
	 */
    public function file_validation($file_path , $file_extension){
       
            $get_file = file_get_contents($file_path);                 
            $get_memory_limit = ini_get('memory_limit');
            if (preg_match('/^(\d+)(.)$/', $get_memory_limit, $matches)) {
                if ($matches[2] == 'M') {
                    $get_memory_limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
                } else if ($matches[2] == 'K') {
                    $get_memory_limit = $matches[1] * 1024; // nnnK -> nnn KB
                }
            }
            
            $uploaded_filesize = filesize($file_path);      
            $size_limit = 5242880;
            $memory_limit = 419430400;
            if(($uploaded_filesize > $size_limit) && ($get_memory_limit < $memory_limit)){
                $message = "Please increase your php memory limit";
                return $message;  
            }

            if(function_exists('mb_check_encoding')) {
                $utf_format = mb_check_encoding($get_file, 'UTF-8');

                if(!$utf_format){    
                    $message = "Your file is not in UTF-8 format";
                    return $message; 
                }
                
            }

                $get_post_max_size = $this->get_config_bytes(ini_get('post_max_size'));
                if($uploaded_filesize > $get_post_max_size){
                    $message = "File size exceeded post maximum size";
                    return $message;
                }
                
                $get_php_size = $this->get_config_bytes(ini_get('upload_max_filesize'));  
                if($uploaded_filesize > $get_php_size){
                    $message = "File size exceeded PHP maximum size";
                    return $message;
                }
                else{
            
                    if($file_extension == 'csv' || $file_extension == 'txt'){
                        $delimiter = $this->getFileDelimiter($file_path, 5);
                        $validate_csv = $this->validateCSV($file_path , $delimiter);
                        $message = "yes";
                    }else{
                        $message = "yes";
                    }
                    return $message;
                }    
            
    }


    /**
	 * Converts filesize to bytes.
	 * @param  string $val - path to file
	 * @return int
	 */
    public function get_config_bytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $intval = intval(trim($val));
        switch ($last) {
                case 'g':
                        $intval *= 1024;
                case 'm':
                        $intval *= 1024;
                case 'k':
                        $intval *= 1024;
        }
        return $intval;
    }

    /**
	 * Validates csv and txt files.
	 * @param  string $file_path 
     * @param  string $delimiter 
	 * @return string
	 */
    public function validateCSV($file_path='', $delimiter=','){
        
        if(!file_exists($file_path) || !is_readable($file_path))
            return FALSE;
            $header = array();
            $data = array();
            if($delimiter == '\t'){
                $delimiter = '~';
                if (($f_handle = fopen($file_path, 'r')) !== FALSE){
                    $temp=$file_path.'temp';
                    $temphandle =fopen($temp, 'w+');
                    chmod($temp, 0777);
               
                    while (($line = fgets($f_handle)) !== false) {
                        $line= str_replace("\t", '~', $line);
                        fwrite($temphandle, $line);
                    }
                    fclose($temphandle);
                    if (($handles = fopen($temp, 'r')) !== FALSE){
                        while (($row = fgetcsv($handles, 0, $delimiter)) !== FALSE)
                        {
                            if(!$header)
                                $header = $row;
                            else{    
                                $data[] = array_combine($header, $row);
                                break;
                            }    
                        }  
                    
                        $handle = fopen($file_path, 'r');   
                        if(array_key_exists(null,$data[0])){
                            $valid = 'No';
                        }
                        else{
                            $valid = 'Yes';
                        }
                        if(empty($data[0])){
                            $valid = 'No';
                        }
                    }
                // fclose($handle);
                return $valid;
                   
                }
                fclose($temphandle);
            }
            else{
                if (($handle = fopen($file_path, 'r')) !== FALSE)
                {
                    while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE)
                    {
                        
                        if(!$header)
                            $header = $row;
                        else{    
                            $data[] = array_combine($header, $row);
                            break;
                        }      
                    }   
                    $handle = fopen($file_path, 'r');   
                    if(array_key_exists(null,$data[0])){
                        $valid = 'No';
                    }else{
                        $valid = 'Yes';
                    }
                    if(empty($data[0])){
                        $valid = 'No';
                    }
                fclose($handle);    
            }
            return $valid;
        }
        
    }


    /**
	 * Possible delimiters.
	 * @param  string $file 
     * @param  int $checkLines
	 * @return string
	 */
    public function getFileDelimiter($file, $checkLines = 2){
		$file = new \SplFileObject($file);
		$delimiters = array(
		  ',',
		  '\t',
		  ';',
		  '|',
		  ':',
		);
		$results = array();
		$i = 0;
		 while($file->valid() && $i < 1){
		    $line = $file->fgets();
		    foreach ($delimiters as $delimiter){
			$regExp = '/['.$delimiter.']/';
			$fields = preg_split($regExp, $line);
			if(count($fields) > 1){
			    if(!empty($results[$delimiter])){
				$results[$delimiter]++;
			    } else {
				$results[$delimiter] = 1;
			    }
			}
		    }
		   $i++;
		}
		if(!empty($results)){
            $results = array_keys($results, max($results));
            return $results[0];
        }
        else{
            return true;
        }

    }


    /**
	 * Converts bytes to KB, MB, GB.
	 * @param  int $bytes 
	 * @return string
	 */
    public function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
	}


    /**
	 * Gets post types, selected post type.
	 * @param string $hashkey 
     * @param string $filename
	 * @return string
	 */
    public function import_record_function($hashkey , $filename){

        $result = array();
        $extension_instance = new ExtensionHandler;
        $get_post = $extension_instance->get_import_post_types();
        $get_taxo = get_taxonomies();
        $import_record_post = array_keys($get_post);
        $import_record_taxonomy = array_keys($get_taxo);
        $get_type = $extension_instance->set_post_types($hashkey , $filename);
        $result['Post Type'] = $import_record_post;
        $result['Taxonomy'] = $import_record_taxonomy;
        $result['selected type'] = $get_type;
    
        return $result;
    }
    
}