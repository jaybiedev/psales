<form name="form1" method="post" action="?p=receiving">
  <table width="50%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>New 
        SRR</strong></font></td>
    </tr>
    <tr> 
      <td nowrap bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Document/PO 
        No. 
        <input name="document" type="text" id="document" value="<?= $document;?>" size="10">
        <?= lookUpAssoc('source',array('PO Record No.'=>'po_header_id','Reference No.'=>'reference','No P.O.'=>'N'),$source);?>
        <input name="p1" type="submit" id="p1" value="Submit">
        </font></td>
    </tr>
  </table>
</form>
