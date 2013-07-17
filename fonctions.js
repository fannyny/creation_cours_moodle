function filltheselect(liste, choix)
{
  switch (liste)
  {
    case "compo":
      raz("filiere");
      raz("promo");
      raz("cours");
      new_option = new Option("------","");
      document.formu.elements["filiere"].options[document.formu.elements["filiere"].length] = new_option;

      for(var valeur in TCF[choix])
      {
        new_option = new Option(valeur,valeur);
        document.formu.elements["filiere"].options[document.formu.elements["filiere"].length] = new_option;
      }
      break;

    case "filiere":
      raz("promo");
      raz("cours");
      var compo = document.formu.elements["compo"].value;

      new_option = new Option("------","");
      document.formu.elements["promo"].options[document.formu.elements["promo"].length] = new_option;

      for(var valeur in TCFP[compo][choix])
      {
        new_option = new Option(valeur,valeur);
        document.formu.elements["promo"].options[document.formu.elements["promo"].length] = new_option;
      }
      break;

    case "promo":
      raz("cours");
      var compo = document.formu.elements["compo"].value;
      var filiere = document.formu.elements["filiere"].value;

      new_option = new Option("----- -","");
      document.formu.elements["cours"].options[document.formu.elements["cours"].length] = new_option;

      for(var valeur in TCFP[compo][filiere][choix])
      {
        new_option = new Option(TCFP[compo][filiere][choix][valeur],valeur);
        document.formu.elements["cours"].options[document.formu.elements["cours"].length] = new_option;

        if(contains(cours, valeur))
        {
          new_option = new Option(TCFP[compo][filiere][choix][valeur]+' ( '+auteurs[trim(valeur)]+' )',valeur);
          document.formu.elements["cours"].options[document.formu.elements["cours"].length-1] = new_option;
          document.formu.elements["cours"].options[document.formu.elements["cours"].length-1].disabled = true;
        }
      }
      break;
      break;
  }
}

function trim (myString)
{
  return myString.replace(/^\s+/g,'').replace(/\s+$/g,'')
}

/** Vérification si un tableau contient une valeur **/
function contains(a, val)
{
  var i = a.length;
  while (i--)
  {
    if (trim(a[i].toString()) == trim(val.toString()))
      return true;
  }
  return false;
}

/** Enlève les espaces de la chaine **/
function trim (myString)
{
  return myString.replace(/^\s+/g,'').replace(/\s+$/g,'')
}

/** Remise a zéro des select **/
function raz(liste)
{
  l=document.formu.elements[liste].length;
  for (i=l; i>=0; i--)
    document.formu.elements[liste].options[i]=null;
}

/** On crée le cours en ajax **/
function createCourse()
{
  //On récupère le cours sélectionné pour envoyer une requête ajax
  var etat = "create";
  var compo = encodeURIComponent(document.formu.elements["compo"].value);
  var filiere = encodeURIComponent(document.formu.elements["filiere"].value);
  var promo = encodeURIComponent(document.formu.elements["promo"].value);
  var coursId = encodeURIComponent(document.formu.elements["cours"].value);
  var coursText = encodeURIComponent(document.getElementById("cours").options[document.getElementById("cours").selectedIndex].text);
  var auteurUid = document.getElementById("auteurUid").value;
  var auteurText = document.getElementById("auteurName").value;

  if(coursId != '') loadXMLDoc(etat,compo, filiere, promo, coursId,coursText,auteurText,auteurUid);
}

/** Retirer l'enseignant d'un cours **/
function removeCourse()
{
  //On récupère le cours sélectionné pour envoyer une requête ajax
  var etat = "remove";
  var coursId = encodeURIComponent(document.formu.elements["cours"].value);
  var auteurUid = document.getElementById("auteurUid").value;
  var coursText = encodeURIComponent(document.getElementById("cours").options[document.getElementById("cours").selectedIndex].text);

  if(coursId != '') loadXMLDoc(etat,null, null, null, coursId,coursText,null,auteurUid);
}

function loadXMLDoc(etat,compo, filiere, promo, coursId, coursText,auteurText,auteurUid)
{
  document.getElementById("loading").style.display='inline';

  if (window.XMLHttpRequest)
  {
    // code for IE7+, Firefox, Chrome, Opera, Safari
    xhr = new XMLHttpRequest();
  }
  else
  {
    // code for IE6, IE5
    xhr = new ActiveXObject("Microsoft.XMLHTTP");
  }

  xhr.open("POST", "traitement.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  if(etat == 'create')
  {
    xhr.onreadystatechange = function()
    {
      if (xhr.readyState == 4 && xhr.status==200)
      {
        document.getElementById("loading").style.display='none';
        alert('Le cours a été créé. Vous pouvez le retrouver dans "Mes cours" d\'ici quelques minutes.');
        window.close();
      }
    }

    xhr.send("etat="+etat+"&compo="+compo+"&filiere="+filiere+"&promo="+promo+"&coursId="+coursId+"&coursText="+coursText+"&auteurText="+auteurText+"&auteurUid="+auteurUid);
  }
  else if(etat == 'remove')
  {
    xhr.onreadystatechange = function()
    {
      if (xhr.readyState == 4 && xhr.status==200)
      {
        document.getElementById("loading").style.display='none';
        alert('Vous avez bien été désinscrit de cet espace de cours.');
        window.close();
      }
    }

    xhr.send("etat="+etat+"&coursId="+coursId+"&auteurUid="+auteurUid+"&coursText="+coursText);
  }


}