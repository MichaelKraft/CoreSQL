<?php
	class CoreSQL {
		private $connection;
		private $resultCache;
		private $tables;

		public function __construct($credentials) {
			if(get_class($credentials) != 'CoreSQL_Configuration') {
				trigger_error("Wrong type for configuration object, please use CoreSQL_Configuration.");
				exit();
			} else {
				if($credentials->hostname == '') {
					$credentials->hostname = 'localhost';
				}

				if($credentials->username == '') {
					trigger_error('No username specified.');
					exit();
				} else if($credentials->password == '') {
					trigger_error('No password specified.');
					exit();
				} else if($credentials->database == '') {
					trigger_error('No database specified.');
					exit();
				} else {
					$connection = mysqli_connect($credentials->hostname,$credentials->username,$credentials->password,$credentials->database);

					if($connection) {
						$this->connection = $connection;
					} else {
						trigger_error(mysqli_error($connection));
						exit();
					}
				}
			}
			$this->tables = array();
		}

		public function define_table($name, $columns) {
			$table = new CSQL_Table($name,$this->connection);
			array_push($this->tables, $table);
			if(!$table->is_defined) {
				$table->columns = $columns;
				$table->initialize($this->connection);
			}
		}

		public function drop_table($name) {
			$query = "DROP TABLE $name";
			mysqli_query($this->connection, $query);
			if(mysqli_error($this->connection)) {
				trigger_error(mysqli_error($this->connection));
				exit();
			} else {
				return true;
			}
		}

		public function query_table($table_name, $predicates, $ascending, $limit) {
			$found = false;
			foreach ($this->tables as $table) {
				if($table->name == $table_name) {
					$found = true;
				}
			}

			if($found) {

			} else {
				trigger_error("Requested table $table_name not found");
				exit();
			}
		}

		public function store_values($table_name,$new_values) {
			$temp = $this->convert_results(mysqli_query($this->connection,"SHOW KEYS FROM `$table_name` WHERE Key_name = 'PRIMARY'"));
			$primary_key = $temp['Column_name'];
			
			if(array_key_exists($primary_key, $new_values)) {
				// update
				$query = "UPDATE `$table_name` SET ";
				foreach ($new_values as $key => $value) {
					if($key != $primary_key) {
						if(is_numeric($value)) {
							$query = $query . "$key=$value,";
						} else {
							$query = $query . "$key='$value',";
						}
					}
				}
				$sort = $new_values[$primary_key];
				$query = substr($query, 0, -1) . " WHERE $primary_key=$sort";
				mysqli_query($this->connection, $query);
				if(mysqli_error($this->connection)) {
					trigger_error(mysqli_error($this->connection));
					exit();
				} else {
					return true;
				}
			} else {
				// insert
				$columns = '(';
				$values = '(';
				foreach ($new_values as $key => $value) {
					$columns = $columns . "$key,";
					if(is_numeric($value)) {
						$values = $values . "$value,";
					} else {
						$values = $values . "\"$value\",";
					}
				}
				$columns = substr($columns, 0, -1) . ')';
				$values = substr($values, 0, -1) . ')';
				$query = "INSERT INTO `$table_name` $columns VALUES $values;";
				mysqli_query($this->connection, $query);
				if(mysqli_error($this->connection)) {
					trigger_error(mysqli_error($this->connection));
					exit();
				} else {
					return mysqli_insert_id($this->connection);
				}
			}
		}

		private function convert_results($results) {
			$output = array();
			$output = (mysqli_fetch_assoc($results));
		    return $output;
		}
	}

	class CSQL_Table {
		public $name;
		public $is_defined;
		public $columns;

		public function __construct($name,$connection) {
			// test if the table exists

			$result = mysqli_query($connection, "SHOW TABLES LIKE '$name'");

			$this->name = $name;
			$this->is_defined = ($result->num_rows == 1);
		}

		public function initialize($connection) {
			$query = "CREATE TABLE `$this->name`(" . PHP_EOL;
			$idcolumn = '';
			foreach ($this->columns as $column) {
				if(get_class($column) != 'CSQL_Table_Column') {
					trigger_error("Wrong type for table column, please use CSQL_Table_Column.");
				exit();
				}
				$null = ' NOT NULL';

				if($column->allow_null == true) {
					$null = '';
				}
				if($column->id) {
					$idcolumn = $column;
					$null = ' NOT NULL AUTO_INCREMENT';
				}
				$query = $query . "$column->name $column->type $column->size $null," . PHP_EOL;
			}
			if($idcolumn == '') {
				$query = $query . "ID INT NOT NULL AUTO_INCREMENT," . PHP_EOL . "PRIMARY KEY (ID)" . PHP_EOL;
			} else {
				$query = $query . "PRIMARY KEY ($idcolumn->name)" . PHP_EOL;
			}
			$query = $query . ');';
			
			mysqli_query($connection, $query);
			if(mysqli_error($connection))
			{
				trigger_error(mysqli_error($connection));
				exit();
			}
		}
	}

	class CSQL_Table_Column	{
		public $name;
		public $type;
		public $id;
		public $allow_null;
		public $size;

		public function __construct($name,$type,$size = 0,$id = false,$allow_null = true)
		{
			$test = strtolower($type);
			if($test != 'int' && $test != 'text' && $test != 'varchar' && $test != 'date') {
				trigger_error('Invalid column type specified');
				exit();
			}

			$this->name = $name;
			$this->type = $type;
			$this->id = $id;
			$this->allow_null = $allow_null;

			if($size == 0)
			{
				$this->size = '';
			}
			else
			{
				$this->size = "($size)";
			}
		}
	}

	class CSQL_Predicate {
		public $column;
		public $search;
		public $operator;

		public function __construct($column,$operator,$search) {
			$this->column = $column;
			$this->search = $search;
			$this->operator = $operator;
		}
	}

	class CSQL_Sort {
		public $column;
		public $ascending;

		public function __construct($column,$ascending) {
			$this->ascending = $ascending;
			$this->column = $column;
		}
	}

	class CoreSQL_Configuration	{
		public $hostname;
		public $username;
		public $password;
		public $database;
	}