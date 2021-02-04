<?php	include ("../jpgraph/src/jpgraph.php"); 


$data = $cGraph['data'];
$leg = $cGraph['leg'];
$xtick = $cGraph['xtick'];
      
$g1 = $cGraph['g1'];
  
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
  $bplot->value->SetFont(FF_ARIAL,FS_BOLD); 
  $bplot->value->SetAngle(45); 
  $bplot->value->SetColor("black","navy"); 
  
  $graph->Add($bplot); 
}
elseif ($g1 == 'pie')
{
  include ("../jpgraph/src/jpgraph_pie.php"); 
  include ("../jpgraph/src/jpgraph_pie3d.php"); 

  $title = "Category Sales (Total Sales: ";
  if ($cGraph['top'] != '')
  {
  	$title .= " Top ".$cGraph['top'].'=';
  }
  else
  {
  	$title .= " Overall = ";
  }
  $title .= number_format($cGraph['total_amount'],2).")";
  $graph = new PieGraph(700,350,"auto"); 
  $graph->SetShadow(); 
  $graph->title->Set($title); 
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
	$ydata = $data; //array(11,3, 8,12,5 ,1,9, 13,5,7 );

	// Create the graph. These two calls are always required
	$graph = new Graph(700, 300,"auto");    
	$graph->SetScale( "textlin");
	$graph->xaxis->SetTickLabels($xtick); 

	// Create the linear plot
	$lineplot =new LinePlot($ydata);
	$lineplot ->SetColor("blue");

	//set margin
	$graph->img->SetMargin(40,20,20,40);
	$graph->title->Set("Category Sales");
	$graph->xaxis->title->Set("Categories Sorted Descending");
	$graph->yaxis->title->Set("Amount of Sales"); 

	$graph->title->SetFont(FF_FONT1,FS_BOLD);
	$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
	$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
	$lineplot->SetColor("blue");
	$lineplot->SetWeight(2);  // Two pixel wide
	

	// Add the plot to the graph
 	$graph->SetShadow();
	$graph->Add( $lineplot);

/*
	//second graph
 	$ydata2 = array(1432,1339,1315,3217,2322,1454,5,4319,2421,4313);
	$lineplot2=new LinePlot($ydata2);
	$lineplot2->SetColor("orange");
	$lineplot2->SetWeight(2);

	$lineplot->SetLegend("Plot 1");
	$lineplot2->SetLegend("Plot 2");
	
	$graph->legend->Pos(0.05,0.5,"right","center");

	$graph->Add($lineplot2);
*/

}
// Display the graph
$graph->Stroke();


?>
