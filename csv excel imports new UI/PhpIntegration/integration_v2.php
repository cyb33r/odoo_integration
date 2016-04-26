<?php
	require_once "../Xmlrpc/ripcord.php";

    class Integration
	{
		################# DECLARATIONS #################

		private static $url;
		private static $dbName;
	    private static $username;
		private static $password;
		private static $uid;
		private static $odooModels;
	
		################# PUBLIC METHODS #################
		
		public static function login($url,$dbName,$username,$password)
		{
			$result = false;
			self::$url = $url;
			self::$dbName = $dbName;
			self::$username = $username;
			self::$password = $password;
			
			$common = ripcord::client($url . "/xmlrpc/2/common");
			$common->version();
			self::$uid = $common->authenticate($dbName, $username , $password, array());
			self::$odooModels = ripcord::client($url . "/xmlrpc/2/object");

			if(self::success(self::$uid))
			{
				$result = true;
			}
			else
			{
				$result = false;
			}
			
			return $result;
		}
		
		public static function readable($model)
		{
			$readable = self::execute_method($model,'check_access_rights',array('read'),array('raise_exception'=>0));

			if($readable == 1)
			{
				return true;
			}
			else
			{
				return false;
			}
		}	

		public static function writeable($model)
		{
			$writeable = self::execute_method($model,'check_access_rights',array('write'),array('raise_exception'=>0));

			if($writeable == 1)
			{
				return true;
			}
			else
			{
				return false;
			}
		}	

		public static function search_record($model,$condition,$mapping)
		{
			return self::execute_method($model,'search',array($condition),$mapping);
		}	
				
		public static function read_record($model,$fields,$condition,$mapping)
		{
			$id = self::search_record($model,$condition,$mapping);
			return self::execute_method($model,'read',array($id),array('fields'=>$fields));	 
		}

		public static function model_fields_list($model)
		{
			return self::execute_method($model,'fields_get',array(),array('attributes' => array('string', 'help', 'type')));
		}
		
		public static function search_and_read_records($model,$fields,$condition,$mapping)
		{
			/*
			echo "<pre>";
			echo $model;
			print_r($fields);
			print_r($condition);
			echo "</pre>";*/
			$fieldsArray = array();
			array_push($fieldsArray,array('fields'=>$fields));
			$condAndMapArray = array_merge($fieldsArray[0],$mapping);
			
			return self::execute_method($model,'search_read',array($condition),$condAndMapArray,array());
		}

		public static function create_record($model,$values)
		{
			echo $model;
			echo "<pre>";
			print_r($values);
			echo "</pre>";
			return self::execute_method($model,'create',array($values),array());
		}
		
		public static function update_record($model,$id,$values)
		{
			$paramsArray = array();			
			if(is_array($id))
			{
				array_push($paramsArray,$id);
			}
			else
			{
				array_push($paramsArray,array($id));
			}
			
			array_push($paramsArray,$values);
			return self::execute_method($model,'write',$paramsArray,array());
		}		

		public static function get_updated_code($model,$id)
		{
			return self::execute_method($model,'name_get',array(array($id)),array());
		}

		public static function delete_record($model,$id)
		{
			$paramsArray = array();	
			if(is_array($id))
			{
				array_push($paramsArray,$id);
			}
			else
			{
				array_push($paramsArray,array($id));
			}
			
			return self::execute_method($model,'unlink',$paramsArray,array());
		}

		public static function success($id)
		{
			$result = false;
			if(is_array($id))
			{
				$result = false;
			}
			else
			{
				if($id > 0)
				{
					$result = true;
				}
				else
				{
					$result = false;
				}
			}

			return $result;
		}

		public static function success_read($result)
		{
			if(!empty($result))
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		################# PRIVATE METHODS #################

		private static function execute_method($model,$method,$params,$mapping)
		{
			return self::$odooModels->execute_kw(self::$dbName,self::$uid,self::$password,$model,$method,$params,$mapping);
		}

	}
	
?>