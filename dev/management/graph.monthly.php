<?php	include ("../jpgraph/src/jpgraph.php"); 


$data = $mGraph['data'];
$acount = $mGraph['acount'];
$xtick = $mGraph['xtick'];
$leg = $mGraph['leg'];
$acount = $mGraph['acount'];
      
$g1 = $mGraph['g1'];
  
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
  $graph->title->Set("Monthly Sales"); 
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
	$ydata = $data; 

	// Create the graph. These two calls are always required
	$graph = new Graph(750, 350,"auto");    
	$graph->SetScale( "textlin");
	$graph->xaxis->SetTickLabels($leg); 

	// Create the linear plot
	$lineplot =new LinePlot($ydata);
	$lineplot ->SetColor("blue");

	//set margin
	$graph->img->SetMargin(40,20,20,40);
	$graph->title->Set("Monthly Sales");
	$graph->xaxis->title->Set("Months,".$mGraph['year']);
	$graph->yaxis->title->Set(""); //Amount of Sales "); 

	$graph->title->SetFont(FF_FONT1,FS_BOLD);
	$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
	$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
   $graph->xaxis->SetTickLabels($xtick);
  	$lineplot->SetColor("blue");
	$lineplot->SetWeight(2);  // Two pixel wide
	

	//second graph
 	$ydata2 = $acount; 
	$lineplot2=new LinePlot($ydata2);
	$lineplot2->SetColor("red");
	$lineplot2->SetWeight(2);


	$lineplot->SetLegend("Total Sales x1000");
	$lineplot2->SetLegend("Count x10");
	
	// Add the plot to the graph
	$graph->legend->Pos(0.05,0.5,"right","center");

 	$graph->SetShadow();
	$graph->Add( $lineplot);

	$graph->Add($lineplot2);

}
// Display the graph
$graph->Stroke();


?>
