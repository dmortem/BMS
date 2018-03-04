<?php
	session_start();
	if (!isset($_SESSION['agent']) OR ($_SESSION['agent'] != md5($_SERVER ['HTTP_USER_AGENT']) )) {

		require ('includes/login_functions.inc.php');
		redirect_user();

	}
	
	$page_title = 'Warehouse Entry';
	include ('includes/header.html');
	require('includes/test_input.inc.php');
?>

<h1>Warehouse Entry</h1>
<form action="" method="post" >  
	<table width="200" border="0" align="center" cellspacing="8">
    <tr>
		<td>Book number:</td>
    	<td><input type="text" name="bno" size="20" maxlength="8" required="required" value="<?php if(!empty($_POST['bno'])) echo test_input($_POST['bno']); ?>"/></td>
		<td>Category:</td>
    	<td><input type="text" name="category" size="20" maxlength="10" required="required" value="<?php if(!empty($_POST['category'])) echo test_input($_POST['category']); ?>"/></td>
    </tr>	
    <tr>
		<td>Title:</td>
        <td><input type="text" name="title" size="20" maxlength="40" required="required" value="<?php if(!empty($_POST['title'])) echo test_input($_POST['title']); ?>"/></td>
        <td>Press:</td>
        <td><input type="text" name="press" size="20" maxlength="30" required="required" value="<?php if(!empty($_POST['press'])) echo test_input($_POST['press']); ?>"/></td>
	</tr>
    <tr>
        <td>Author:</td>
        <td><input type="text" name="author" size="20" maxlength="20" required="required" value="<?php if(!empty($_POST['author'])) echo test_input($_POST['author']); ?>"/></td>
        <td>Year:</td>
        <td align="center">
		<?php
			echo '<select name="year" required="required">';
			$years = range(2016, 1897);
			foreach($years as $value) {
				echo "<option value=\"$value\" ";
				if(isset($_POST['year'])&&$_POST['year']==$value)
					echo 'selected="selected"';
				echo "> $value</option>\n";
			}
			echo '</select>';
		?></td>
	</tr>
    <tr>
        <td>Price:</td>
        <td><input type="number" name="price" min="0"  step="0.01" required="required" value="<?php if(!empty($_POST['price'])) echo test_input($_POST['price']); ?>"/></td>
        <td>Amount:</td>
        <td><input type="number" name="amount" min="0" required="required" value="<?php if(!empty($_POST['amount'])) echo test_input($_POST['amount']); ?>"/></td>
	</tr>
    <tr >
        <td colspan="4" align="center"><input type="submit" name="submit" value="Enter" /></td>
    </tr>
</table>
</form>
<br/>
<form enctype="multipart/form-data" action="" method="post">
<input type="hidden" name="MAX_FILE_SIZE" value="524288" />
<div align="center"><fieldset><legend><font size="+1">Select a text file to be uploaded:</font></legend><b>File:</b><input type="file" name="upload" /></fieldset><input type="submit" name="submit" value="Upload" /></div>
</form>

<?php
	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
		require ('mysqli_connect.php');
		$errors=array();
		if($_POST['submit']=='Enter'){//单本入库
			$bno=test_input($_POST['bno']);
			$category=test_input($_POST['category']);
			$title=test_input($_POST['title']);
			$press=test_input($_POST['press']);
			$year=test_input($_POST['year']);
			$author=test_input($_POST['author']);
			$price=test_input($_POST['price']);
			$amount=test_input($_POST['amount']);
			
			if(empty($bno)) $errors[]='Book number is not filled.';
			if(empty($category)) $errors[]='Category is not filled.';
			if(empty($title)) $errors[]='Title is not filled.';
			if(empty($press)) $errors[]='Press is not filled.';
			if(empty($year)) $errors[]='Year is not filled.';
			if(empty($author)) $errors[]='Author is not filled.';
			if(empty($price)) $errors[]='Price is not filled.';
			if(empty($amount)) $errors[]='Amount is not filled.';
			
			if(empty($errors)){
				$query="select * from book where bno='".mysqli_real_escape_string($dbc, $bno)."'";
				$r = mysqli_query ($dbc, $query);
				$num = mysqli_num_rows($r);
				if($num==1){//书号已存在
					$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
					if($category==$row['category']&&$title==$row['title']&&$press==$row['press']&&$year==$row['year']&&$author==$row['author']&&$price==$row['price']){//同一本书
						mysqli_free_result ($r);
						$query="update book set total=total+".mysqli_real_escape_string($dbc, $amount)." ,stock=stock+".mysqli_real_escape_string($dbc, $amount)." where bno='".mysqli_real_escape_string($dbc, $bno)."'";
						mysqli_query ($dbc, $query);
						mysqli_close($dbc);
						echo "<h1>Result</h1><p>The book($bno) is added successfully.</p><br/>\n";
						include ('includes/footer.html');
						exit();
					}
					else{//不同本书
						mysqli_free_result ($r);
						$errors[]='Another book has used the book bumber.';
					}
				}
				else{//书号不存在
					$query="insert into book values('".mysqli_real_escape_string($dbc, $bno)."','".mysqli_real_escape_string($dbc, $category)."','".mysqli_real_escape_string($dbc, $title)."','".mysqli_real_escape_string($dbc, $press)."',".mysqli_real_escape_string($dbc, $year).",'".mysqli_real_escape_string($dbc, $author)."',".mysqli_real_escape_string($dbc, $price).",".mysqli_real_escape_string($dbc, $amount).",".mysqli_real_escape_string($dbc, $amount).")";
					mysqli_query ($dbc, $query);
					mysqli_close($dbc);
					echo "<h1>Result</h1><p>The book($bno) is added successfully.</p><br/>\n";
					include ('includes/footer.html');
					exit();
				}
			}
		}
		elseif($_POST['submit']=='Upload'){//多本入库
			if(move_uploaded_file ($_FILES['upload']['tmp_name'], "uploads/{$_FILES['upload']
['name']}")){
				$file = fopen("uploads/{$_FILES['upload']['name']}", "r") or die("Unable to open file!");
				while(!feof($file)) {
					$v=trim(fgets($file));
					$v=trim($v,'()');
					$a=explode(',',$v);
					$b=array();
					foreach($a as $k=>$v){
						$a[$k]=trim($v);
						$b[$k]=mysqli_real_escape_string($dbc, $a[$k]);
					}
					$query="select * from book where bno='".$b[0]."'";
					$r = mysqli_query ($dbc, $query);
					$num = mysqli_num_rows($r);
					if($num==1){//书号已存在
						$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
						if($a[1]==$row['category']&&$a[2]==$row['title']&&$a[3]==$row['press']&&$a[4]==$row['year']&&$a[5]==$row['author']&&$a[6]==$row['price']){//同一本书
						$query="update book set total=total+".$a[7]." ,stock=stock+".$a[7]." where bno='".$b[0]."'";
						mysqli_query ($dbc, $query);
						}
						mysqli_free_result ($r);
					}
					else{//书号不存在
						$query="insert into book values('".$b[0]."','".$b[1]."','".$b[2]."','".$b[3]."',".$b[4].",'".$b[5]."',".$b[6].",".$b[7].",".$b[7].")";
						mysqli_query ($dbc, $query);
					}
				}
				fclose($file);
				mysqli_close($dbc);
				echo "<h1>Result</h1><p>Upload successfully.</p><br/>\n";
				include ('includes/footer.html');
				exit();
			}
			else{
				$errors[]='Please upload a text file.';	
			}
		}
		
		mysqli_close($dbc);
		if (!empty($errors)) {//打印错误
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