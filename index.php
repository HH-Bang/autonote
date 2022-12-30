<?php
//error_reporting( E_ALL );
//ini_set( "display_errors", 1 );

//session_save_path( $_SERVER['DOCUMENT_ROOT']."/session" );
session_start();
require_once( $_SERVER['DOCUMENT_ROOT']."/config/config.php" );
require_once( $_SERVER['DOCUMENT_ROOT']."/lib/autoload.php" );

use lib\func_class as comm_func;

$setClass = new comm_func;
$setClass->Load_controllers();
