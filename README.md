# Yahoo News Nest - *a web crusader*

A Solr search engine with PHP web userinterface to index and search yahoo news articles.

### Web Crawler:

Extended crawler4j, an open source web crawler for Java, to crawl Yahoo News articles with the following configurations:
Crawling seed: https://www.yahoo.com/news
Crawling depth: 16
Number of crawlers: 20000

Under *Crawler*, please refer to *MyController.java* for controller for the crawler and *MyCrawler.java* for the implementation of crawler logic.

Crawling statistics can be found in the following files:
visit_yahoo.csv - statistics of news artciles visited.
fetch_yahoo.csv - statistics of news articles actually fetched.

### Apache Solr:

Solr schema file: *managed-schema*
Solr configurations file: *solrconfig.xml*

### User Interface:

Script: *search_ui.php*
Technologies: PHP, HTML, jQuery, Bootstrap

A [HTML DOM Parser in PHP] (https://simplehtmldom.sourceforge.io/) is used for easy manipulation of HTML.
Spell corrector feature is makes use of Peter Norvig's [dictionary] (www.norvig.com/big.txt).

### Snapshots:

Homepage:
![alt text](screenshots/Homepage.PNG)

Autocompletion feature:
![alt text](screenshots/autocomplete.PNG)

Spellcorrection feature:
![alt text](screenshots/spell-correct.PNG)

Search results:
![alt text](screenshots/results.PNG)
