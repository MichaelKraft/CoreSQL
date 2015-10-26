<?php
	require_once('coresql.php');

	$config = new CoreSQL_Configuration;

	$config->username = 'coresqltest';
	$config->password = 'hSY-Qny-zD9-HW2';
	$config->database = 'coresqltest';

	$coresql = new CoreSQL($config);
	
	$coresql->define_table('test_create',array(
			new CSQL_Table_Column('ID','INT',64,true,false),
			new CSQL_Table_Column('name','TEXT'),
			new CSQL_Table_Column('savedtext','VARCHAR',255),
		));

	$predicate = new CSQL_Predicate('name','=','test');
	$coresql->query_table('test_create',array(),true,10);

