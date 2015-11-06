<?php
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