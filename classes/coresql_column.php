<?php
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