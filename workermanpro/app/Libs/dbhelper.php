<?php
namespace system\model;

	class dbhelper
	{
		private $isConnected = false;
		private static  $obj = null;
		static private $conInfo = array();
		static private $_fileds = array();
		static private $_limit = "";
		static private $_order = "";
		static private $_group = "";
		static private $_update = "";
		static private $_data = array();
		private static $_con;
		private static $_table;
		public function __clone()
		{
			trigger_error('禁止克隆dbhelper对象！',E_USER_ERROR);
		}
		private function __construct()
		{
			//echo 'the construct of dbhelper<br/>';
		}
		public static function getInstance($table='')
		{
			if(!$table)
				trigger_error('M()的参数不能 为空，您必须指定一个表名！',E_USER_ERROR);
			if(!dbhelper::$obj instanceof dbhelper)
			{
				  $con = include(dirname(__FILE__).'/../Data/config.php');
				  $conHandle=@mysql_connect($con['host'],$con['user'],$con['password']);
				  if(!$conHandle)
				  {
				  	trigger_error('连接数据库失败，请检查数据库配置文件！',E_USER_ERROR);
				  }
				  if(!@mysql_select_db($con['database'],$conHandle))
				  {
				  	trigger_error('连接数据库成功，但选择数据库失败，请检查您所配置的指定数据库是否存在或可访问！',E_USER_ERROR);
				  }
				  dbhelper::$_con = $conHandle;
				  dbhelper::$obj = new dbhelper();
			}
			dbhelper::$_table = $table;
			return dbhelper::$obj;
		}
		public  function printMsg()
		{
			//echo 'The print func of dbhelper!<br/>';
			//var_dump(dbhelper::$conInfo);
		}
		public function __call($func, $args) {  
			if (in_array($func, array('from', 'join', 'where'))) { 
			dbhelper::$conInfo[$func] = $args;  
			return dbhelper::$obj; 
			}else 
			{
				$func = strtolower($func);//将所有函数名，转换为小写
				switch($func)
				{
					case 'field':
						{
							dbhelper::$_fileds = $args;
							return dbhelper::$obj;
						}	 
					break;
					case 'limit':
					{
						//echo '调用了limit ';
						dbhelper::$_limit = ' limit ';
						if(is_array($args))
						{
							//echo '参数是数组<br/>';
							if(is_array($args[0]))
							{
								//echo 'limit数组传参！';
								//var_dump($args[0]);
								$count = count($args[0]);
								switch($count)
								{
									case 1:
										if(!is_string($args[0][0]))
											dbhelper::$_limit .='0,'.$args[0][0];
										else
											dbhelper::$_limit .=$args[0][0];
									break;
									case 2:
									if(!is_string($args[0][0]))
											dbhelper::$_limit .=(string)$args[0][0].','.(string)$args[0][1];
										else
											dbhelper::$_limit .=$args[0][0];
									break;
									default:
										trigger_error('limit()方法只接受1个或2个参数！',E_USER_ERROR);
									break;
								}
							}else if(is_string($args[0]))
							{
								//echo 'limit字符串传参！';
								dbhelper::$_limit .=$args[0];
							}else if(is_numeric($args[0]))
							{
								//echo 'limit 数字传参';
								if(is_array($args))
								{
									$count = count($args);
									//echo 'limit count '.$count;
									switch($count)
									{
										case 1:
											dbhelper::$_limit .=$args[0].' ';
										break;
										case 2:
											dbhelper::$_limit .=$args[0].','.$args[1].' ';
										break;
										default:
											trigger_error($func.'()方法参数数量为1或2',E_USER_ERROR);
										break;
									}
								}
								//var_dump($args);
								
							}
						}
						//var_dump($args);
						return dbhelper::$obj;
					}
					break;
					case 'order':
					{
						//echo '调用了order方法 ';
						dbhelper::$_order = 'ORDER BY ';
						if(empty($args[0]))
						{
							trigger_error($func.'order()方法不接受空参数！',E_USER_ERROR);
							//break;
						}
						if(is_array($args[0]))
						{
							//数组传参
							//echo 'order 数组传参！';
							$count = count($args[0]);
							$i=0;
							foreach($args[0] as $key => $val)
							{
								if(is_string($key))
									dbhelper::$_order.=$key.' '.$val.' ';
								else
									dbhelper::$_order.=$val.' ';
								if($i!=$count-1)
									dbhelper::$_order.=',';
								$i++;
							}
							
						}else if(is_string($args[0]))
						{
							//echo 'order 字符串传参！';
							dbhelper::$_order.=$args[0];
						}else{
							trigger_error($func.'order()方法只支持字符串和数组传参！',E_USER_ERROR);
						}
						return dbhelper::$obj;
					}
					break;
					case 'group':
					{
						//echo '调用了group方法 ';
						dbhelper::$_group = 'GROUP BY ';
						if(empty($args[0]))
						{
							trigger_error($func.'group()方法不接受空参数！',E_USER_ERROR);
							//break;
						}
						if(is_array($args[0]))
						{
							//数组传参
							//echo 'group 数组传参！';
							$count = count($args[0]);
							$i=0;
							foreach($args[0] as $key => $val)
							{
								if(is_string($key))
									dbhelper::$_group.=$val.' ';
								else
									trigger_error($func.'group()方法只支持字符串和字符串数组传参！',E_USER_ERROR);;
								if($i!=$count-1)
									dbhelper::$_group.=',';
								$i++;
							}
							
						}else if(is_string($args[0]))
						{
							//echo 'group 字符串传参！';
							dbhelper::$_group.=$args[0].' ';
						}else{
							trigger_error($func.'group()方法只支持字符串和字符串数组传参！',E_USER_ERROR);
						}
						return dbhelper::$obj;
					}
					break;
					case 'table':
						if((!is_string($args[0])) || empty($args[0]))
						{
							trigger_error('table()方法的参数只能是非空的字符串！',E_USER_ERROR);
						}
						dbhelper::$_table = $args[0];
						return dbhelper::$obj;
					break;
					default:
						trigger_error($func.'方法未定义！',E_USER_ERROR);
					break;
					
				}
				
			}
			
		}
		public function add($args='')
		{
			if(empty($args))
			{
				if(empty(dbhelper::$_data))
				{
					trigger_error('add()方法不存在任何可添加的数据！',E_USER_ERROR);
				}
				
			}else{
				$this->data($args);
			}
			if(!is_array(dbhelper::$_data))
				trigger_error('add()参数或绑定的数据只能是一维数组！',E_USER_ERROR);
			$strHead = 'INSERT INTO '.dbhelper::$_table.' ';
			$strColumn = ' ( ';
			$strValue = ' ( ';
			$strBody = ' VALUES ';
			$count = count(dbhelper::$_data);
			$i = 0;
			foreach(dbhelper::$_data as $key => $val)
			{
				$strColumn.=$key;
				if(is_string($val))
				{
					$strValue.="'".$val."'";
				}else
					$strValue.=$val;
				if($i!=$count-1)
					{
						$strColumn.=',';
						$strValue.=',';
					}	
				$i++;
			}
			$strColumn .= ' )';
			$strValue .= ' )';
			$strSQL = $strHead.$strColumn.$strBody.$strValue;
			//echo 'your SQL: '.$strSQL;
			$result = @mysql_query($strSQL,dbhelper::$_con);
			if($result == false)
			{
				trigger_error('MakePHP Error:执行SQL语句'.$strSQL.'出错！错误信息：'.@mysql_error(),E_USER_ERROR);
			}
			if($result == true)
			{
				$ret = true;
			}
			dbhelper::clearArgs();//完成查询，清除缓存中的所有参数
			return $ret;
		}
		public function data($args)
		{
			dbhelper::$_data = $args;
			return dbhelper::$obj;
		}
		public function addAll()
		{
			return dbhelper::$obj;
		}
		public function select()
		{
			$sql_head = 'select * from ';
			if(!empty(dbhelper::$_fileds))
			{
				$fields='';
				if(is_array(dbhelper::$_fileds[0]))
				{
					//echo 'count field:'.count(dbhelper::$_fileds);
					$count = count(dbhelper::$_fileds[0]);
					for($i=0;$i<$count;$i++)
					{
						$fields.=dbhelper::$_fileds[0][$i];
						if($i!=$count-1)
							$fields.=',';
					}
				}else{
					$fields=dbhelper::$_fileds[0];
				}
				$sql_head = 'select '.$fields.' from ';
				//echo '指定了字段！<br/>';
			}
			$sql_body =dbhelper::$_table.' ';
			foreach(dbhelper::$conInfo as $key => $val)
			{
				//echo $key;
				//var_dump($val);
				$strValue='';
				if(is_array($val[0]))
				{
					//如果是数组传参
					$cVal=count($val[0]);
					$it = 0;
					foreach($val[0] as $val_key => $val_value)
					{
//					    echo $val_key."\n";
//					    echo $val_value."\n";
						if(is_string($val_key))
							{
								if(is_string($val_value))
									$val_value ="'".$val_value."'";
								else if($val_value === false)
                                    $val_value =' false ';
                                else if($val_value === true)
                                    $val_value =' true ';
								$strValue.=' '.$val_key.' = '.$val_value;
							}else
							{
							    if($val_value === false)
                                    $strValue.=' false ';
							    else if($val_value === true)
                                    $strValue.=' true ';
							    else
								    $strValue.=' '.$val_value.' ';
							}
							if($it !=$cVal-1)
								$strValue.=' and ';
							$it++;
					}
				}else
					$strValue = $val[0];
				$sql_body.=$key.' '.$strValue.' ';
			}
			
			//检查是否group
			if(dbhelper::$_group!="")
			{
				$sql_body.=dbhelper::$_group;
			}	
			//检查是否order
			if(dbhelper::$_order!="")
			{
				$sql_body.=dbhelper::$_order;
			}	
			//检查是否limit
			if(dbhelper::$_limit!="")
			{
				$sql_body.=dbhelper::$_limit;
			}
			$sql=$sql_head.$sql_body;
			//echo 'Your SQL: '.$sql.'<br/>';
			$result = mysql_query($sql,dbhelper::$_con);
			if($result == false)
			{
				trigger_error('MakePHP Error:执行SQL语句'.$sql.'出错！',E_USER_ERROR);
			}
			$ret = array();
			while($reData= mysql_fetch_assoc($result))
			{
				if($reData == false)
				{
					$reData = array();
				}
				array_push($ret,$reData);
				//var_export($reData);
			}
			//var_dump($ret);
			dbhelper::clearArgs();//完成查询，清除缓存中的所有参数
			return $ret;
		}
		private function isPrimaryKey($key)
		{
			$bRet = false;
			
			return $bRet;
		}
		public function update($args='')
		{
			if(empty($args))
			{
				trigger_error('MakePHP Error: Update()方法不接受空参数！',E_USER_ERROR);
			}
			dbhelper::$_update  ="UPDATE ".dbhelper::$_table." set ";
			if(is_array($args))
			{
				//echo 'update 数组传参！';
				$i = 0;
				$count = count($args);
				foreach($args as $key => $val)
				{
					dbhelper::$_update.=$key.' = '.$val.' ';
					if($i!=$count-1)
						dbhelper::$_update.=', ';
					$i++;
				}
				foreach(dbhelper::$conInfo as $key => $val)
				{
					//echo $key;
					$strValue='';
					if(is_array($val[0]))
					{
						//如果是数组传参
						$cVal=count($val[0]);
						$it = 0;
						foreach($val[0] as $val_key => $val_value)
						{
							if(is_string($val_key))
							{
								if(is_string($val_value))
									$val_value ="'".$val_value."'";
								$strValue.=' '.$val_key.' = '.$val_value;
							}else
							{
								$strValue.=' '.$val_value.' ';
							}
							if($it !=$cVal-1)
								$strValue.=' and ';
							$it++;
						}
					}else
						$strValue = $val[0];
					
					dbhelper::$_update.=$key.' '.$strValue.' ';
				}
				
			}else if(is_string($args))
			{
				dbhelper::$_update.=' '.$args.' ';
			}else
			{
				trigger_error('MakePHP Error: Update()方法只支持数组或字符串类型的参数！',E_USER_ERROR);
			}
				//echo 'YOUR SQL: '.dbhelper::$_update;
				$result = mysql_query(dbhelper::$_update,dbhelper::$_con) or die(mysql_error());//不能写成mysql_query($query);
				$update_rows = mysql_affected_rows();
				//echo '更新条数：'.$update_rows;
				if($result == false)
				{
					trigger_error('MakePHP Error:执行SQL语句'.$sql.'出错！',E_USER_ERROR);
				}
				$ret = array();
				if(is_array($result))
				{
					while($reData= mysql_fetch_array($result))
					{
						if($reData == false)
						{
							$reData = array();
						}
						array_push($ret,$reData);
			
						//var_export($reData);
					}
					//var_dump($ret);
					
				}else
				{
					if($result==true)
					{
						//echo '更新记录成功！';
						$ret = $update_rows;
					}
				}
				return $ret;
		}
		public function delete()
		{
			$sql_head = 'delete from ';
			
			$sql_body =dbhelper::$_table.' ';
			foreach(dbhelper::$conInfo as $key => $val)
			{
				//echo $key;
				//var_dump($val);
				$strValue='';
				if(is_array($val[0]))
				{
					//如果是数组传参
					$cVal=count($val[0]);
					$it = 0;
					foreach($val[0] as $val_key => $val_value)
					{
						if(is_string($val_key))
							{
								if(is_string($val_value))
									$val_value ="'".$val_value."'";
								$strValue.=' '.$val_key.' = '.$val_value;
							}else
							{
								$strValue.=' '.$val_value.' ';
							}
							if($it !=$cVal-1)
								$strValue.=' and ';
							$it++;
					}
				}else
					$strValue = $val[0];
				
				$sql_body.=$key.' '.$strValue.' ';
			}
			//检查是否limit
			if(dbhelper::$_limit!="")
			{
				$sql_body.=dbhelper::$_limit;
			}
			//检查是否group
			if(dbhelper::$_group!="")
			{
				$sql_body.=dbhelper::$_group;
			}	
			//检查是否order
			if(dbhelper::$_order!="")
			{
				$sql_body.=dbhelper::$_order;
			}	
			
			$sql=$sql_head.$sql_body;
			//echo 'Your SQL: '.$sql.'<br/>';
			$result = mysql_query($sql,dbhelper::$_con) or die(mysql_error());//不能写成mysql_query($query);
			$del_rows = mysql_affected_rows();
			//echo '删除条数：'.$del_rows;
			if($result == false)
			{
				trigger_error('MakePHP Error:执行SQL语句'.$sql.'出错！',E_USER_ERROR);
			}
			$ret = array();
			if(is_array($result))
			{
				while($reData= mysql_fetch_array($result))
				{
					if($reData == false)
					{
						$reData = array();
					}
					array_push($ret,$reData);
		
					//var_export($reData);
				}
				//var_dump($ret);
				
			}else
			{
				if($result==true)
				{
				//	echo '删除记录成功！';
					$ret = $del_rows;
				}
			}
			
			dbhelper::clearArgs();//完成查询，清除缓存中的所有参数
			
			return $ret;
		}
		public function execute($sql)
		{
			return mysql_query($sql);
		}
		static private function clearArgs()
		{
			//清除缓存的参数
			dbhelper::$conInfo = array();
			dbhelper::$_fileds = array();
			dbhelper::$_limit = "";
			dbhelper:: $_order = "";
			dbhelper::$_group = "";
			dbhelper::$_update = "";
		}
	}
?>