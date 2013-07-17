<?php require_once('CAS.php'); ?>

<?php
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
?>

<?php
	header("Content-Type: text/plain");

	//On récupère les données passées en paramètre
	$etat = $_POST['etat'];

	if($etat == 'create')
	{
		$compo = $_POST['compo'];
		$filiere = $_POST['filiere'];
		$promo = $_POST['promo'];
		$name = $_POST['auteurText'];
	}

	$uid = $_POST['auteurUid'];
	$coursId = $_POST['coursId'];
	$coursText = $_POST['coursText'];

	// On veut créer un cours
	if($etat == 'create')
	{
		//On écrit dans un fichier csv le cours a ajouté
		$fichier = "cours.csv";
		$fic = fopen($fichier,'w+');
		$ch = "fullname;shortname;category;idnumber;summary\n";
		fwrite($fic,$ch);

		//On écrit le cours en question dans le csv
		$category = strtolower($compo).'/'.strtolower($filiere).'/'.strtolower($promo);
		$cours = strtolower($coursText).";".strtolower($coursText).";".$category.";".trim($coursId).";".strtolower($coursText)."\n";
		fwrite($fic,$cours);
		fclose($fic);

		//On exécute le script pour ajouter un cours
		$commande = "/usr/bin/php ../admin/tool/uploadcourse/cli/uploadcourse.php --action=addnew --file=/nfswebuapv/e-uapv2013.univ-avignon.fr/www/creation_cours/".$fichier." --delimiter=semicolon --verbose";
		exec($commande);

		//On remplit le deuxième fichier qui permet de voir les cours déjà créé
		$fichierCours = "coursExistant.csv";
		$fic = fopen($fichierCours,'a+');

		$octet=filesize($fichierCours);
		if ($octet==0)
		{
			$ch = "textCours;idCours;name;uidCreator;date\n";
			fwrite($fic,$ch);
		}

		$ch = trim($compo." > ".$filiere." > ".$promo." > ".$coursText).";".trim($coursId).";".$name.";".$uid.";".date('d/m/y H:i')."\n";
		fwrite($fic,$ch);

		//On écrit dans le fichier des enseignants
		$fichierCours = "enroll.csv";
		$fic = fopen($fichierCours,'a+');
		$ch = "add,editingteacher,".$uid.",".$coursId."\n";
		fwrite($fic,$ch);
	}
	else if($etat == 'remove')
	{
		//On supprime du deuxième fichier le cours associé déjà créé
		$fichierCours = "coursExistant.csv";
		$dataCoursExistant = array();

		$contenu = file($fichierCours);

		foreach ($contenu as $ligne)
		{
			$tl= explode(';',$ligne);
			if($tl[0] != $coursText)
				$dataCoursExistant[] = $ligne;
		}

		$fic = fopen($fichierCours,'w+');
		foreach($dataCoursExistant as $data)
			fwrite($fic,$data);

		//On écrit dans le fichier des enseignants qu'on veut le supprimer
		$fichierCours = "enroll.csv";
		$fic = fopen($fichierCours,'a+');
		$ch = "del,editingteacher,".$uid.",".$coursId."\n";
		fwrite($fic,$ch);
	}

	//On écrit dans le fichier de log
	$log = "log.txt";
	$fic = fopen($log,'a+');
	fwrite($fic,$ch);

	//On lance la commande pour ajouter l'enseignant au cours
	exec('/usr/bin/php /nfswebuapv/e-uapv2013.univ-avignon.fr/www/admin/cli/cron.php');
?>