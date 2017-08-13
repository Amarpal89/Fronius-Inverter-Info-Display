<?php
/**************

***************/
//	IP Address of the inverter
$dataManagerIP = "192.168.1.100";
//	Consumption rate - $/kWh consumed
$ConsumptionRate = 0.28;
//	Feedin rate - $/kWh fed into grid
$FeedinRate = 0.109;

//	API call for Fronius
$apiURL1 = "http://".$dataManagerIP."/solar_api/v1/GetPowerFlowRealtimeData.fcgi";
$apiURL2 = "http://".$dataManagerIP."/solar_api/v1/GetMeterRealtimeData.cgi?Scope=Device&DeviceID=0";
$apiURL3 = "http://".$dataManagerIP."/solar_api/v1/GetInverterRealtimeData.cgi?Scope=Device&DeviceID=1&DataCollection=MinMaxInverterData";
//	Get the raw JSON
$jsonData1 = file_get_contents($apiURL1);
$jsonData2 = file_get_contents($apiURL2);
$jsonData3 = file_get_contents($apiURL3);
//	Decode into an object
$solar1= json_decode($jsonData1, true);
$solar2= json_decode($jsonData2, true);
$solar3= json_decode($jsonData3, true);

//	Get the variables in which we're interested
$solarTimestamp = $solar1["Head"]["Timestamp"];//	
$solarP_Grid = round($solar1["Body"]["Data"]["Site"]["P_Grid"]);//	+ve from grid, -ve to grid
$solarP_Load = round($solar1["Body"]["Data"]["Site"]["P_Load"]);//	
$solarP_PV = round($solar1["Body"]["Data"]["Site"]["P_PV"]);//	
$solarE_Day = $solar1["Body"]["Data"]["Site"]["E_Day"];//
$solarSelfConsumption = round($solar1["Body"]["Data"]["Site"]["rel_SelfConsumption"]);//	
$solarAutonomy = round($solar1["Body"]["Data"]["Site"]["rel_Autonomy"]);//
//-----------------------------------------------------------------------------------------------------
$solarVoltage = round($solar2["Body"]["Data"]["Voltage_AC_Phase_1"]);//		
//-----------------------------------------------------------------------------------------------------
$solarDAY_PMAX = $solar3["Body"]["Data"]["DAY_PMAX"];//	Max Solar Power Watts
$solarDAY_PMAX = $solarDAY_PMAX["Value"];
$solarDAY_UACMAX = $solar3["Body"]["Data"]["DAY_UACMAX"];//	Max AC Voltage Volts
$solarDAY_UACMAX = round($solarDAY_UACMAX["Value"]);
//-------------------------------------------------------------------------------------------------------


$solarE_Day = $solarE_Day/1000; //convert to kW.h

//echo $solarTimestamp."<br>","<br>";
//var_dump($solarTimestamp);

if($solarP_Grid == abs($solarP_Grid)) {
	$colorpgrid = "red";
	$colortgrid = "red";
	$usestate = "CONSUMPTION";
	$gridstate = "IMPORTING";
	$hourcost = round(($ConsumptionRate * abs($solarP_Grid) * 0.001),2);
} else {
	$colorpgrid = "limegreen";
	$colortgrid = "black";
	$usestate = "FEED IN";
	$gridstate = "EXPORTING";
	$hourcost = round(($FeedinRate * abs($solarP_Grid) * 0.001),2);
}


//scaling 480
$height = "65px";
$fontheight = "33px";
$screenwidth = 720;
$screenwidthpx = $screenwidth."px";

$pv_width = (round(($screenwidth/5000)*$solarP_PV))."px";
$pgrid_width = (round(($screenwidth/5000)*abs($solarP_Grid)))."px";
$pload_width = round(($screenwidth/5000)*abs($solarP_Load))."px";
$eday_width = round(($screenwidth/40)*$solarE_Day,2)."px";
$SelfConsumption_width = round(($solarSelfConsumption*($screenwidth/100)))."px";
$PMAX_width = (round(($screenwidth/5000)*$solarDAY_PMAX))."px";


//abs values for display && rounding
$solarP_Grid = abs($solarP_Grid);
$solarP_Load = abs($solarP_Load);
$solarE_Day = round($solarE_Day,2);


