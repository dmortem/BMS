<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	require ('includes/login_functions.inc.php');
	require ('mysqli_connect.php');
	
	list ($check, $data) = check_login($dbc, $_POST['mgrID'], $_POST['password']);
	
	if ($check) {
		
		session_start();
		$_SESSION['mgrID'] = $data['mgrID'];
		$_SESSION['name'] = $data['name'];
		
		$_SESSION['agent'] = md5($_SERVER['HTTP_USER_AGENT']);

		redirect_user('loggedin.php');
			
	} else {

		$errors = $data;

	}
		
	mysqli_close($dbc); 

}

require('includes/test_input.inc.php');
include ('includes/login_page.inc.php');
?>