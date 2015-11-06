<?php
	class CSQL_Sort {
		public $column;
		public $ascending;

		public function __construct($column,$ascending) {
			$this->ascending = $ascending;
			$this->column = $column;
		}
	}