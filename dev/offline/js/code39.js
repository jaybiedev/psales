// JavaScript BarCode39 v. 1.0 (c) Lutz Tautenhahn, 2005
// The author grants you a non-exclusive, royalty free, license to use,
// modify and redistribute this software.
// This software is provided "as is", without a warranty of any kind.

Chars="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. *$/+%";
Codes=new Array(
"111221211","211211112","112211112","212211111","111221112","211221111","112221111","111211212",
"211211211","112211211","211112112","112112112","212112111","111122112","211122111","112122111",
"111112212","211112211","112112211","111122211","211111122","112111122","212111121","111121122",
"211121121","112121121","111111222","211111221","112111221","111121221","221111112","122111112",
"222111111","121121112","221121111","122121111","121111212","221111211","122111211","121121211",
"121212111","121211121","121112121","111212121");
BarPic=new Array(2);
BarPic[0]=new Image(); BarPic[0].src="../js/b.gif"
BarPic[1]=new Image(); BarPic[1].src="../js/w.gif"

function Code39(theX, theY, theBarHeight, theFontHeight, theBarCodeText)
{ var pp="", ff;
  if ((theX!="")&&(theY!="")) pp="position:absolute;left:"+theX+";top:"+theY+";";
  if ((theFontHeight>4)&&(theBarHeight>=2*theFontHeight))
  { ff="style='font-size:"+theFontHeight+"px;font-family:Verdana;'";
    document.write("<div style='"+pp+"font-size:"+theFontHeight+"px;font-family:Verdana;'><table noborder cellpadding=0 cellspacing=0><tr>");
    document.write("<td rowspan=2 valign=top>"+CodePics("*",theBarHeight)+"</td>");
    for (i=0; i<theBarCodeText.length; i++)
      document.write("<td>"+CodePics(theBarCodeText.charAt(i),theBarHeight-theFontHeight-1)+"</td>");
    document.write("<td rowspan=2 valign=top>"+CodePics("*",theBarHeight)+"</td>");
    document.write("</tr><tr>");
    for (i=0; i<theBarCodeText.length; i++)
      document.write("<td align=center "+ff+">"+theBarCodeText.charAt(i)+"</td>");
    document.write("</tr></table></div>");
  }
  else
  { document.write("<div style='"+pp+"'><table noborder cellpadding=0 cellspacing=0>");
    document.write("<tr><td>"+CodePics("*",theBarHeight)+"</td>");
    for (i=0; i<theBarCodeText.length; i++)
      document.write("<td>"+CodePics(theBarCodeText.charAt(i),theBarHeight)+"</td>");
    document.write("<td>"+CodePics("*",theBarHeight)+"</td></tr></table></div>");
  }
}
function CodePics(theChar, theHeight)
{ var ss="", cc="9", ii=Chars.indexOf(theChar);
  if (ii>=0) cc=Codes[ii];
  for (ii=0; ii<cc.length; ii++) ss+="<img src='"+BarPic[ii%2].src+"' width="+cc.charAt(ii)+" height="+theHeight+">";
  ss+="<img src='"+BarPic[ii%2].src+"' width=1 height="+theHeight+">";
  return(ss);
}
