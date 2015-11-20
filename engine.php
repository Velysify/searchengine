<?php
//Start a connection to the database page_search, Truncate the tables and fill the database again.
$conn = start_conn("page_search");
$conn->query("TRUNCATE TABLE page");
$conn->query("TRUNCATE TABLE word");
$conn->query("TRUNCATE TABLE occurrence"); 
fill_database();
$conn->close();

//Starts a database connection.
function start_conn($s){
	$user = 'root';
	$pass = '';
	$db = $s;
	$conn = new mysqli('localhost', $user, $pass, $db);
	if ($conn->connect_error){
		die("Connection failed: " . $conn->connect_error);
	}
	return $conn;
}
//Finds all files in the directory above engine.php and in all directories below it and adds them to the database. 
//Also opens these pages and take out all the words in them. Called from admin_search_database.
function fill_database(){
	$conn = start_conn('page_search');
	$content = '';
	$Directory = new RecursiveDirectoryIterator("../");
	$Iterator = new RecursiveIteratorIterator($Directory,RecursiveIteratorIterator::CHILD_FIRST);
	try{
		foreach($Iterator as $fullFileName => $fileSPLObject){
			if($fileSPLObject->isReadable() && !$fileSPLObject->isDir()){
				$entry = $fileSPLObject->getFileName();
				$page_id = '';
				$result = $conn->query("SELECT id FROM page WHERE name = \"$entry\"");
				$row = mysqli_fetch_array($result);
				$result->close();
				//If the page already exists set page_id to the id in the table
				if($row['id']){
					$page_id = $row['id'];

				}
				//Otherwise create the page and get the id. 
				else{
					$sql = "INSERT INTO page (name) VALUES('$entry')";
					if ($conn->query($sql) === TRUE) {
						$result = $conn->query("SELECT id FROM page WHERE name = \"$entry\"");
						$row = mysqli_fetch_array($result);
						$page_id = $row['id'];
						$result->close();
					}
					else{
						echo ("Error");
					}
				}
				//Read all the words from the file
				$file = fopen($fullFileName, "r") or die("Unable to open file");
				filesize($fullFileName);
				$text = fread($file, 1024);
				fclose($file);
				$text= trim($text);
				$text = strip_tags($text);
				$text = str_replace("  ", "", $text);
				$text = ereg_replace('/&\w;/', '',$text);
				$list_of_words = explode(" ", $text);
				//and for each word in the file, send it and the page id to fill_words.
				foreach (array_filter($list_of_words) as $value){
					$value = trim($value);
					if($value){
						fill_words($value, $page_id);
					}
				}
				unset($value);
			}
		}
		fill_comments();
	}
	catch (UnexpectedValueException $e) {
		printf("Directory [%s] contained a directory we can not recurse into", $directory);
	}

}

//Use fill_words on all words in the comments.
function fill_comments(){
	$conn_guest = start_conn('guestbook');
	$str = '';
	$result = $conn_guest->query('SELECT * FROM comment');
		while($row = $result->fetch_assoc()){
			$str = $str . $row['Name'] . " " . $row['Epost'] . " " . $row['Hemsida'] . " " . $row['Kommentar'];
		}
	$conn_guest->close();
	
	//Send all the words in the "comment" table from the guestbook page to fill_words with the page_id: 0 
	$list_of_words = explode(" ", $str);
	foreach (array_filter($list_of_words) as $value){
		$value = trim($value);
		if($value){
			fill_words($value, 0);
		}
	}
	unset($value);
}
//Add the words and create occurrences for time the word is discovered.
function fill_words($word, $page_id){
	$conn = start_conn('page_search');
	//If it is from comments, send it to the guestbook instead.
	if($page_id == 0){
		$result = $conn->query("SELECT id FROM page WHERE name = \"6.2.1 Gastbok.php\"");
		$row = mysqli_fetch_array($result);
		$page_id = $row['id'];
		$result->close();
	}
	$result = $conn->query("SELECT id FROM word WHERE word = \"$word\"");
	$row = mysqli_fetch_array($result);
	//If the word is already in the database add an occurrence of it with the page_id.
	if($row){
		$result->close();
		$word_id = $row['id'];
		$sql2 = "INSERT INTO occurrence(word_id, page_id) VALUES('$word_id','$page_id')";
		if($conn->query($sql2) === TRUE){
		}
		else{
			echo("Error");
		}
	}
	//Else add the word into the word table and create and occurrence. 
	else{
		$result->close();
		$sql = "INSERT INTO word (word) VALUES('$word')";
		if ($conn->query($sql) === TRUE) {
			$result = $conn->query("SELECT id FROM word WHERE word = \"$word\"");
			$row = mysqli_fetch_array($result);
			$word_id = $row['id'];
			$sql2 = "INSERT INTO occurrence(word_id, page_id) VALUES('$word_id','$page_id')";
			if($conn->query($sql2) === TRUE){
			}
			else{
				echo("Error");
			}
		}
		else{
			echo ("Error");
		}			
	}
}	
?>