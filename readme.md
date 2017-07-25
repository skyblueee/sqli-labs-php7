README
================
SQLI-LABS is a platform to learn SQLI(https://github.com/Audi-1/sqli-labs.git).

SQLI-LABS-PHP7 is based on SQLI-LABS

Following labs are covered for GET and POST scenarios:

1. Error Based Injections (Union Select)
	1. String
	2. Intiger
2. Error Based Injections (Double Injection Based)

3. BLIND Injections:
	1.Boolian Based
	2.Time Based
4. Update Query Injection.
5. Insert Query Injections.
6. Header Injections.
	1.Referer based.
	2.UserAgent based.
	3.Cookie based.
7. Second Order Injections
8. Bypassing WAF
	1. Bypassing Blacklist filters
		Stripping comments
		Stripping OR & AND
		Stripping SPACES and COMMENTS
		Stripping UNION & SELECT
	2. Impidence mismatch
9. Bypass addslashes()
10. Bypassing mysqli_real_escape_string. (under special conditions)
11. Stacked SQL injections.
12. Secondary channel extraction

========================================================================================
Install Instructions:

1. Unzip the contents inside the apache folder, for example under /var/www
2. This will create a folder sql-labs under it. else you can use git command from within /var/www folder.
/var/www folder and then use following command> git clone https://github.com/skyblueee/sqli-labs-php7.git sqli-labs
3. Open the file "db-creds.inc" which is under sql-connections folder inside the sql-labs folder.
4. Update your MYSQL database username and password.(default for kali are used root:)
5. From your browser access the sql-labs folder to load index.html
6. Click on the link setup/resetDB to create database, create tables and populate Data.
7. Labs ready to be used, click on lesson number to open the lesson page.
8. Enjoy the labs

==========================================================================================

Corrosponding walkthrough video tutorials and explainations can be found at:
(https://github.com/Audi-1/sqli-labs.git).
