<?

// Options Smarty

$smarty_debugging = false;
$smarty_compile_check = true;

// Paramètres de Connexion à la Base de Données

$dbuser = "llhoir";		// Utilisateur
$dbpasswd = "v3g9tk7j";		// Mot de passe
$dbserver = "localhost";	// Adresse du Serveur
$dbname = "camps";		// Nom de la base de données
$tablefiches = "fiches";	// Nom de la table des fiches

$secure = false;		// Mode sécurisé
$cryptkey = "scouts";		// Clé de décryptage du mode sécurisé

// Paramètres PDF

define('FPDF_FONTPATH','../fonts/');

// Répertoires

$ImagePath = "../img";
$IncludePath = "../include";
$SmartyClassPath = "../smarty/libs/Smarty.class.php";
$SmartyValidatePath = "../smarty/libs/SmartyValidate.class.php";

// Différentes couleurs utilisées dans le document

$color_bg = "#2947B5";
$color_txt = "#4764D1";
$color_tb1 = "#111E4F";
$color_tb2 = "#131C3F";

// Connexion à la base de données

function connect() {
	global $dbuser, $dbpasswd, $dbname, $dbserver;
	$db = mysql_connect($dbserver,$dbuser,$dbpasswd) or die ("Impossible de se connecter au serveur ".$dbserver." !");
	mysql_select_db($dbname,$db) or die ("Impossible de se connecter à la base de données ".$dbname." !");
}

// Déconnexion de la base de données

function disconnect() {
	global $dbuser, $dbpasswd, $dbname, $dbserver;
	mysql_close(mysql_connect($dbserver,$dbuser,$dbpasswd));
}

function my_upper($value, $params, &$formvars) {
        return mb_strtoupper($value,"utf-8");
}


?>
