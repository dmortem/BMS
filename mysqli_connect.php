<?php

DEFINE ('DB_USER', 'root');
DEFINE ('DB_PASSWORD', '');
DEFINE ('DB_HOST', 'localhost');
DEFINE ('DB_NAME', 'bms');

$dbc = @mysqli_connect (DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) OR die ('Could not connect to MySQL: ' . mysqli_connect_error() );

mysqli_set_charset($dbc, 'utf8');

//以下注释代码用于向数据库系统注册管理员用户：
 //$r = @mysqli_query ($dbc, "insert into manager values('3140100333',SHA1('123456'),'Wu Chiyu','123')");
 //