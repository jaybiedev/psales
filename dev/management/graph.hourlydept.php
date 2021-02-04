<?php	include ("../jpgraph/src/jpgraph.php"); 


$data = $hGraph['data'];
$data1 = $hGraph['data1'];
$data2 = $hGraph['data2'];
$leg = $hGraph['leg'];
$acount = $hGraph['acount'];
$xtick = $hGraph['xtick'];
      
$g1 = $hGraph['g1'];
  
if ($g1 == 'bar')
{
  include ("../jpgraph/src/jpgraph_bar.php"); 

  $graph = new Graph(250,150,"auto"); 
  $graph->SetScale("textint"); 
  $graph->img->SetMargin(50,30,50,50); 
  $graph->AdjBackgroundImage(0.4,0.7,-1); //setting BG type 
  //$graph->SetBackgroundImage("linux_pez.png",BGIMG_FILLFRAME); //adding image 
  $graph->SetShadow(); 
  
  $graph->xaxis->SetTickLabels($leg); 
  $graph->title->Set("Hourly Sales By Department"); 
  
  $bplot = new BarPlot($data); 
  $bplot->SetFillColor("lightgreen"); // Fill color 
  $bplot->value->Show(); 
  $bplot->value->SetFont(FF_FONT1,FS_BOLD); 
  $bplot->value->SetAngle(45); 
  $bplot->value->SetColor("black","navy"); 
  
  $graph->Add($bplot); 
}
elseif ($g1 == 'pie')
{
  include ("../jpgraph/src/jpgraph_pie.php"); 
  include ("../jpgraph/src/jpgraph_pie3d.php"); 

  $graph = new PieGraph(700,350,"auto"); 
  $graph->SetShadow(); 
  $graph->title->Set("Hourly Sales By Department"); 
  $graph->title->SetFont(FF_FONT1,FS_BOLD); 
  
  $pplot = new PiePlot3D($data); 
  $pplot->SetSize(.3); 
  $pplot->SetCenter(0.45); 
  $pplot->SetStartAngle(20); 
  $pplot->SetAngle(45); 
  
  $pplot->SetLegends($leg); 
  
  $pplot->value->SetFont(FF_FONT1,FS_BOLD); 
  $pplot->value->SetColor("darkred"); 
  $pplot->SetLabelType(PIE_VALUE_PER); 
  
  $a = array_search(max($data),$data); //Find the position of  maixum value. 
  $pplot->ExplodeSlice($a); 
  
  $graph->Add($pplot); 
} 
elseif ($g1 == 'line')
{
//	include ( "../jpgraph/src/jpgraph.php");
	include ("../jpgraph/src/jpgraph_line.php");

	// Some data
	$ydata = $data1; //array(11,3, 8,12,5 ,1,9, 13,5,7 );

	// Create the graph. These two calls are always required
	$graph = new Graph(750, 350,"auto");    
	$graph->SetScale( "textlin");
	$graph->xaxis->SetTickLabels($leg); 

	// Create the linear plot
	$lineplot =new LinePlot($ydata);
	$lineplot ->SetColor("blue");

	//set margin
	$graph->img->SetMargin(40,20,20,40);
	$graph->title->Set("Hourly Sales By Department for ".$hGraph['date']);
	$graph->xaxis->title->Set("Time");
	$graph->yaxis->title->Set("Amount of Sales"); //("Amount of Sales "); 

	$graph->title->SetFont(FF_FONT1,FS_BOLD);
	$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
	$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
	$lineplot->SetColor("blue");
	$lineplot->SetWeight(2);  // Two pixel wide
	

	// Add the plot to the graph
 	$graph->SetShadow();
	$graph->Add( $lineplot);


	//second graph
 	$ydata2 = $data2; // array(1432,1339,1315,3217,2322,1454,5,4319,2421,4313);
	$lineplot2=new LinePlot($ydata2);
	$lineplot2->SetColor("orange");
	$lineplot2->SetWeight(2);

	$lineplot->SetLegend("Drygoods Sales x1000");
	$lineplot2->SetLegend("Grocery Sales x1000");
	
	$graph->legend->Pos(0.05,0.5,"right","center");

	$graph->Add($lineplot2);


}
// Display the graph
$graph->Stroke();


?>
