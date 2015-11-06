<?php
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