<?php
include 'engine.php';
header('Content-type: text/html; charset=utf-8');
//Echo form for search field.
echo('<form method="post" action="9. Search.php">
		<table>
			<tr>
				<td><input type="text" name="keyword"/></td>
				<td> <input type="submit" value="Sök" name="push_button" />
			</tr>
		</table>
    </form>');
//If there is a keyword, search for the keyword and order the result after occurrences.
if($_POST['keyword']){
	$keyword = $_POST['keyword'];
	$conn = start_conn('page_search');
	$stmt = $conn->prepare("SELECT p.name AS url, COUNT(*) AS occurrences FROM page p, word w, occurrence o WHERE p.id = o.page_id AND w.id = o.word_id AND w.word =? GROUP BY p.id ORDER BY occurrences DESC");
	$stmt->bind_param("s", $keyword);
	$stmt->execute();
	$result = $stmt->get_result();
	$stmt->close();
	$conn->close();
	if($result){
		echo("<h3>Search results for '". $_POST['keyword']."':</h3>\n");
		for($i = 1; $row = $result->fetch_assoc(); $i++){
			echo("$i. <a href='".$row['url']."'>".$row['url']."</a>\n");
			echo("occurrences: (".$row['occurrences'].")<br><br>\n");
		}
	}
	// If there is no result, say so. 
	else{
		echo("No results");
	}
}
else{
}