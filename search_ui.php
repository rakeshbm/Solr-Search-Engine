<?php 

include 'SpellCorrector.php';
include 'simple_html_dom.php';

header('Content-Type: text/html; charset=utf-8');

$limit = 10;
$f = false;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;

if ($query)
{
  require_once('Apache/Solr/Service.php');

  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/assignment');

  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }
  
  try
  {
	if(!isset($_GET['algorithm'])) $_GET['algorithm']="lucene";
  	if($_GET['algorithm'] == "lucene")
  	{
    		$results = $solr->search($query, 0, $limit);
  	}
  	else
  	{
  		$params = array('sort' => 'pageRankFile desc');
  		$results = $solr->search($query, 0, $limit, $params);
  	}
  }
  catch (Exception $e)
  {
  	die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}

?>

<html>
  <head>
    <style>
	body {color: black; background-color: white; font-size:20px;}
	form {text-align: center;}
	fieldset {margin: 0 30em;}
	legend {margin: 0 auto; font-weight: 600; font-size: 30px;}
	#q {height: 3em; width: 20em; font-size: 20px}
	li {border: 1px solid black; margin:1em;}
	.url {font-size: 18px;}
	.id {font-size:15px;}
	#parent { display: flex; }
	#narrow { width: 350px; margin: 1em;}
	#narrow img { height:300px; width:300px; vertical-align:middle;}
	#wide { flex: 1; margin-top: 2em; margin-right: 2em; display: insentence-block;}
	#summary {margin: 2em;}
	#search {background-color: #4285F4; color: white; height: 2em; font-size: 17px; border-radius: 10%; width: 6em;}
    </style>
    <title>Yahoo News</title>
  
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.12.0/themes/smoothness/jquery-ui.css">
        <script src="http://code.jquery.com/jquery-1.12.0.js"></script>
        <script src="http://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>
  
  </head>
  <body>

	<script>
	         $(function() {
	             var wc=0;
	             var dropdown = [];
	             var preUrl = "http://localhost:8983/solr/assignment/suggest?wt=json&indent=true&indent=on&q=";
	             $("#q").autocomplete({
	                 source : function(request, response) {
	                      var finalString="",before="";
	                      var query = $("#q").val().toLowerCase();
	                      var maxDisplay = 5;
	                      var space =  query.lastIndexOf(' ');
	                      if(query.length-1>space && space!=-1){
	                       finalString=query.substr(space+1);
	                       before = query.substr(0,space);
	                     }
	                     else{
	                       finalString=query.substr(0);
	                     }
	                     var URL = preUrl + finalString;    
         
                    	 $.ajax({
    	                     url : URL,
    	                     success : function(data) {
                                var js = data.suggest.suggest
                             	var docs = JSON.stringify(js);
                             	var jsonData = JSON.parse(docs);
                             	var answer = jsonData[finalString].suggestions;
         
                             	var j=0;
                             	for (var i=0; i<answer.length; i++){
                                 if (before == ""){
                                     dropdown[j] = answer[j].term
                                 } else{
                                     dropdown[j] = before + " " + answer[j].term;
                                 }
                                 j++;
                             	}
                             	response(dropdown.slice(0,maxDisplay));
         
                         	},
                            dataType : 'jsonp',
                            jsonp : 'json.wrf'
                         });
                     dropdown=[];
                     },
             	     minLength : 1
              	})
              });
    	</script>    
	<br>
	<form accept-charset="utf-8" method="get">
		<fieldset>
		<legend>Yahoo News</legend><br>
		<input type="text" id="q" name="q" placeholder="Search Yahoo..." value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"> <br><br>
		<u>Ranking algorithm:</u><br><br><input type="radio" id="lucene" value="lucene" name="algorithm" <?php if(isset($_REQUEST['algorithm']) && $_GET['algorithm']=='lucene') echo 'checked="checked"' ?>>Lucene<br>
		<input type="radio" id="pagerank" value="pagerank" name="algorithm" <?php if(isset($_REQUEST['algorithm']) && $_GET['algorithm']=='pagerank') echo 'checked="checked"' ?>>PageRank<br><br>
 		<input type="submit" value="Search"/ id="search">
		</fieldset>
    	</form>
    
