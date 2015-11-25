<?php
	class CSQL_Predicate 
	{
		private $queries;

		public function __construct($column,$operator,$search) {
			$query = $this->generate_query_string($column,$operator,$search);

			$this->$queries = array('root' => $query);
		}

		public function add_comparator($column,$operator,$search,$mode) {
			if($mode == '&&')
				$mode = 'AND';
			if($mode == '||')
				$mode == 'OR';

			if($mode != 'AND' && $mode != 'OR')
			{
				trigger_error("Invalid mode specified for adding comparator.");
			}

			$query = $this->generate_query_string($column,$operator,$search);

			$querytemp = array($mode => $query);

			arra
		}

		public function where_string() {

		}

		private function generate_query_string($column,$operator,$search) {
			$search = mysqli_escape_string($search);

			if(is_numeric($search)) {
				$query = "`$column` $operator $search";
			} else {
				$query = "`$column` $operator '$search'";
			}

			return $query;
		}
	}