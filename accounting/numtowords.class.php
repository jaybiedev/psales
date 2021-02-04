<?
 
 class num2words {  
    var $numb = Array();  
    var $tail;  
    var $number;  
    var $currency;  
    var $min;  
    function num2words () {  
      $this->numb = Array ("",  
                           "one",  
                           "two",  
                           "three",  
                           "four",  
                           "five",  
                           "six",  
                           "seven",  
                           "eight",  
                           "nine");  
    }  
    function mod($a,$b) {  
      return $a-$b*floor($a/$b);  
    }  
    function setTail($str) {  
      $this->tail = $str;  
    }  
    function setNumber($int) {  
      $int = trim($int);  
      if (is_int(strpos($int,"-"))) {  
        $this->number = substr($int,strpos($int,"-")+1,strlen($int));  
        $this->currency = "minus";  
      } else {  
        $this->number = $int;  
      }  
      $this->setAsCurrency();  
    }  
    function getCurrency() {  
      return $this->currency;  
    }  
    function printCurrency() {  
      print ucfirst(strtolower(trim($this->currency)));  
    }  
    function setAsCurrency() {  
      $xpos = strpos($this->number,".");  
      if (is_int($xpos)) {  
        $pecahan = round(substr($this->number,$xpos,strlen($this->number)),2);  
        $sisa = substr($this->number,0,$xpos);  
      } else {  
        $pecahan = "";  
        $sisa    = $this->number;  
      }  
      if ($sisa==0 || $this->number==0) {  
        $this->currency .= "zero ".$this->tail;  
      } else {  
        $trilion = floor($sisa/pow(10,12));  
        $sisa    = $this->mod($sisa,1000000000000);  

        $billion = floor($sisa/pow(10,9));  
        $sisa    = $this->mod($sisa,1000000000);  

        $million = floor($sisa/pow(10,6));  
        $sisa    = $this->mod($sisa,1000000);  

        $thousand = floor($sisa/pow(10,3));  
        $sisa     = $this->mod($sisa,1000);  

        $words    = $this->ThreeDigit($trilion, "trilion");  
        $words   .= $this->ThreeDigit($billion, "billion");  
        $words   .= $this->ThreeDigit($million, "million");  
        $words   .= $this->ThreeDigit($thousand, "thousand");  
        $words   .= $this->ThreeDigit($sisa,"");  
        $words   .= " ".$this->tail;  
      }  
      if ($pecahan>0) {  
        //$words .= " and". $this->ThreeDigit(round($pecahan*100),"sen");  
		$words .= " and"." ".round($pecahan*100)."/100";
      }  
      $this->currency .= strtolower($words);  
    }  
    function ThreeDigit($amount, $suffix="") {  
      $sisa = (int) $amount;  
      $words = "";  
      if ($sisa < 20 && $sisa > 10) {  
        if ($sisa==11) {  
          $words = " eleven";  
        } elseif ($sisa == 12) {  
          $words = " twelve";  
        } elseif ($sisa == 13) {  
          $words = " thirteen";  
        } elseif ($sisa == 15) {  
          $words = " fifteen";  
        } elseif ($sisa == 18) {  
          $words = " eighteen";  
        } else {  
          $words = " ".$this->numb[$sisa-10]."teen";  
        }  
        if ($suffix != "") {  
          $words .= " ".$suffix;  
        }  
        return $words;  
      }  
      $ratus = floor($sisa/100);  
      if ($ratus <= 0) {  
        $words .= "";  
      } else {  
        $words .= " ".$this->numb[$ratus]." hundred";  
      }  
      $sisa = $this->mod($sisa,100);  
      if ($sisa < 20 && $sisa > 10) {  
        if ($sisa == 11) {  
          $words .= " eleven ". $suffix;  
        } elseif ($sisa == 12) {  
          $words .= " twelve";  
        } elseif ($sisa == 13) {  
          $words .= " thirteen";  
        } elseif ($sisa == 15) {  
          $words .= " fifteen";  
        } elseif ($sisa == 18) {  
          $words .= " eighteen";  
        } else {  
          $words .= " ".$this->numb[$sisa-10]."teen ". $suffix;  
        }  
        return $words;  
      }  
      $puluh = floor($sisa/10);  
      if ($puluh == 0) {  
        $words .= "";  
      } elseif ($puluh == 1) {  
        $words .= " ten";  
      } elseif ($puluh == 2) {  
        $words .= " twenty";  
      } elseif ($puluh == 3) {  
        $words .= " thirty";  
      } elseif ($puluh == 5) {  
        $words .= " fifty";  
      } elseif ($puluh == 5) {  
        $words .= " eighty";  
      } else {  
        $words .= " ".$this->numb[$puluh]."ty";  
      }  
      $sisa = $this->mod($sisa,10);  
      if ($sisa>0&&$sisa<=9) {  
        $words .= " ".$this->numb[$sisa];  
      }  
      if ($amount>0&&$amount<=1000) {  
        $words .= " ".$suffix;  
      }  
      return $words;  
    }  
  } 
  
?>