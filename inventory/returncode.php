<script>
	function approve_confirm() {
	  doyou = confirm("Do you want to confirm?");
	  
	  if(doyou == true) {
		return true;
	  }
	  else if(doyou == false) {
		return false;
	  }
	}  
</script>
<style type="text/css">
	*{
		font-family:Verdana, Geneva, sans-serif;
		font-size:12px;
	}
	.container{
		width:50%;	
		margin:auto;
	}
	hr{
		border:1px solid #FFF;	
	}
	
	table{
		width:100%:	
	}
</style>
<?
$b = $_REQUEST['b'];
$return_code = $_REQUEST['return_code'];
$description = $_REQUEST['description'];

$return_code_id = $_REQUEST['return_code_id'];
$update_return_code = $_REQUEST['update_return_code'];
$update_description = $_REQUEST['update_description'];
$id = $_REQUEST['id'];

if($b == "Add"){
	if($return_code && $description){
		@pg_query("insert into return_code (return_code,description) values ('$return_code','$description')") or die(@pg_last_error());	
		message1("Return Code Saved");
	}
} else if ($b == "Update"){
	if($return_code_id){
		$i = 0;
		foreach($return_code_id as $id){
			@pg_query("
				update
					return_code
				set
					return_code = '$update_return_code[$i]',
					description = '$update_description[$i]'
				where
					return_code_id = '$id'
			");
			$i++;
		}
		message1("Return Code Updated");
	}
} else if($b == "d"){
	@pg_query("delete from return_code where return_code_id = '$id'");
	message1("Return Code Deleted");
}
?>
<form name="form1" id="f1"  method="post" action="">
	<div class="container">
        <div style="display:inline-block;">
            Return Code: <br />
            <input type="text" name="return_code" />
        </div>
        <div style="display:inline-block;">
            Description: <br />
            <input type="text" name="description" />
        </div>
        <input type="submit" name="b" value="Add" />
        <input type="submit" name="b" value="Update" />
        <hr style="clear:both; margin:10px 0px;" /> 
        
        <table>
        	<tr>
            	<th width="20">#</th>
	            <th width="20"></th>
                <th>Return Code</th>
                <th>Description</th>
           	</tr>
            <?
			$i=1;
			$result = @pg_query("select * from return_code");
			while($r = @pg_fetch_assoc($result)){
            ?>
            <tr>
            	<td><?=$i++?></td>
            	<td><a href="?p=returncode&b=d&id=<?=$r['return_code_id']?>" onclick="return approve_confirm();"><input type="button"  value="Delete" /></a></td>
                <td><input type="text" name="update_return_code[]" value="<?=$r['return_code']?>" /></td>
                <td><input type="text" name="update_description[]" value="<?=$r['description']?>" /></td>
                <input type="hidden" name="return_code_id[]" value="<?=$r['return_code_id']?>"  />
            </tr>
            <? } ?>
        </table>
        
   	</div>
</form>
