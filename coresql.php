<?php
	// fetch components
	require_once('classes/coresql_configuration.php');
	require_once('classes/coresql_table.php');
	require_once('classes/coresql_column.php');
	require_once('classes/coresql_predicate.php');
	require_once('classes/coresql_sort.php');

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
