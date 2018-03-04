<?php

$page_title = 'Login';
include ('includes/header.html');

if (isset($errors) && !empty($errors)) {
	echo '<h1>Error!</h1>
	<p class="error">The following error(s) occurred:<br />';
	foreach ($errors as $msg) {
		echo " - $msg<br />\n";
	}
	echo '</p><p>Please try again.</p>';
}

?><h1>Login</h1>
<form action="" method="post" >
	<br/>
	<table align="center" cellspacing="8">
    <tr>
		<td>Manager ID:</td>
        <td><input type="text" name="mgrID" size="20" maxlength="60" required="required" value="<?php if(!empty($_POST['mgrID'])) echo test_input($_POST['mgrID']); ?>"/></td>
    </tr>
    <tr>
		<td>Password:</td>
    	<td><input type="password" name="password" size="20" maxlength="20" required="required" /></td>
    </tr>
    <tr>
		<td colspan="2" align="center"><input type="submit" name="submit" value="Login" /></td>
    </tr>
    </table>
</form>

<?php include ('includes/footer.html'); ?>