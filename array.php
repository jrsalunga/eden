<?php //-->
/*
 * This file is part of the Eden package.
 * (c) 2009-2011 Christian Blanquera <cblanquera@gmail.com>
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

require_once dirname(__FILE__).'/class.php';
require_once dirname(__FILE__).'/type/abstract.php';
require_once dirname(__FILE__).'/type/error.php';
require_once dirname(__FILE__).'/string.php';

/**
 *
 * @package    Eden
 * @category   registry
 * @author     Christian Blanquera <cblanquera@gmail.com>
 * @version    $Id: registry.php 1 2010-01-02 23:06:36Z blanquera $
 */
class Eden_Array extends Eden_Type_Abstract implements ArrayAccess, Iterator {
	/* Constants
	-------------------------------*/
	/* Public Properties
	-------------------------------*/
	/* Protected Properties
	-------------------------------*/
	protected static $_preMethods = array(
		'array_change_key_case',	'array_chunk',
		'array_combine',			'array_count_datas',
		'array_diff_assoc',			'array_diff_key',
		'array_diff_uassoc',		'array_diff_ukey',
		'array_diff',				'array_fill_keys',
		'array_filter',				'array_flip',
		'array_intersect_assoc',	'array_intersect_key',
		'array_intersect_uassoc',	'array_intersect_ukey',
		'array_intersect',			'array_keys',
		'array_merge_recursive',	'array_merge',
		'array_pad',				'array_push',
		'array_reverse',			'array_shift',
		'array_slice',				'array_splice',
		'array_sum',				'array_udiff_assoc',
		'array_udiff_uassoc',		'array_udiff',
		'array_uintersect_assoc',	'array_uintersect_uassoc',
		'array_uintersect',			'array_unique',
		'array_datas',				'count',
		'current',					'each',
		'end',						'extract',
		'in_array',					'key',
		'next',						'prev',
		'sizeof');
	
	protected static $_postMethods = array(
		'array_fill', 		'array_map', 
		'array_search', 	'compact');
	
	protected static $_referenceMethods = array(
		'array_unshift', 	'array_walk_recursive', 
		'array_walk', 		'arsort',
		'asort',			'krsort',
		'ksort', 			'natcasesort',
		'natsort',			'reset',
		'rsort', 			'shuffle',
		'sort',				'uasort',
		'uksort',			'usort');
	
	/* Private Properties
	-------------------------------*/
	/* Get
	-------------------------------*/
	public static function get(array $data = array()) {
		return self::_getMultiple(__CLASS__, $data);
	}
	
	/* Magic
	-------------------------------*/
	public function __construct(array $value = array()) {
		parent::__construct($value);
	}
	
	/* Public Methods
	-------------------------------*/
	/**
	 * Removes a row in an array and adjusts all the indexes
	 *
	 * @param *array the array
	 * @param string the key to leave out
	 * @param bool is this a value or key value array?
	 * @return this
	 */
	public function cut($arrayKey, $isValueArray = true) {
		//argument 1 must be a string, argument 2 must be a boolean
		Eden_Error_Validate::get()->argument(0, 'string')->argument(1, 'bool');
		
		$array = array();
		foreach($this->_data as $key => $value) {
			//if the key does not equal 
			//the key to to leave out
			if($arrayKey != $key)
			{
				//we want to add it to the new array
				if($isValueArray)
				{
					$array[] = $value;
				}
				else
				{
					$array[$key] = $value;
				}	
			}
		}
		
		$this->_data = $array;  
		
		return $this;
	}
	
	/**
	 * Inserts a row in an array after the given index and adjusts all the indexes
	 *
	 * @param *string the key we are looking for to past after
	 * @param *mixed the value to paste
	 * @param string the key to paste along with the value
	 * @return this
	 */
	public function paste($afterKey, $arrayValue, $arrayKey = NULL) {
		//argument 1 must be a string, argument 3 must be a string or null
		Eden_Error_Validate::get()->argument(0, 'string')->argument(2, 'string', 'null');
		
		$array = array();
		foreach($this->_data as $key => $value) {
			//if the array key is not null
			//we know they ment to make this
			//array an key value array
			if(!is_null($arrayKey)) {
				//lets add it
				$array[$key] = $value;
				//if the current key is the key we
				//want to add the new value after
				if($afterKey == $key) {
					//lets add the new value
					$array[$arrayKey] = $arrayValue;
				}
			} else {
				//lets add it
				$array[] = $value;
				//if the current key is the key we
				//want to add the new value after
				if($afterKey == $key) {
					//lets add the new value
					$array[] = $arrayValue;
				}
			}
		}
		$this->_data = $newArray; 
		
		return $this;
	}
	
	/**
	 * Converts a multidimensional array with similar rows to an accociate array
	 *
	 * @param *array the table to key
	 * @param *string the name of the key we are looking for
	 * @return this
	 */
	public function associateTable($key, $many = false) {
		//argument 1 must be a string, argument 2 must be a boolean
		Eden_Error_Validate::get()->argument(0, 'string')->argument(1, 'bool');
		
		$table = array();
		foreach($this->_data as $i => $row) {
			$newKey = isset($row[$key]) ? $row[$key] : $i;
			
			if($many) {
				$table[$newKey][] = $row;	
			} else {
				$table[$newKey] = $row;
			}
		}
		
		$this->_data = $table;
		
		return $this;
	}
	