?>
<!-- HTML to dump when script called -->
<!-- Realtime energy flow table -->
	<div class="divTable" style="width:720px; border:4px solid #000;">
	<div class="divTableBody">
	<div class="divTableRow">
			<div class='divTableCell';> <!-----SOLAR OUTPUT----->
			<?php
			echo "<div class='bartext1'>  
				<span class='span1'>SOLAR OUTPUT:</span>
			</div>";
			echo "<div class='bartext2' style='width:$screenwidthpx;'>
				<span class='span2'>$solarP_PV W</span>
			</div>";
			echo "<div class='bar1' style='width:$pv_width; background:Dodgerblue;'></div>";
			?>
			</div>
	</div>
	</div>
	</div>
	
	<div class="divTable" style="width:720px; border:4px solid #000;">
	<div class="divTableBody">
	<div class="divTableRow">
			<div class="divTableCell";> <!-----LOAD----->
			<?php 
			echo "<div class='bartext1'>
				<span class='span1'>LOAD:</span>
			</div>";
			echo "<div class='bartext2' style='width:$screenwidthpx;'>
				<span class='span2'>$solarP_Load W</span>
			</div>";
			echo "<div class='bar1' style='width:$pload_width; background:darkorange;'></div>";
			?>
			</div>
	</div>
	</div>
	</div>
	<div class="divTable" style="width:720px; border:4px solid #000;">
	<div class="divTableBody">
	<div class="divTableRow">
			<div class="divTableCell"> <!-----GRID----->
			<?php 
			echo "<div class='bartext1' style='line-height:150px;'>
				<span class='span4'>GRID $usestate:</span>
			</div>";
			echo "<div class='bartext2' style='line-height:150px; width:$screenwidthpx; color:$colortgrid;'>
				<span class='span3'>$solarP_Grid W</span>
			</div>";
			echo "<div class='bar1' style='height:150px; width:$pgrid_width; background:$colorpgrid;'></div>";
			?>
			</div>
	</div>
	</div>
	</div>
	<!-------------------------Voltage------------------------------>
	<div class="datatext1" style="width:720px"> 
		<?php
			echo "Current AC Voltage:&nbsp; $solarVoltage V";
		?>
	</div>
	<div class="datatext1" style="width:720px"> 
		<?php
			echo "Max AC Voltage:&nbsp; $solarDAY_UACMAX V";
		?>
	</div>
	
	<div class="divTable" style="width:720px; border:4px solid #000;">
	<div class="divTableBody">
	<div class="divTableRow">
			<div class='divTableCell';> <!-----TODAYS PRODUCTION----->
			<?php
			echo "<div class='bartext1' style='line-height:150px'> 
				<span class='span1'>PRODUCTION:</span>
			</div>";
			echo "<div class='bartext2' style='line-height:150px; width:$screenwidthpx; color:firebrick;'>
				<span class='span3'>$solarE_Day kW.h</span>
			</div>";
			echo "<div class='bar1' style='height:150px; width:$eday_width; background:DarkViolet;'></div>";
			?>
			</div>
	</div>
	</div>
	</div>
	
	<div class="divTable" style="width:720px; border:4px solid #000;">
	<div class="divTableBody">
	<div class="divTableRow">
			<div class="divTableCell";> <!-----PMAX_width----->
			<?php 
			echo "<div class='bartext4'> 
				<span class='span0'>Max Solar Power:</span>
			</div>";
			echo "<div class='bartext5' style='width:$screenwidthpx; color:black;'>
				<span class='span2'>$solarDAY_PMAX W</span>
			</div>";
			echo "<div class='bar2' style='width:$PMAX_width; background:LightSkyBlue;'></div>";
			?>
			</div>
	</div>
	</div>
	</div>
	
	<div class="divTable" style="width:720px; border:4px solid #000;">
	<div class="divTableBody">
	<div class="divTableRow">
			<div class="divTableCell";> <!-----SELF CONSUMPTION----->
			<?php 
			echo "<div class='bartext4'> 
				<span class='span0'>SELF CONSUMPTION:</span>
			</div>";
			echo "<div class='bartext5' style='width:$screenwidthpx; color:black;'>
				<span class='span2'>$solarSelfConsumption %</span>
			</div>";
			echo "<div class='bar2' style='width:$SelfConsumption_width; background:gold;'></div>";
			?>
			</div>
	</div>
	</div>
	</div>
	
	<div class="divTable" style="width:720px; border:4px solid #000;">
	<div class="divTableBody">
	<div class="divTableRow">
			<div class="divTableCell"> <!-----gridstate----->
			<?php 
			echo "<div class='bartext3' style='width:$screenwidthpx; color:black;'> 
				<span class='span3';>$gridstate: $$hourcost/hr</span>
			</div>";
			echo "<div class='bar1' style='width:$screenwidthpx; background:$colorpgrid;'></div>";
			?>
			</div>
	</div>
	</div>
	</div>
	
	<!-----138 | Chapter 7: Practical PHP Date----->
	<div class="datatext2" style="width:720px"> 
		<?php
			echo date("l jS F", time());
		?>
	</div>

<style>
.datatext1{
	text-align: center;
	font-weight:bold;
	font-family:arial;
	font-size:38px;
	line-height:40px; 
	padding-top:12px;
	padding-bottom:12px;
}
.datatext2{
	text-align: center;
	font-weight:bold;
	font-family:arial;
	font-size:55px;
	line-height:75px; 
	padding-top:12px;
	padding-bottom:12px;
}
.bartext1{
	z-index:1; 
	position:absolute; 
	line-height:100px; 
	padding-left:15px;
}
.bartext2{
	z-index:1;
	position:absolute;
	line-height:100px;
	text-align:right; 
}
.bartext3{
	z-index:1;
	position:absolute;
	line-height:100px;
	text-align:center; 
}
.bartext4{
	z-index:1;
	position:absolute;
	line-height:70px;
	text-align:center; 
	padding-left:15px;
}
.bartext5{
	z-index:1;
	position:absolute;
	line-height:70px;
	text-align:right;
}
.bar1{
	z-index:0;
	position:relative; 
	max-width:100%; 
	height:100px; 
}
.bar2{
	z-index:0;
	position:relative; 
	max-width:100%; 
	height:70px; 
}
.span0{
	vertical-align: middle;
	background-color:white;
	font-weight:bold;
	font-family:arial;
	font-size:35px;
}
.span1{
	vertical-align: middle;
	background-color:white;
	font-weight:bold;
	font-family:arial;
	font-size:42px;
}
.span2{
	background-color:white;
	vertical-align: middle;
	padding-right:20px;
	font-weight:bold;
	font-family:arial;
	font-size:42px;
}
.span3{
	background-color:white;
	vertical-align: middle;
	padding-right:20px;
	font-weight:bold;
	font-family:arial;
	font-size:65px;
}
.span4{
	background-color:white;
	vertical-align: middle;
	padding-right:20px;
	font-weight:bold;
	font-family:arial;
	font-size:42px;
}

</style>