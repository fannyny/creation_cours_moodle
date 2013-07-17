<?php
$url = urlencode('http://e-uapv2013.univ-avignon.fr/creation_cours/listes2.php');
require_once('CAS.php');
$cas_host = 'cas.univ-avignon.fr';
$cas_port = 443;
$cas_context = '';
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
phpCAS::setNoCasServerValidation();
if(!phpCAS::isAuthenticated())
	header('location:https://cas.univ-avignon.fr/login?service='.$url);
?>

<html lang="fr">
<head>
    <meta charset="utf-8">
    <link href="bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <script type="text/javascript" src="fonctions.js"></script>
</head>

<body>

<?php
	$uid = PHPCas::getUser();

	//On récupère les informations sur le user loggué
	$ldaphost = "ldap.univ-avignon.fr";
	$ldapport = 389;

	$ldapconn = ldap_connect($ldaphost, $ldapport) or die("Impossible de se connecter au serveur LDAP $ldaphost");
	if ($ldapconn)
	{
		ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

		// identification
		$ldapbind = ldap_bind($ldapconn);
		$filter="(uid=$uid)";
		if ($ldapbind)
		{
			$DNREAD = 'dc=univ-avignon,dc=fr';
			$sr = ldap_search ($ldapconn, $DNREAD, $filter);

			$infos = ldap_get_entries($ldapconn, $sr);
			$name = $infos[0]['displayname'];
		}
	}
	ldap_close($ldapconn);
?>

<!-- lecture du tableau des cours existants en PHP -->
<?php
	$row = 1;
	$dataCoursExistant = array();
	$dataAuteur = array();

	if (($handle = fopen("coursExistant.csv", "r")) !== FALSE)
	{
		while (($data = fgetcsv($handle, 1000, ";")) !== FALSE)
		{
			$num = count($data);
			$row++;
			$dataCoursExistant[] = $data[1];
			$dataAuteur[$data[1]] = $data[2];
		}
	}
	fclose($handle);
?>

<!-- lecture du tableau en PHP -->
<?php
	$tab_compo = array();
	$tab_compo_filiere = array();
	$tab_cours = array();
	$fich = "files/all_cours.csv";
	$contenu = file($fich);

	foreach ($contenu as $ligne)
	{
		$tl= explode(';',$ligne);
		if ($tl[0]!='fullname')
		{
			$fullname = $tl[0];
			$shortnamename = $tl[1];
			$category = $tl[2];
			$idcours = $tl[3];

			$tc= explode ('/',$category);
			$compo = utf8_encode($tc[0]);
			$filiere = utf8_encode($tc[1]);
			$promo = utf8_encode($tc[2]) ;

			$tab_compo[$compo] = count($tab_compo);
			$tab_compo_filiere[$compo][$filiere] = count($tab_compo_filiere);
			$tab_compo_filiere_promo[$compo][$filiere][$promo][$idcours] = utf8_encode($fullname);
		}
	}
?>

<!-- Lecture du tableau en js -->
<script>
    var TCFP = <?php echo json_encode($tab_compo_filiere_promo); ?>;
    var TCF = <?php echo json_encode($tab_compo_filiere); ?>;
    var TC = <?php echo json_encode($tab_compo); ?>;
    var cours = <?php echo json_encode($dataCoursExistant); ?>;
		var auteurs = <?php echo json_encode($dataAuteur); ?>;
</script>

<div id='blocPrincipal'>
    <div id="blocHeader">
        <div id='header'></div>
        <h1>Créer mon espace de cours</h1>
        <div id='toolbar'>
            <p>Créer mon espace de cours sur la plateforme e-uapv</p>
        </div>
    </div>
    <form name="formu" class="form-horizontal">
        <div class="control-group">
            <label class="control-label" for="compo">Choisir une composante</label>
            <div class="controls">
                <select name="compo" id="compo" class="span4" onChange='filltheselect(this.name,this.value)'>
                    <option value="" selected> ---- </option>
									<?php
									foreach($tab_compo as $cle => $valeur)
										echo('<option value="'.$cle.'">'.$cle.'</option>\n');
									?>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="filiere">Choisir une filière</label>
            <div class="controls">
                <select name="filiere" id="filiere" class="span4" onChange='filltheselect(this.name,this.value)'>
                    <option value="" selected> ---- </option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="promo">Choisir une promotion</label>
            <div class="controls">
                <select name="promo" id="promo" class="span4" onChange='filltheselect(this.name,this.value)'>
                    <option value="" selected> ---- </option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="cours">Choisir un cours</label>
            <div class="controls">
                <select name="cours" id="cours" class="span4" onChange='filltheselect(this.name,this.value)'>
                    <option value="" selected> ---- </option>
                </select>
            </div>
        </div>
				<div class='right'>
					<img src='ajax-loader.gif' id='loading' />
					<button onclick="createCourse()" class="btn btn-info" id='creer' type="button">Créer le cours</button>
				</div>
    </form>
		<input type="hidden" id="auteurName" value="<?php echo $name[0]; ?>" />
    <input type="hidden" id="auteurUid" value="<?php echo $uid; ?>" />
</div><br/>

	<p>Vous vous êtes trompé de cours ? <a href='removeCours.php' target="_blank">Cliquez ici pour vous désinscrire d'un espace de cours.</a></p>
	<p>Le cours est grisé ? Rapprochez vous de l'enseignant qui l'a récupéré.</p>

</body>
</html>