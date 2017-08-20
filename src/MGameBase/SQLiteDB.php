<?php
/*
注意细节:实例化对象时传入的是数据库的路径，要是数据库不存在的话会自动创建。
*/
namespace MGameBase;
class SQLiteDB extends \SQLite3{
	
    public $url;
		
    public function __construct($url){
        $this->url=$url;
		$this->ok = true;
		try{
			$this->open($url);
		}catch(\Exception $e1){
			$this->ok = false;
		}catch(\Error $e2){
			$this->ok = false;
		}
    }
	
	public function success(){
		return $this->ok;
	}
	
	
    public static function  check_input($value){
		$ok = $value;
        if (get_magic_quotes_gpc()) {
            $ok = sqlite_escape_string($value);
        }
		if($ok !== $value){
			 return "Gamebase".$ok;
		}else{
			return $value;
		}
       
    }
	
	public static function check_output($value){ //TODO
		if(strpos($value, "Gamebase") <= 2){
			return substr($value, strpos($value, 8));
		}
		return $value;
	}
	
	//    create(market, ["id" => "INTEGER PRIMARY KEY"], null)
    public function create($table,$name,$type=null){
		if($this->exist($table))
			return false;
        $sql = 'CREATE TABLE '.$table.'(';
        if($type==null){
            $arrname = array_keys($name);
            $arrtype = array_values($name);
        }else{
            $arrname = explode("|", $name);
            $arrtype = explode("|", $type);
        }
        for($i=0;$i<count($arrname);$i++){
            if($i==count($arrname)-1){
                $sql = $sql.$arrname[$i]."   ".$arrtype[$i]."";
            }else{
                $sql = $sql.$arrname[$i]."   ".$arrtype[$i].",";
            }
             
        }
        $sql = $sql.');';
        $re = $this->query($sql);
        if($re){
            return true;
        }else{
            return false;
        }
    }
	
    
    public function exist($tableName){
    //检测表格是否存在
        $strSql="SELECT COUNT(name) as Rows FROM sqlite_master WHERE type='table' and name='$tableName' ";       
        $dbResults= $this->query($strSql);
        $row = $dbResults->fetchArray(SQLITE3_ASSOC);
        
        if ($row["Rows"]==1)
            return true;
        else
            return false;
    }	
	
    public function drop($table){
        $sql = 'DROP TABLE '.$table.';';
        $re = $this->query($sql);
        if($re){
            return true;
        }else{
            return false;
        }
    }
	
	
    public function insert($table,$name,$value=null){
        $sql = "INSERT INTO ".$table.'(';
        if($value == null){
        $arrname = array_keys($name);
        $arrvalue = array_values($name);
        }else{
        $arrname = explode('|', $name);
        $arrvalue = explode('|', $value);
        }
        for($i=0;$i<count($arrname);$i++){
            if($i==count($arrname)-1){
                $sql = $sql.$arrname[$i];
            }else{
                $sql = $sql.$arrname[$i].",";
            }
        }
        $sql = $sql.")VALUES(";
        for($i=0;$i<count($arrvalue);$i++){
            if($i==count($arrvalue)-1){
                $sql = $sql."'".$arrvalue[$i]."'";
            }else{
                $sql = $sql."'".$arrvalue[$i]."',";
            }
        }
        $sql .=");";
        $re = $this->query($sql);
        if($re){
            return true;
        }else{
            return false;
        }
    }
	
	//   null时 单条记录   非null时 多条
    public function del($table,$Conditionsname,$Conditionsvalue=null){
        if($Conditionsvalue!=null){
            $sql = "DELETE FROM ".$table." WHERE ".$Conditionsname."='".$Conditionsvalue."';";
        }else{
            $sql = "DELETE FROM ".$table." WHERE ";
            $arrname = array_keys($Conditionsname);
            $arrvalue = array_values($Conditionsname);
            for($i=0;$i<count($arrname);$i++){
                if($i==count($arrname)-1){
                    $sql.=$arrname[$i].'='."'".$arrvalue[$i]."'";
                }else{
                    $sql.=$arrname[$i].'='."'".$arrvalue[$i]."',";
                }
            }
            $sql.=';';
        }
        $re = $this->query($sql);
        if($re){
            return true;
        }else{
            return false;
        }
    }
	
	
    public function select($table,$name,$Conditionsname,$Conditionsvalue=null){
        if($Conditionsvalue!=null){
            $sql = "SELECT ".$name." FROM ".$table." WHERE ".$Conditionsname."=".$Conditionsvalue.";";
        }else{
            $sql = "SELECT ".$name." FROM ".$table." WHERE ";
            $arrname = array_keys($Conditionsname);
            $arrvalue = array_values($Conditionsname);
            for($i=0;$i<count($arrname);$i++){
                if($i==count($arrname)-1){
                    $sql.=$arrname[$i].'='."'".$arrvalue[$i]."'";
                }else{
                    $sql.=$arrname[$i].'='."'".$arrvalue[$i]."' and ";
                }
            }
            $sql.=';';
        }
        $ret = $this->query($sql);
		$return = [];
        while($row = $ret->fetchArray(SQLITE3_ASSOC)){
			array_push($return, $row[$name]);
		}
		if(count($return) <= 1){
			return array_shift($return);
		}else{
			return $return;
		}
    }
	
