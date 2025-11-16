<?php 
/** 
 * Tools in mysql 
 */ 
class mysql_api_code 
{ 
    private $db; 
 
    function __construct($db) 
    { 
        $this->db = $db; 
    } 
    /** 
     * Write in col in mysql 
     * @param string $dir name the folder 
     * @param string $file name the file 
     * @param string $values value 
     * @return mysql_result|false  
     * @see https://t.me/api_tele 
     */ 
    public function sql_write($dir, $values) 
    { 
        mysqli_query($this->db, "SET NAMES utf8mb4"); 
        mysqli_query($this->db, "SET CHARACTER SET utf8mb4"); 
        if ($this->db) { 
            return mysqli_query($this->db, "INSERT INTO $dir $values"); 
        } else { 
            return false; 
        } 
    } 
    /** 
     * Read from the col in mysql 
     * @param string $dir name the folder 
     * @param string $file name the file 
     * @return array|false  
     * @see https://t.me/api_tele 
     */ 
    public function sql_read($dir, $file) 
    { 
        mysqli_query($this->db, "SET NAMES utf8mb4"); 
        mysqli_query($this->db, "SET CHARACTER SET utf8mb4"); 
        if ($this->db) { 
            $re = mysqli_query($this->db, "SELECT * FROM $dir"); 
            $a = array(); 
            if (true) { 
                while ($read = mysqli_fetch_assoc($re)) { 
                    $a[] = $read[$file]; 
                } 
            } 
            return $a; 
        } else { 
            return false; 
        } 
    }

    public function sql_readarray($dir) 
    { 
        mysqli_query($this->db, "SET NAMES utf8mb4"); 
        mysqli_query($this->db, "SET CHARACTER SET utf8mb4"); 
        if ($this->db) { 
            $re = mysqli_query($this->db, "SELECT * FROM $dir"); 
            $arr = mysqli_fetch_all($re, MYSQLI_ASSOC);
            return $arr; 
        } else { 
            return false; 
        } 
    }
    public function sql_readarray_count($dir) 
    { 
        mysqli_query($this->db, "SET NAMES utf8mb4"); 
        mysqli_query($this->db, "SET CHARACTER SET utf8mb4"); 
        if ($this->db) { 
            $re = mysqli_query($this->db, "SELECT * FROM $dir"); 
            $arr = mysqli_fetch_all($re, MYSQLI_ASSOC);
            return count($arr); 
        } else { 
            return false; 
        } 
    }
    /** 
     * Edit value from col in mysql 
     * @param string $dir name the folder 
     * @param string $file name the file 
     * @return array|false  
     * @see https://t.me/api_tele 
     */ 
    /**
    * @param string $dir : الجدول
    * @param string $file : اسم العمود المراد تغييره
    * @param string $new : القيمة الجديدة
    * 
    *
    */
    public function sql_edit($dir, $file, $new, $where, $where_value) 
    {   
        mysqli_query($this->db, "SET NAMES utf8mb4"); 
        mysqli_query($this->db, "SET CHARACTER SET utf8mb4"); 
        if ($this->db) { 
            return mysqli_query($this->db, "UPDATE $dir SET $file='$new' WHERE $where='$where_value' "); 
        } else { 
            return false; 
        } 
    } 
    /** 
     * Delete value from col in mysql 
     * @param string $dir name the folder 
     * @param string $file name the file 
     * @param string $value the value  
     * @return  mysqli_result|false  
     * @see https://t.me/api_tele 
     */ 
    public function sql_del($dir, $file, $value) 
    { 
        mysqli_query($this->db, "SET NAMES utf8mb4"); 
        mysqli_query($this->db, "SET CHARACTER SET utf8mb4"); 
        if ($this->db) { 
            return mysqli_query($this->db, "DELETE FROM $dir WHERE $file = '$value' "); 
        } else { 
            return false; 
        } 
    } 
    /** 
     * Create table in mysql 
     * @param string $name name the table 
     * @param array|null $names_col name col in table 
     * @return array|false  
     * @see https://t.me/api_tele 
     */ 
    public function sql_create_table($name, $names_col = []) 
    { 
        mysqli_query($this->db, "SET NAMES utf8mb4"); 
        mysqli_query($this->db, "SET CHARACTER SET utf8mb4"); 
        if ($this->db) { 
            $ct = "CREATE TABLE $name ("; 
            foreach ($names_col as $name_col) { 
                $ct .= $name_col . ","; 
            } 
            $ct .= ");"; 
            return mysqli_query($this->db, $ct); 
        } else { 
            return false; 
        } 
    } 
    /** 
     * Add col in table 
     * @param string $name_T name the table 
     * @param string $name_col name the col in table 
     * @return array|false  
     * @see https://t.me/api_tele 
     */ 
    public function sql_add_col($name_T, $name_col) 
    { 
        mysqli_query($this->db, "SET NAMES utf8mb4"); 
        mysqli_query($this->db, "SET CHARACTER SET utf8mb4"); 
        if ($this->db) { 
            return mysqli_query($this->db, "ALTER TABLE $name_T ADD $name_col;"); 
        } else { 
            return false; 
        } 
    }

    public function sql_select($dir, $where, $is) 
    { 
        // $dir name of table
        mysqli_query($this->db, "SET NAMES utf8mb4"); 
        mysqli_query($this->db, "SET CHARACTER SET utf8mb4"); 
        if ($this->db) { 
            $re = mysqli_query($this->db, "SELECT * FROM $dir WHERE $where='$is'"); 
            $arr = mysqli_fetch_all($re, MYSQLI_ASSOC);
            if(count($arr) > 0){
                return $arr[0]; 
            }else{
                return false;
            }
            
        } else { 
            return false; 
        } 
    }

    public function sql_select_all($dir, $where, $is) 
    { 
        // $dir name of table
        mysqli_query($this->db, "SET NAMES utf8mb4"); 
        mysqli_query($this->db, "SET CHARACTER SET utf8mb4"); 
        if ($this->db) { 
            $re = mysqli_query($this->db, "SELECT * FROM $dir WHERE $where='$is'"); 
            $arr = mysqli_fetch_all($re, MYSQLI_ASSOC);
            if(count($arr) > 0){
                return $arr;
            }else{
                return false;
            }
            
        } else { 
            return false; 
        } 
    }

    public function sql_count($dir, $where, $is) 
    { 
        // $dir name of table
        mysqli_query($this->db, "SET NAMES utf8mb4"); 
        mysqli_query($this->db, "SET CHARACTER SET utf8mb4"); 
        if ($this->db) { 
            $re = mysqli_query($this->db, "SELECT * FROM $dir WHERE $where='$is'"); 
            $arr = mysqli_fetch_all($re, MYSQLI_ASSOC);
            return count($arr); 
        } else { 
            return 0;
        } 
    }
     
}

include("./config.php");
$sql = new mysql_api_code($db);

?>