# MySQL-Easy-DB-Backup-Restore
Really simple PHP class for easy backup, restore, and truncate database. 

Easily backup and restore your database with simpler line of code!
```sh
require_once("../path/to/EasyBackupRestore.php");
#database connection configuration
$config = array(
	"host" => "localhost",
	"dbname" => "lat_backup",
	"username" => "root",
	"password" => ""
);
$br = new EasyBackupRestore($config);

#Database backup query as easy as this line of code : 
$br->backup();

#Database restore from SQL file as easy as this line of code :
$br->restore("path/to/exportedfile.sql");
```

# Options
You can backup database with 2 methods : 
 - Automatic Backup
 - Determined saved location 

```sh
#Automatic backup
$br->backup();

#Determined saved location
$savedir = "path/to/directory";
$br->backup(true, $savedir);
```

Feel free to contact me at tianrosandhy@gmail.com~