    public function update($table,$name,$value = null,$Conditionsname,$Conditionsvalue=null){
		if($value != null){
			if($Conditionsvalue!=null){
				$sql = "UPDATE ".$table." SET ".$name."= '".$value."' WHERE ".$Conditionsname."='".$Conditionsvalue."';";
			}else{
				$sql = "UPDATE ".$table." SET ".$name."= '".$value."' WHERE ";
				$arrname = array_keys($Conditionsname);
				$arrvalue = array_values($Conditionsname);
				for($i=0;$i<count($arrname);$i++){
					if($i==count($arrname)-1){
						$sql.=$arrname[$i].'='."'".$arrvalue[$i]."'";
					}else{
						$sql.=$arrname[$i].'='."'".$arrvalue[$i]."' and ";
					}
				}
				$sql.=';';
			}
			$re = $this->query($sql);
			if($re){
				return true;
			}else{
				return false; 
			}
		}else{
			$sql = "UPDATE ".$table."SET ";
			$arrname = array_keys($name);
			$arrvalue = array_values($name);
			for($i=0;$i<count($arrname);$i++){
				if($i==count($arrname)-1){
					$sql.=$arrname[$i].'='."'".$arrvalue[$i]."'";
				}else{
					$sql.=$arrname[$i].'='."'".$arrvalue[$i]."', ";
				}
			}
			if($Conditionsvalue != null){
				$sql .= " WHERE ".$Conditionsname."='".$Conditionsvalue."';";
			}else{
				$sql .= " WHERE ";
				$arrname = array_keys($Conditionsname);
				$arrvalue = array_values($Conditionsname);
				for($i=0;$i<count($arrname);$i++){
					if($i==count($arrname)-1){
						$sql.=$arrname[$i].'='."'".$arrvalue[$i]."'";
					}else{
						$sql.=$arrname[$i].'='."'".$arrvalue[$i]."' and ";
					}
				}
				$sql.=';';
			}
			$re = $this->query($sql);
			if($re){
				return true;
			}else{
				return false; 
			}
		}
    }
	
	
    public function group($table,$name){
        $sql = "SELECT ".$name." FROM ".$table.";";
        $return = array();
        $ret = $this->query($sql);
        while($row = $ret->fetchArray(SQLITE3_ASSOC) ){
            array_push($return, $row[$name]);
        }
        return $return;
    }
	
	
    public function fetchall($sql){
        $return = array();
        $ret = $this->query($sql);
        while($row = $ret->fetchArray(SQLITE3_ASSOC)){
            array_push($return, $row);
        }
        return $return;
    }
	
    public function vague_select($table,$name,$Conditionsname,$Conditionsvalue=null){
        if($Conditionsvalue!=null){
            $sql = "SELECT ".$name." FROM ".$table." WHERE ".$Conditionsname."like '%".$Conditionsvalue."%';";
        }else{
            $sql = "SELECT ".$name." FROM ".$table." WHERE ";
            $arrname = array_keys($Conditionsname);
            $arrvalue = array_values($Conditionsname);
            for($i=0;$i<count($arrname);$i++){
                if($i==count($arrname)-1){
                    $sql.=$arrname[$i].'like '."'%".$arrvalue[$i]."%'";
                }else{
                    $sql.=$arrname[$i].'like '."'%".$arrvalue[$i]."%' and ";
                }
            }
            $sql.=';';
        }
        $ret = $this->query($sql);
		$return = [];
        while($row = $ret->fetchArray(SQLITE3_ASSOC)){
			array_push($return, $row[$name]);
		}
		if(count($return) <= 1){
			return $row[$name];
		}else{
			return $return;
		}
    }
	
	public function get_last_id($table){
		$sql = "SELECT last_insert_rowid() FROM ".$table.";";
        $ret = $this->query($sql);
        $row = $ret->fetchArray(SQLITE3_ASSOC);
		if($row == false){
			return null;
		}
        return $row["id"];
	}
	
	
}
?>