<?php

if ($results)
{

  $tbString = $_GET['q'];
			
			$complete = "";
			$queries  = explode(" ", $query);
			
			if (sizeof($queries) == 1) {
				$complete = SpellCorrector::correct($query);
			} else {
				foreach ($queries as $arrayElement) {
				    $temp     = SpellCorrector::correct($arrayElement);
				    $complete = $complete . " " . $temp;
				}
			}

			if (strtolower(trim($tbString)) != strtolower(trim($complete))) {
				
				$lower_complete = strtolower(trim($complete));
				
				echo "<label>Showing results for " . $tbString . ". <br><b>Did you mean </b><a href='search_ui.php?q=".$lower_complete."' style='color:red;'>" . $complete . "</a><b>?</b></label>";
			}
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);

  $inputFile = file("/home/rakesh/Downloads/URLtoHTML_yahoo_news.csv");
			
			foreach ($inputFile as $sentence) {
				$file                 = str_getcsv($sentence);
				$fileUrlMap[$file[0]] = trim($file[1]);
			}

?>
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    
    <ol>
<?php
  $csv = array_map('str_getcsv', file('/home/rakesh/Downloads/URLToHTML_yahoo_news.csv'));
	
  foreach ($results->response->docs as $doc)
  {  
	$key = str_replace("/home/rakesh/Downloads/solr-7.7.0/yahoo/", "", $doc->id);
	$url = $fileUrlMap[$key];
	$image = $doc->og_image;
	$id = $doc->id;
  	$title = $doc->og_title;
  	
	$searchWord = $_GET['q'];
	$queryWords = explode(" ", $searchWord);
	$snippet = "";
	$file_content = file_get_contents($id);
	$textContents = str_get_html($file_content);
		    
	foreach ($textContents->find('p') as $sentence)
	{
		$wordCount = 0;        
		$sentenceLow    = strtolower($sentence);
		$defaultSnippet = strip_tags($sentence);
		        
		foreach ($queryWords as $word)
		{
			if (!empty($word) && strpos($sentenceLow, strtolower($word)) !== false)
			{
		        	$wordCount++;
		        }
		}
		        
		if ($maxWordCount < $wordCount)
		{
		        $snippet      = strtolower($defaultSnippet);
		        $maxWordCount = $wordCount;
		}
	}
		    
        $pos = 0;
        foreach ($queryWords as $word)
	{
		if (strpos(strtolower($snippet), strtolower($word)) !== false)
		{
			$pos = strpos(strtolower($snippet), strtolower($word));
		        break;
		}
	}
		    
	$start = 0;
	if ($pos > 80)
	{
		$start = $pos - 80;
	}
	else
	{
		$start = 0;
	}
	$end = $start + 160;
	if (strlen($snippet) < $end)
	{
		$end   = strlen($snippet) - 1;
		$post1 = "";
	}
	else
	{
		$post1 = "<strong>&nbsp;...</strong>";
	}
		    
	if (strlen($snippet) > 160)
	{
		if ($start > 0)
			$pre = "<strong>...&nbsp;</strong>";
		else
		        $pre = "";
		$snippet = $pre . substr($snippet, $start, $end - $start + 1) . $post1;
	}

	if (strlen($snippet) == 0)
	{
		$snippet = $doc->description;
		
	}
	error_reporting(E_ALL ^ E_NOTICE);
?>
<li>
		<div id="parent">
		<div id="narrow">
			<img src="<?php echo $image ?>">
		</div>
		<div id="wide">
			<b><a target="_blank" href="<?php echo $url ?>" style="text-decoration:none; color: blue">
				<?php echo $title ?> 
			  </a></b></br>
			<span class="url"><i><a target="_blank" href="<?php echo $url ?>" style="color:green;"><?php echo $url ?></a></i></span></br>
			<span class="id">ID: <?php echo $id ?></span></br></br>
			<span class="summary"><?php echo $snippet ?></span></br>
		</div>
		</div>
	</li>
	<?php

		
	}
}
?>
</ol>

	</body>
</html>

