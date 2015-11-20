<?php
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
?>