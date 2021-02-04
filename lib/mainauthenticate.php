<?
	function authenticate($username, $mpassword)
	{
		global $SYSCONF, $ADMIN;
		$p = "login";
		if ($mpassword == null or $username == null)
		{
			return 0;			
		}
		else
		{
			$mpassword = md5($mpassword);
			$q = "select * from admin where username='$username' and mpassword='$mpassword'";
			$qr = pg_query($q) or pg_errormessage();
			if (!$qr)
			{
				return 0;
			}
			elseif (pg_num_rows($qr) == 0)
			{
				return 0;
			}
			else
			{
				if (!session_is_registered("ADMIN"))
				{
					session_register("ADMIN");
					$ADMIN=array();
				}
				$ADMIN=pg_fetch_assoc($qr);

				if ($ADMIN['enable']=="N") 
				{
				  $q = "insert into userlog (admin_id, date_in, date_out, remarks) 
                  values ('".$ADMIN['admin_id']."','$date_in', '$date_out','User account DISABLED...Access Denied')";
          		$qr = @pg_query($q) or message(pg_errormessage());
					message('User account DISABLED... Access Denied.');
					return 0;
				}

				$attempts = 0;
				while ($attempts < 10)
				{
					$attempts++;
					mt_srand ((double) microtime() * 1000000);
					$randval = md5(mt_rand().$ADMIN['admin_id']);
					$q = "update admin set sessionid='$randval' 
						where username='$username' and mpassword='$mpassword'";
					
					if (pg_query($q)) 
					{
						$attempts = 0;
						$qa = pg_query("select * from adminrights where admin_id='".$ADMIN['admin_id']."'");
						if (pg_num_rows($qa)==0 || !$qa) {
							redirect("message=Security check...Pls. login again...&p=login");
						}
						else {
							$adminrights=array();
							while ($ra = pg_fetch_assoc($qa)) {
								$adminrights[] = $ra;
							}
						}	

						break;
					}	
				}		
				
				if ($attempts == 0)
				{
					$sessionid = $randval;
					$ADMIN['sessionid']=$sessionid;
					$p = "";
					
				  	$q = "insert into userlog (admin_id, date_in, ip, last_invoice) 
                  		values ('".$ADMIN['admin_id']."','$date_in',
                  					'".$_SERVER['REMOTE_ADDR']."','LOGIN')";
                  		
          		$qr = @pg_query($q) or message(pg_errormessage());
          		$id = @pg_insert_id('userlog');
          		$ADMIN['userlog_id'] = $id;
				}
				else
				{
					return 0;
				}
			}
		}	
		return true;
	}
?>
