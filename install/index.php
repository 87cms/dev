<?php

global $cookie, $smarty;

define("SQL_DEBUG", true);

require_once('../classes/Error.php');
require_once('../classes/Tools.php');
require_once('../classes/Core.php');
require_once('../classes/Cookie.php');

date_default_timezone_set('Europe/Paris');

if( count($_POST) > 0 ){
	
	$fp = fopen('../config/constant.php', 'a+');
	fwrite($fp, 'define("_DOMAIN_", "'.Tools::getSuperglobal('domain').'");'."\r\n");	
	fwrite($fp, 'define("SALT", "'.generateRandomString(35).'");');
	fclose($fp);
	
	$fp = fopen('../config/db_settings.php', 'a+');
	fwrite($fp, 'define("DBHOST", "'.Tools::getSuperglobal('hostname').'");'."\r\n");
	fwrite($fp, 'define("DBNAME", "'.Tools::getSuperglobal('database').'");'."\r\n");
	fwrite($fp, 'define("DBUSER", "'.Tools::getSuperglobal('username').'");'."\r\n");
	fwrite($fp, 'define("DBPASSWORD", "'.Tools::getSuperglobal('password').'");'."\r\n");
	fwrite($fp, 'define("_DB_PREFIX_", "'.Tools::getSuperglobal('dbprefix').'");'."\r\n");
	fclose($fp);
	
	include '../config/constant.php';
	include '../config/db_settings.php';
	
	require_once('../classes/Db.php');
	require_once('../classes/Mysql.php');
	
	$fullrq = file_get_contents('dump.sql');
	Db::getInstance()->query($fullrq);
	
	$tables = Db::getInstance()->Select("SHOW TABLES FROM ".DBNAME);
	
	if( _DB_PREFIX_ ){
		for($i=1; $i<count($tables); $i++){
			$table = $tables[$i];
			Db::getInstance()->query("RENAME TABLE `".$table['Tables_in_'.DBNAME]."` TO `"._DB_PREFIX_.$table['Tables_in_'.DBNAME]."`");
		}
	}
	
	Db::getInstance()->Insert(_DB_PREFIX_.'config', array('name' => 'domain', 'value' => Tools::getSuperglobal('domain')) );
	
	Db::getInstance()->Insert(_DB_PREFIX_.'lang', 
		array(
			'name' => Tools::getSuperglobal('lang_name'), 
			'code' => Tools::getSuperglobal('lang_code'), 
			'active' => 1, 
			'defaultlang' => 1
		) 
	);
	
	Db::getInstance()->Insert(_DB_PREFIX_.'user', array(
		'email' => Tools::getSuperglobal('admin_email'),
		'password' => create_hash(Tools::getSuperglobal('admin_password')),
		'is_admin' => 1,
		'date_add' => date('Y-m-d h:m:s'),
		'date_upd' => date('Y-m-d h:m:s')
	));
	
	
	
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Install 87 CMS </title>
<link href="../admin/template/style.css" rel="stylesheet" type="text/css">
<style>
	#form_login {
		width:540px;
		margin-top:20px;	
	}
	legend {
		background: #e0e0e0;
		font-size: 16px;
		font-family: 'Lato', sans-serif;
		padding: 10px;
		display:block;
		width:100%;
		margin-bottom:10px
	}	
</style>
</head>

<body id="login_body">
<?php 
if( count($_POST) > 0 ){
?>

<h1>You can delete the Install folder, and go to your new website</h1>

<?php
}else{
?>
<div id="form_login">
    <div id="logo">
        <img src="../admin/images/logo.png" width="60">
        <span>Install</span>
    </div>
    
    <form action="#" method="post">
        <fieldset>
            <legend>1. Database</legend>
            <p>
                <label>Hostname</label>
                <input type="text" name="hostname" value="" class="text" />
            </p>
             <p>
                <label>Database</label>
                <input type="text" name="database" value="" class="text" />
            </p>
            <p>
                <label>Username</label>
                <input type="text" name="username" value="" class="text" />
            </p>
            <p>
                <label>Password</label>
                <input type="text" name="password" value="" class="text" />
            </p>
            <p>
                <label>Tables prefix</label>
                <input type="text" name="dbprefix" value="" class="text" />
            </p>
        </fieldset>
        
        <fieldset>
            <legend>2. Admin access</legend>
            <p>
                <label>Admin email</label>
                <input type="text" name="admin_email" value="" class="text" />
            </p>
            <p>
                <label>Admin password</label>
                <input type="text" name="admin_password" value="" class="text" />
            </p>
        </fieldset>
        
        <fieldset>
            <legend>3. Website</legend>
            <p>
                <label>Domain</label>
                <input type="text" name="domain"  class="text" value="<?php echo $_SERVER['HTTP_HOST']; ?>" />
            </p>
            <p>
                <label>Default lang name (ex: English)</label>
                <input type="text" name="lang_name" value=""  class="text" />
            </p>
            <p>
                <label>Default lang code (ex: en)</label>
                <input type="text" name="lang_code" value=""  class="text" />
            </p>
        </fieldset>
        
        <input type="submit" name="Submit" value="Install" class="button button_add" />
    
    </form>
</div>


<?php } 

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ$%^)-(|:!';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}


?>
</body>
</html>