<?php
	session_start();
	if (!isset($_SESSION['agent']) OR ($_SESSION['agent'] != md5($_SERVER ['HTTP_USER_AGENT']) )) {

		require ('includes/login_functions.inc.php');
		redirect_user();

	}
	
	$page_title = 'Add or Delete Cards';
	include ('includes/header.html');
	require('includes/test_input.inc.php');
?>

<h1>Add or Delete Cards</h1>
<form action="" method="post" >
	<br/>
	<table align="center" cellspacing="8">
  	<tr>
    	<td>Card number:</td>
    	<td><input type="text" name="cno" size="20" maxlength="7" required="required" <?php if(!empty($_POST['cno'])) echo 'value="'.test_input($_POST['cno']).'"'; ?>/></td>
  	</tr>
  	<tr>
  	  	<td>Name:</td>
		<td><input type="text" name="name" size="20" maxlength="10" <?php if(!empty($_POST['name'])) echo 'value="'.test_input($_POST['name']).'"'; ?>/></td>
  	</tr>
  	<tr>
    	<td>Department:</td>
    	<td><input type="text" name="department" size="20" maxlength="40" <?php if(!empty($_POST['department'])) echo 'value="'.test_input($_POST['department']).'"'; ?>/></td>
  	</tr>
    <tr>
    	<td>Type:</td>
    	<td>
        <select name="type">
        <?php
			$type=array('T','G','U','O');
			foreach($type as $value){
				echo  "<option value=\"$value\"";
				if(isset($_POST['type'])&&$_POST['type']==$value)
					echo 'selected="selected"';
				echo ">$value</option>";
			}
		?>
            </select></td>
  	</tr>
  	<tr>
    	<td>Operation:</td>
    	<td><input type="radio" name="operation" value="D" required="required" <?php if(!empty($_POST['operation'])&&$_POST['operation']=='D') echo 'checked="checked"'; ?>/>&nbsp;delete&nbsp;&nbsp;
        <input type="radio" name="operation" value="A" required="required" <?php if(!empty($_POST['operation'])&&$_POST['operation']=='A') echo 'checked="checked"'; ?>/>&nbsp;add </td>
  	</tr>
  	<tr>
    	<td colspan="2" align="center"><input type="submit" name="submit" value="Submit" /></td>
  	</tr>
	</table>
</form>

<?php
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$cno=test_input($_POST['cno']);
		$name=test_input($_POST['name']);
		$department=test_input($_POST['department']);
		$type=test_input($_POST['type']);
		$operation=test_input($_POST['operation']);
		$errors=array();
		require ('mysqli_connect.php');
		
		if(empty($cno)) $errors[]='Card number is not filled.';
		if(empty($operation)) $errors[]='Operation is not filled.';
		
		if(empty($errors)&&$operation=='D'){//删除
			$query="select * from card where cno='".mysqli_real_escape_string($dbc, $cno)."'";
			$r = mysqli_query ($dbc, $query);
			$num = mysqli_num_rows($r);
			mysqli_free_result ($r);
			if($num==1){//卡已存在
				$query="select * from borrow where cno='".mysqli_real_escape_string($dbc, $cno)."' and return_date is null";
				$r = mysqli_query ($dbc, $query);
				$num = mysqli_num_rows($r);
				mysqli_free_result ($r);
				if($num==0){//没有未归还图书
					$query="delete from borrow where cno='".mysqli_real_escape_string($dbc, $cno)."'";
					mysqli_query ($dbc, $query);
					$query="delete from card where cno='".mysqli_real_escape_string($dbc, $cno)."'";
					mysqli_query ($dbc, $query);
					echo "<h1>Result</h1><p>The card($cno) is deleted successfully.</p><br/>\n";
					mysqli_close($dbc);
					include ('includes/footer.html');
					exit();
				}
				else{//有未归还图书
					$errors[]='This card has not returned all books.';
				}
			}
			else{//卡不存在
				$errors[]='The card number does not exist.';
			}
		}
		
		if(empty($errors)&&$operation=='A'){//添加
			if(empty($name)) $errors[]='Name is not filled.';
			if(empty($department)) $errors[]='Department is not filled.';
			if(empty($type)) $errors[]='Type is not filled.';
			if(empty($errors)){//信息齐全
				$query="select * from card where cno='".mysqli_real_escape_string($dbc, $cno)."'";
				$r = mysqli_query ($dbc, $query);
				$num = mysqli_num_rows($r);
				mysqli_free_result ($r);
				if($num==1){//卡已存在
					$errors[]='The card number has existed.';
				}
				else{//卡不存在
					$query="insert into card values('".mysqli_real_escape_string($dbc, $cno)."','".mysqli_real_escape_string($dbc, $name)."','".mysqli_real_escape_string($dbc, $department)."','".mysqli_real_escape_string($dbc, $type)."')";
					mysqli_query ($dbc, $query);
					echo "<h1>Result</h1><p>The card($cno) is registered successfully.</p><br/>\n";
					mysqli_close($dbc);
					include ('includes/footer.html');
					exit();
				}
			}
		}
		
		mysqli_close($dbc);
		
		if (!empty($errors)) {
			echo '<h1>Error!</h1><p class="error">The following error(s) occurred:<br />';
			foreach ($errors as $msg) {
				echo " - $msg<br />\n";
			}
			echo '</p><p>Please try again.</p>';
		}
	}
?>

<?php
include ('includes/footer.html');
?>