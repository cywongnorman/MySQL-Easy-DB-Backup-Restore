<?php
/**
* @package		EasyBackupRestore.PHP
* @author 		Christian Rosandhy
* @link			www.tianrosandhy.com
* @version 		v1.0.0
* @license		https://opensource.org/licenses/MIT
* @copyright	Copyright (c) 2017, www.tianrosandhy.com
*/

Class EasyBackupRestore{
	var $db; 
	var $table_list; //nama-nama tabel
	var $table_desc; //deskripsi jenis tabel (TABLE/VIEW)

	public function __construct($dbname, $user, $pass, $hostname="localhost"){
		//koneksi database sengaja diduplikat, agar tetap bisa berjalan untuk pengguna dengan engine database yang lain
		$db = new PDO('mysql:host='.$hostname.';dbname='.$dbname.';charset=utf8',$user,$pass);
		$this->db = $db;

		//listing nama tabel di database
		$sql = $this->db->query("SHOW FULL TABLES");
		$list = array();
		foreach($sql->fetchAll() as $row){
			$list[] = $row[0];
			$desc[$row[0]] = $row[1];
		}
		$this->table_list = $list;
		$this->table_desc = $desc;
	}


	public function backup($backuptype=null, $loc=null){
		#Bila output berupa file, maka hasil method ini adalah file yang siap didownload (diusahakan format SQL saja)
		if(strlen($loc) == 0)
			$filename = "Backup-".date("YmdHis").".sql";
		else
			$filename = $loc;


		$list = $this->table_list;
		$output = "";
		if(count($list) > 0){
			foreach($list as $tb){
				$type = $this->table_desc[$tb];
				if($type == "BASE TABLE")
					$type = "TABLE";
				else if($type == "VIEW")
					$type = "VIEW";
				else{
					//selain jenis tabel dan view diskip saja dulu
					//silakan dikembangkan kalau ada ide di bagian ini
					continue;
				}

				$sql = $this->db->query("SHOW CREATE $type $tb");
				$row = $sql->fetch();
				//pembuatan script struktur tabel
				$output .= "DROP $type IF EXISTS $tb;\n";
				$output .= $row[1].";\n\n";


				//pembuatan script input data
				//hanya dilakukan untuk table jenis BASE TABLE
				if($this->table_desc[$tb] <> "VIEW"){
					$get = $this->db->query("SELECT * FROM $tb");
					$num = $get->columnCount();

					$output .= "INSERT INTO $tb VALUES\n";
					$get_arr = array();
					foreach($get as $r){
						$ins_arr = array();
						for($i=0; $i<$num; $i++){
							if(is_int($r[$i]))
								$ins_arr[] = $r[$i];
							else
								$ins_arr[] = $this->db->quote($r[$i]);
						}
						$get_arr[] = "(".implode($ins_arr, ", ").")";
					}
					$output .= implode($get_arr, ", \n").";\n\n";
				}

			}
		}
		else{
			exit("Error : tidak ada tabel yang dipilih");
		}


		if(is_null($backuptype)){
			$location = $filename;
			$file = fopen($location, "w");
			fwrite($file, $output);
			fclose($file);
			return $location;
		}
		else{
			return $output;
		}
	}



	public function restore($file){
		//sip
		$get = fopen($file, "r");
		$content = fread($get, filesize($file));

		//pecah query setiap ada tanda petik
		$pecah = explode(";", $content);
		$n = count($pecah);
		for($i=0; $i<$n; $i++){
			$txt = trim($pecah[$i]);
			if(strlen($txt) == 0)
				unset($pecah[$i]);
			else
				$pecah[$i] = $txt;
		}

		//jalankan masing-masing query yang sudah ditrim
		foreach($pecah as $sql){
			 $this->db->exec($sql);
		}

		return true;
	}




	public function empty(){
		$list = $this->table_list;
		$output = "";
		if(count($list) > 0){
			foreach($list as $tb){
				$sql = $this->db->query("TRUNCATE $tb");
			}
		}
		else{
			exit("Error : tidak ada tabel yang dipilih");
		}
		return true;
	}

}