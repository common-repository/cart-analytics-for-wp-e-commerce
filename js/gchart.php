<?php session_start();
Header("content-type: application/x-javascript"); ?>
// Load the Visualization API and the piechart package.
google.load('visualization', '1', {'packages':['corechart']});
      
// Set a callback to run when the Google Visualization API is loaded.
google.setOnLoadCallback(drawChart);
      
function drawChart() {
	var jsonData = jQuery.ajax({
		url: "<?php  
					echo $_SESSION['ca_data']['ca_path'].'/json_data.php?active=true'; 
			?>",
		dataType:"json",
		async: false
		}).responseText;
        
        // Set chart options
      var options = {'width':800,
                    'height':600,
                    };
          
// Create our data table out of JSON data loaded from server.
var data = new google.visualization.DataTable(jsonData);

// Instantiate and draw our chart, passing in some options.
var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
	chart.draw(data, options);
}