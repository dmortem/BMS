<?php
	session_start();
	if (!isset($_SESSION['agent']) OR ($_SESSION['agent'] != md5($_SERVER ['HTTP_USER_AGENT']) )) {

		require ('includes/login_functions.inc.php');
		redirect_user();

	}
	
	$page_title = 'Borrow&amp;Return';
	include ('includes/header.html');
	require('includes/test_input.inc.php');
?>

<h1>Borrow&amp;Return</h1>
<form action="" method="post" >
	<br/>
	<table align="center" cellspacing="8">
    <tr>
		<td>Card number:</td>
        <td><input type="text" name="cno" size="20" maxlength="7" required="required" <?php if(!empty($_POST['cno'])) echo 'value="'.test_input($_POST['cno']).'"'; ?>/></td>
    </tr>
    <tr>
		<td>Book number:</td>
    	<td><input type="text" name="bno" size="20" maxlength="8" <?php if(!empty($_POST['bno'])) echo 'value="'.test_input($_POST['bno']).'"'; ?>/></td>
    </tr>
    <tr>
    	<td>
		<label for="operation">Operation:</label>
        </td>
        <td>
    	<input type="radio" name="operation" value="B" <?php if(!empty($_POST['operation'])&&$_POST['operation']=='B') echo 'checked="checked"'; ?>/>borrow&nbsp;&nbsp;
        <input type="radio" name="operation" value="R" <?php if(!empty($_POST['operation'])&&$_POST['operation']=='R') echo 'checked="checked"'; ?>/>return 
        </td>
    </tr>
    <tr>
		<td colspan="2" align="center"><input type="submit" name="submit" value="Submit" /></td>
    </tr>
    </table>
</form>

<?php
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		require ('mysqli_connect.php');
		$errors=array();
		$cno=mysqli_real_escape_string($dbc, trim($_POST['cno']));
		$bno=mysqli_real_escape_string($dbc, trim($_POST['bno']));
		if(!empty($_POST['cno'])){//检查卡号
			$query="select * from card where cno='".$cno."'";
			$r = mysqli_query ($dbc, $query);
			if(mysqli_num_rows($r)!=1)
				$errors[]='The card number does not exist.';
			mysqli_free_result ($r);
		}
		if(!empty($_POST['bno'])){//检查书号
			$query="select * from book where bno='".$bno."'";
			$r = mysqli_query ($dbc, $query);
			if(mysqli_num_rows($r)!=1)
				$errors[]='The book number does not exist.';
			mysqli_free_result ($r);
		}
		if(!empty($errors)){//有错误
			echo '<h1>Error!</h1>
				<p class="error">The following error(s) occurred:<br />';	
			foreach ($errors as $msg) {
				echo " - $msg<br />\n";
			}
			echo '</p><p>Please try again.</p>';
			include ('includes/footer.html');
			mysqli_close($dbc);
			exit();
		}
		
		
		if(!empty($_POST['cno'])&&(empty($_POST['bno'])||!isset($_POST['operation']))){//查询
			$query="select * from book where bno in (select bno from borrow where cno='".$cno."' and return_date is null) order by bno asc";
			$r = mysqli_query ($dbc, $query);
			$num = mysqli_num_rows($r);
			echo '<h1>Books not returned</h1>';
			if($num>0){//有记录
				echo "<p>There are $num records.</p><br/>\n";
				echo '<table align="center" cellspacing="3" cellpadding="3" width="100%">
	<tr align="left"><td><b>Book number</b></td><td><b>Category</b></td><td><b>Title</b></td><td><b>Press</b></td><td><b>Year</b></td><td><b>Author</b></td><td><b>Price</b></td><td><b>Total</b></td><td><b>Number</b></td></tr>
';

				while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
					echo '<tr align="left"><td>' . $row['bno'] . '</td><td>' . $row['category'] .  '</td><td>' . $row['title'] .'</td><td>' . $row['press'] .'</td><td>' . $row['year'] .'</td><td>' . $row['author'] .'</td><td>' . $row['price'] .'</td><td>' . $row['total'] .'</td><td>' . $row['stock'] .'</td></tr>';
				}
				
				echo '</table>';
				mysqli_free_result ($r);
			}
			else{//无记录
				echo '<p class="error">No records.</p>';
			}
			mysqli_close($dbc);
			include ('includes/footer.html');
			exit();	
		}
		
		if(!empty($_POST['cno'])&&!empty($_POST['bno'])&&$_POST['operation']=='B'){//借书
				$query="select stock from book where bno='".$bno."'";
				$r = mysqli_query ($dbc, $query);
				$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
				if($row['stock']==0)
					$errors[]='All copies of this book are not available.';
				mysqli_free_result ($r);
				echo '<h1>Result</h1>';
				if(empty($errors)){//有余量
					$query="insert into borrow values('".$cno."','".$bno."','".date('Y-m-d')."',null,'".$_SESSION['mgrID']."')";
					mysqli_query ($dbc, $query);
					$query="update book set stock=stock-1 where bno='".$bno."'";
					mysqli_query ($dbc, $query);
					echo '<p>Congratulations! You can take it now.</p>';
				}
				else{//无余量
					$query="select borrow_date from borrow where return_date is null and bno='".$bno."' order by borrow_date asc";
					$r = mysqli_query ($dbc, $query);
					$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
					$Date_List=explode("-",$row['borrow_date']);
					$time=mktime(0,0,0,$Date_List[1],$Date_List[2],$Date_List[0]);
					$date=date('Y-m-d',$time+24*3600*30);
					$errors[]='You can borrow this book no earlier than '.$date.'.';
					mysqli_free_result ($r);
					echo '<p class="error">';	
					foreach ($errors as $msg) {
						echo " - $msg<br />\n";
					}
				}
				include ('includes/footer.html');
				mysqli_close($dbc);
				exit();
		}
		
		if(!empty($_POST['cno'])&&!empty($_POST['bno'])&&$_POST['operation']=='R'){//还书
				$query="select * from borrow where cno='".$cno."' and bno='".$bno."' and return_date is null";
				$r = mysqli_query ($dbc, $query);
				$num = mysqli_num_rows($r);
				mysqli_free_result ($r);
				if($num==0){//未借
					echo '<h1>Result</h1><p class="error">You have not borrowed the book!</p>';
				}
				else{//已借
					$query="update borrow set return_date='".date('Y-m-d')."' where cno='".$cno."' and bno='".$bno."' and return_date is null order by borrow_date asc limit 1";
					mysqli_query ($dbc, $query);
					$query="update book set stock=stock+1 where bno='".$bno."'";
					mysqli_query ($dbc, $query);
					echo '<h1>Result</h1><p>Thanks! You have returned the book.</p>';
				}
				include ('includes/footer.html');
				mysqli_close($dbc);
				exit();
		}
	}
?>

<?php
include ('includes/footer.html');
?>