	/**
	 * Returns a list of values of a given column
	 *
	 * @param *string the key to look for
	 * @param string the value of the key that 
	 *  needs to be returned
	 * @return mixed
	 */
	public function getKeyValue($key, $index = NULL) {
		//argument 1 must be a string, argument 2 must be a string or null
		Eden_Error_Validate::get()->argument(0, 'string')->argument(1, 'string', 'null');
		
		$table = $this->associateTable($table, $key);
		
		if(is_null($index)) {
			return Eden_Array::get(array_keys($table));
		}
		
		return isset($table[$index]) ? $table[$index] : false;
	}
	
	/**
	 * Sorts a table
	 *
	 * @param *string sort field
	 * @param string order
	 * @param int start
	 * @param int range
	 * @return array
	 */
	public function paginate($field, $order = 'ASC', $start = 0, $range = 0) {
		Eden_Error_Validate::get()
			->argument(0, 'string') //argument 1 must be a string
			->argument(1, 'string') //argument 2 must be a string
			->argument(2, 'number') //argument 3 must be a number
			->argument(3, 'number'); //argument 4 must be a number
		
		if($start < 0) {
			$start = 0;
		}
		
		if($range < 0) {
			$range = 0;
		}
		
		//first lets take the field column
		//and put it in a new array
		$keyList = array();
		foreach($this->_data as $row) {
			//if the table is inconsistent
			if(!isset($row[$field])) {
				//don't do anyhting more
				return $this;
			}
			
			$keyList[] = $row[$field];
		}
		
		//lets now sort this column
		//keeping the key value relation
		//intact
		if($order == 'DESC') {
			arsort($keyList);
		} else {
			asort($keyList);
		}
		
		//lastly we want to create a 
		//new array and put in each row
		//by order of the column sort
		//if there is a row with the same
		//column value it doesn't really
		//matter which one goes first
		$table = array();
		$startI = 0;
		$rangeI = 0;
		//parse key list
		foreach( $keyList as $index => $sortField ) {
			//if the start is less 
			//than the increment start
			if((int) $start <= $startI ) {
				//if a range is defined and 
				//the increment range is less than that
				if((int) $range && $rangeI < (int) $range) {
					//we will add it
					$table[] = $this->_data[$index];
					$rangeI ++;
				//if range equals 0 we assume 
				//that we need to get all of it
				} else if($range == 0) {
					$table[] = $this->_data[$index];
				}
			}
			$startI ++;
		}
		
		$this->_data = $table;
		
		return $this;
	}
	
	/**
	 * Rewinds the position
	 * For Iterator interface
	 *
	 * @return void
	 */
	public function rewind() {
        reset($this->_data);
    }

	/**
	 * Returns the current item
	 * For Iterator interface
	 *
	 * @return void
	 */
    public function current() {
        return current($this->_data);
    }

	/**
	 * Returns th current position
	 * For Iterator interface
	 *
	 * @return void
	 */
    public function key() {
        return key($this->_data);
    }

	/**
	 * Increases the position
	 * For Iterator interface
	 *
	 * @return void
	 */
    public function next() {
        next($this->_data);
    }

	/**
	 * Validates whether if the index is set
	 * For Iterator interface
	 *
	 * @return void
	 */
    public function valid() {
        return isset($this->_data[$this->key()]);
    }
	
	/**
	 * Sets data using the ArrayAccess interface
	 *
	 * @param number
	 * @param mixed
	 * @return void
	 */
	public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->_data[] = $value;
        } else {
            $this->_data[$offset] = $value;
        }
    }
	
	/**
	 * isset using the ArrayAccess interface
	 *
	 * @param number
	 * @return bool
	 */
    public function offsetExists($offset) {
        return isset($this->_data[$offset]);
    }
    
	/**
	 * unsets using the ArrayAccess interface
	 *
	 * @param number
	 * @return bool
	 */
	public function offsetUnset($offset) {
        unset($this->_data[$offset]);
    }
    
	/**
	 * returns data using the ArrayAccess interface
	 *
	 * @param number
	 * @return bool
	 */
	public function offsetGet($offset) {
        return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
    }
	
	/* Protected Methods
	-------------------------------*/
	protected function _getPreMethod($name) {
		if(in_array($name, self::$_preMethods)) {
			return $name;
		}
		
		if(in_array('array_'.$name, self::$_preMethods)) {
			return 'array_'.$name;
		}
		
		return false;
	}
	
	protected function _getPostMethod($name) {
		if(in_array($name, self::$_postMethods)) {
			return $name;
		}
		
		if(in_array('array_'.$name, self::$_postMethods)) {
			return 'array_'.$name;
		}
		
		return false;
	}
	
	protected function _getReferenceMethod($name) {
		if(in_array($name, self::$_postMethods)) {
			return $name;
		}
		
		if(in_array('array_'.$name, self::$_postMethods)) {
			return 'array_'.$name;
		}
		
		return false;
	}
	
	/* Private Methods
	-------------------------------*/
}