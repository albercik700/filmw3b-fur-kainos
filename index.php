<!--
https://students-training-2015.herokuapp.com/

Zadanie polega na stworzeniu aplikacji webowej, która pobierze dane dotyczące filmów z bazy PostgreSQL zahostowanej na heroku oraz zwizualizowaniu ich wg. wymagań opisanych poniżej. Całość należy zahostować w chmurze (np. Heroku, AppHarbor). Kod powininen być umieszczony w repozytorium git'a (github / bitbucket). Wybór technologii jest dowolny.

Aplikacja powinna zawierać strony:

    '/' - ta strona zawiera tabele, w której widnieje ranking 20 filmów. Ranking jest tworzony przy użyciu sortowania: średnia ocen filmu malejąco oraz data ukazania się filmu rosnąco. Tabela powinna zawierać 3 kolumny: z tytułami filmu, ze średnią oceną filmu oraz kolumne z linkami do szczegółowego opisu filmu (Szczegóły w ostatnim podpunkcie).
    “/topGenre” - ta strona zawiera diagram kołowy, na którym są zaprezentowane procentowo sumy filmów z danych kategorii.
    “/search” - ta strona zawiera formularz, w którym zaimplementowane jest szukanie wyników z bazy danych na podstawie dwóch parametrów, gdzie pierwszy parametr określa gatunki filmu, drugi określa minimalną wartość średniej ocen szukanego filmu (tzn. szukamy filmów z oceną większą niż np. 7.5).
    “/movie/:id” - parametr :id to id filmu z bazy danych. Ta strona wyświetla szczegóły na temat danego filmu, w tym:
        Tytuł filmu
        Średnia ocena użytkowników
        Gatunki jakie reprezentuje dany film
        Opis filmu, który zostanie pobrany przy pomoc imdb api (http://www.omdbapi.com/)

Do zadania należy użyć bazy danych z filmami. Dane do połączenia:

    host: ec2-54-217-202-110.eu-west-1.compute.amazonaws.com
    database: d8u6uelvine6d6
    port: 5432
    user: iwzexazhfjxbbt
    password: 4JVMJFooosyfdM5Y79Si-c691D
    ssl: true



W razie problemów z ilością połączeń do bazy, można zahostować własną.

W razie innych problemów lub wątpliwości pisz na konkurs@kainos.com
--!>
<html>
<head>
	<title>FilmW3b</title>
	<link href="/filmdb/styl.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<div id="kontener">
	<div id="header">
	<ul>
	<li><a href="/filmdb/Home">Strona głowna</a></li>
	<li><a href="/filmdb/topGenre">Diagram</a></li>
	<li><a href="/filmdb/search">Search</a></li>
	</ul>
	<br /><hr style="color:white;" /><br />
	</div>
<div id="show">
<?php
	try{
		$conn=new PDO("pgsql:host=ec2-54-217-202-110.eu-west-1.compute.amazonaws.com dbname=d8u6uelvine6d6 user=iwzexazhfjxbbt password=4JVMJFooosyfdM5Y79Si-c691D port=5432");
		if(isset($_GET['p']) and is_numeric($_GET['p'])){
			echo "<center><b>Szczegóły filmu</b></center><br/>";
			try{
				$query=$conn->prepare("select m.original_title,m.title,m.release_date,m.vote_average,m.vote_count from movie m where m.id=:idek");
				$query->bindParam(":idek",$_GET['p'],PDO::PARAM_INT);
				$query->execute();
				if($query->rowCount()>0){
					foreach($query as $row){}
					echo "<table>\n";
					echo "<tr><td style=\"text-align:center; background-color:#e2e2e2; font-weight: bold; font-size:11px;\">Oryginalny tytuł</td><td>".$row['original_title']."</td></tr>\n";
					echo "<tr><td style=\"text-align:center; background-color:#e2e2e2; font-weight: bold; font-size:11px;\">Tytuł</td><td>".$row['title']."</td></tr>\n";
					echo "<tr><td style=\"text-align:center; background-color:#e2e2e2; font-weight: bold; font-size:11px;\">Data wydania</td><td>".$row['release_date']."</td></tr>\n";
					echo "<tr><td style=\"text-align:center; background-color:#e2e2e2; font-weight: bold; font-size:11px;\">Średnia ocena</td><td>".$row['vote_average']."</td></tr>\n";
					echo "<tr><td style=\"text-align:center; background-color:#e2e2e2; font-weight: bold; font-size:11px;\">Ilość głosów</td><td>".$row['vote_count']."</td></tr>\n";
					$query=$conn->prepare("select g.name from movie m left join movie_genre mg on m.id=mg.movie_id left join genre g on mg.genre_id=g.id where m.id=:idek");
					$query->bindParam(":idek",$_GET['p'],PDO::PARAM_INT);
					$query->execute();
					if($query->rowCount()>0){
						echo "<tr><td style=\"text-align:center; background-color:#e2e2e2; font-weight: bold; font-size:11px;\">Kategorie</td><td>";
						foreach($query as $cats){
							echo $cats['name']." ";
						}
						echo "</td></tr>\n";
					}
					$title=rawurlencode($row['title']);
					$opis_jsn=json_decode(file_get_contents("http://www.omdbapi.com/?t=".$title."&y=&plot=short&r=json"),true);
					if($opis_jsn['Response']=='True'){
						echo "<tr><td style=\"text-align:center; background-color:#e2e2e2; font-weight: bold; font-size:11px;\">Opis</td><td>".$opis_jsn['Plot']."</td></tr>\n";
					}
					$query->closeCursor();
				}else{
					echo "Nie ma takiego filmu w bazie";
				}
			}catch(PDOexception $e){
				echo "Błąd!";
			}
		}else if(isset($_GET['p']) and ($_GET['p']=='topGenre')){
			$query=$conn->query("select g.name,count(g.name)as count,(select count(*) from movie_genre) as sum from movie m left join movie_genre mg on m.id=mg.movie_id left join genre g on mg.genre_id=g.id group by g.name order by count desc");
			$chart="http://chart.apis.google.com/chart?chs=800x375&chd=t:";
			$nazwa='';
			foreach($query as $row){
				$chart=$chart.round(($row['count']*100)/$row['sum'],0).",";
				$nazwa=$nazwa.$row['name']." ".round(($row['count']*100)/$row['sum'],1)."%|";
			}
			$chart=trim($chart,',')."&cht=p3&chl=".$nazwa;
			$query->closeCursor();
			echo "<center><b>Diagram prezentujący procentowy udział filmów danej kategorii w bazie</b></center><br/>";
			echo "<img src=\"".$chart."\" alt=\"Wykres\" border=\"0\"/>";
		}else if(isset($_GET['p']) and $_GET['p']=='search'){
			if(isset($_POST['vote']) && isset($_POST['cat']) && is_numeric($_POST['vote']) && $_POST['vote']<=10){
				$query=$conn->prepare("select m.id,m.original_title||' / '||m.title as title,m.vote_average,g.name from movie m left join movie_genre mg on m.id=mg.movie_id left join genre g on mg.genre_id=g.id where g.name like :catt and m.vote_average>=:vote order by m.vote_average desc;");
				$cat="%".$_POST['cat']."%";
				$query->bindParam(":catt",$cat,PDO::PARAM_STR);
				$query->bindParam(":vote",$_POST['vote'],PDO::PARAM_INT);
				$query->execute();
				echo "Wyniki wyszukiwania dla kategorii <b>".htmlspecialchars($_POST['cat'])."</b> i średniej oceny <b>".htmlspecialchars($_POST['vote'])."</b><br/><br />";
				echo "<table>\n<tr style=\"text-align:center; background-color:#e2e2e2; font-weight: bold; font-size:11px;\"><td>Oryginalny / Tłumaczenie</td><td>Średnia ocena</td><td>Szczegóły filmu</td></tr>\n";
				foreach($query as $row){
					echo "<tr><td>".$row['title']."</td><td style=\"text-align:center;\">".$row['vote_average']."</td><td style=\"text-align:center;\"><a href=movie/".$row['id'].">Szczegóły</a></td></tr>\n";
				}
			}else{
				$query=$conn->query("select g.name from genre g;");
				echo "<center><b>Wybierz kategorię i podaj ocenę</b></center><br/>";;
				echo "<form method=\"POST\" action=\"search\" enctype=\"application/x-www-form-urlencoded\">\n";
				echo "Ocena: <input type=\"text\" name=\"vote\"><br/>\n";
				echo "Kategoria: <select  name=\"cat\">";
				foreach($query as $row){
					echo "<option>".$row['name']."</option>\n";
				}
				echo "</select><br/>\n<input type=\"submit\" value=\"send\"/>\n</form>\n";
			}
		}else{
			$query=$conn->query("select id,original_title|| ' / ' ||title as title,vote_average from movie order by vote_average desc,release_Date asc limit 20;");
			echo "<center><b>TOP 20</b></center><br/>";
			echo "<table>\n<tr style=\"text-align:center; background-color:#e2e2e2; font-weight: bold; font-size:11px;\"><td>Oryginalny / Tłumaczenie</td><td>Średnia ocena</td><td>Szczegóły filmu</td></tr>\n";
			foreach($query as $row){
				echo "<tr><td>".$row['title']."</td><td style=\"text-align:center;\">".$row['vote_average']."</td><td style=\"text-align:center;\"><a href=movie/".$row['id'].">Szczegóły</a></td></tr>\n";
			}
			echo "</table>\n";
		}
	}catch(PDOexception $e){
		echo "Błąd połączenia z bazą!";
	}
?>
	</div>
</div>
</body>
</html>