<?php
session_start();
$page_title = 'Query';
include ('includes/header.html');
require('includes/test_input.inc.php');
?>

<h1>Query</h1>
<form action="" method="post" >

    <p align="center">Enter your query requirements in the form below: </p><br/>
    
    <table width="200" border="0" align="center" cellspacing="8">
      <tr>
        <td>Category:</td>
        <td><input type="text" name="category" size="20" maxlength="10" value="<?php if(!empty($_POST['category'])) echo test_input($_POST['category']); ?>"/></td>
        <td>Title:</td>
        <td><input type="text" name="title" size="20" maxlength="40" value="<?php if(!empty($_POST['title'])) echo test_input($_POST['title']); ?>"/></td>
      </tr>
      <tr>
        <td>Press:</td>
        <td><input type="text" name="press" size="20" maxlength="30" value="<?php if(!empty($_POST['press'])) echo test_input($_POST['press']); ?>"/></td>
        <td>Author:</td>
        <td><input type="text" name="author" size="20" maxlength="20" value="<?php if(!empty($_POST['author'])) echo test_input($_POST['author']); ?>"/></td>
      </tr>
      <tr>
        <td>Price:</td>
        <td><input type="number" name="price1" min="0" step="0.01" value="<?php if(!empty($_POST['price1'])) echo test_input($_POST['price1']); ?>"/></td>
        <td align="center">~</td>
        <td><input type="number" name="price2" min="0" step="0.01" value="<?php if(!empty($_POST['price2'])) echo test_input($_POST['price2']); ?>"/></td>
      </tr>
      <tr>
        <td>Year:</td>
        <td align="center">
		<?php
			echo '<select name="year1" >';
			$years = range(1897, 2016);
			foreach($years as $value) {
				echo "<option value=\"$value\" ";
				if(isset($_POST['year1'])&&$_POST['year1']==$value)
					echo 'selected="selected"';
				echo "> $value</option>\n";
			}
			echo '</select>';
		?></td>
        <td align="center">~</td>
        <td align="center">
		<?php
			echo '<select name="year2" >';
			$years = range(2016, 1897);
			foreach($years as $value) {
				echo "<option value=\"$value\" ";
				if(isset($_POST['year2'])&&$_POST['year2']==$value)
					echo 'selected="selected"';
				echo "> $value</option>\n";
			}
			echo '</select>';
		?></td>
      </tr>
      <tr >
        <td>Order by</td>
        <td align="center">
        	<select name="orderby">
            <?php
				$orderby=array('bno','title','category','press','author','price','year');
				foreach($orderby as $value){
					echo  "<option value=\"$value\"";
					if(isset($_POST['orderby'])&&$_POST['orderby']==$value)
						echo 'selected="selected"';
					echo ">$value</option>";
				}
			?>
            </select>
        </td>
        <td align="center">in</td>
        <td align="center">
        	<select name="in" >
            <option value="desc" <?php if(isset($_POST['in'])&&$_POST['in']=='desc')
						echo 'selected="selected"';?> >descending order</option>
            <option value="asc" <?php if(isset($_POST['in'])&&$_POST['in']=='asc')
						echo 'selected="selected"';?> >ascending order</option>
            </select>
        </td>
      </tr>
      <tr >
        <td colspan="4"><p align="center"><input type="submit" name="submit" value="Query" /></p></td>
      </tr>
</table>
	
</form>

<?php
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		$q=array();
		if(isset($_POST['category'])&&!empty($_POST['category'])){
			$q['category']=$_POST['category'];
		}
		if(isset($_POST['title'])&&!empty($_POST['title'])){
			$q['title']=$_POST['title'];
		}
		if(isset($_POST['press'])&&!empty($_POST['press'])){
			$q['press']=$_POST['press'];
		}
		if(isset($_POST['author'])&&!empty($_POST['author'])){
			$q['author']=$_POST['author'];
		}
		if(isset($_POST['price1'])&&is_numeric($_POST['price1'])){
			$q['price1']=$_POST['price1'];
		}
		if(isset($_POST['price2'])&&is_numeric($_POST['price2'])){
			$q['price2']=$_POST['price2'];
		}
		if(isset($_POST['year1'])&&is_numeric($_POST['year1'])){
			$q['year1']=$_POST['year1'];
		}
		if(isset($_POST['year2'])&&is_numeric($_POST['year2'])){
			$q['year2']=$_POST['year2'];
		}
		if(isset($_POST['orderby'])&&!empty($_POST['orderby'])){
			$q['orderby']=$_POST['orderby'];
		}
		if(isset($_POST['in'])&&!empty($_POST['in'])){
			$q['in']=$_POST['in'];
		}
		
		$errors=array();
		if(empty($q)){
			$errors[]='Query requirements are not filled.';
		}
		if(isset($q['price1'])&&isset($q['price2'])&&$q['price1']>$q['price2']){
			$errors[]='The interval of price is illegal.';
		}
		if(isset($q['year1'])&&isset($q['year2'])&&$q['year1']>$q['year2']){
			$errors[]='The interval of year is illegal.';
		}
		
		if(isset($errors) && !empty($errors)){
			echo '<h1>Error!</h1>
				<p class="error">The following error(s) occurred:<br />';
			foreach ($errors as $msg) {
				echo " - $msg<br />\n";
			}
			echo '</p><p>Please try again.</p>';
		}
		else{
			require ('mysqli_connect.php');
			$query='select * from book where ';
			foreach($q as $k=>$v){
				switch($k){
					case 'price1':
						$query=$query."price>=$v and ";
						break;
					case 'price2':
						$query=$query."price<=$v and ";
						break;
					case 'year1':
						$query=$query."year>=$v and ";
						break;
					case 'year2':
						$query=$query."year<=$v ";
						break;
					case 'orderby':
						$query=$query."order by $v ";
						break;
					case 'in':
						$query=$query."$v";
						break;
					default:
						$query=$query."$k='".mysqli_real_escape_string($dbc, trim($v))."' and ";
						break;
				}
			}
			$r = mysqli_query ($dbc, $query);
			$num = mysqli_num_rows($r);
			echo '<h1>Result</h1>';
			if($num>0){
				echo "<p>There are $num records.</p><br/>\n";
				echo '<table align="center" cellspacing="3" cellpadding="3" width="100%">
	<tr align="left"><td><b>Book number</b></td><td><b>Category</b></td><td><b>Title</b></td><td><b>Press</b></td><td><b>Year</b></td><td><b>Author</b></td><td><b>Price</b></td><td><b>Total</b></td><td><b>Stock</b></td></tr>
';

				while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
					echo '<tr align="left"><td>' . $row['bno'] . '</td><td>' . $row['category'] .  '</td><td>' . $row['title'] .'</td><td>' . $row['press'] .'</td><td>' . $row['year'] .'</td><td>' . $row['author'] .'</td><td>' . $row['price'] .'</td><td>' . $row['total'] .'</td><td>' . $row['stock'] .'</td></tr>';
				}
				
				echo '</table>';
				mysqli_free_result ($r);
			}
			else{
				echo '<p class="error">No records.</p>';
			}
			mysqli_close($dbc);
		}
	}
?>

<?php
include ('includes/footer.html');
?>