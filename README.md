# filmw3b-fur-kainos
Zadanie polega na stworzeniu aplikacji webowej, która pobierze dane dotyczące filmów z bazy PostgreSQL zahostowanej na heroku oraz zwizualizowaniu ich wg. wymagań opisanych poniżej. Całość należy zahostować w chmurze (np. Heroku, AppHarbor). Kod powininen być umieszczony w repozytorium git'a (github / bitbucket). Wybór technologii jest dowolny.

Aplikacja powinna zawierać strony:

    “/” - ta strona zawiera tabele, w której widnieje pierwsze 20 filmów z rankingu. Ranking jest tworzony przy użyciu sortowania: średnia ocen filmu malejąco oraz data ukazania się filmu rosnąco. Tabela powinna zawierać 3 kolumny: z tytułami filmu, ze średnią oceną filmu oraz kolumne z linkami do szczegółowego opisu filmu (Szczegóły w ostatnim podpunkcie).
    “/topGenre” - ta strona zawiera diagram kołowy, na którym są zaprezentowane procentowo wystąpienia danego gatunku w bazie danych.
    “/search” - ta strona zawiera formularz, w którym zaimplementowane jest szukanie wyników z bazy danych na podstawie dwóch parametrów, gdzie pierwszy parametr określa gatunki filmu, drugi określa minimalną wartość średniej ocen szukanego filmu (tzn. szukamy filmów z oceną większą niż np. 7.5).
    “/movie/:id” - parametr :id to id filmu z bazy danych. Ta strona wyświetla szczegóły na temat danego filmu, w tym:
        Tytuł filmu
        Średnia ocena użytkowników
        Gatunki jakie reprezentuje dany film
        Opis filmu, który zostanie pobrany przy pomoc imdb api (http://www.omdbapi.com/)
