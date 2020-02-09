<?php

$motionTemplates = array();

$motionTemplates["Equipage"] = array();

$motionTemplate = array();
$motionTemplate["title"] = "L'Assemblée Permanente valide la création de l'équipage &quot;Nom de l'équipage&quot;" ;
$motionTemplate["description"] = "Pour accéder au code de fonctionnement de cet équipage, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page.";
$motionTemplate["replace"] = "[{&quot;src&quot;:&quot;Nom de l'équipage&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[&quot;équipage&quot;,&quot;equipage&quot;]}]";
$motionTemplate["label"] = "Création d'équipage";
$motionTemplates["Equipage"][] = $motionTemplate;

$motionTemplate = array();
$motionTemplate["title"] = "L'Assemblée Permanente valide la modification du code de fonctionnement de l'équipage &quot;Nom de l'équipage&quot;";
$motionTemplate["description"] = "Pour accéder à la modification du code de fonctionnement de cet équipage, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page.";
$motionTemplate["replace"] = "[{&quot;src&quot;:&quot;Nom de l'équipage&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[&quot;équipage&quot;,&quot;equipage&quot;]}]";
$motionTemplate["label"] = "Modification de code de fonctionnement d'un équipage";
$motionTemplates["Equipage"][] = $motionTemplate;

$motionTemplate = array();
$motionTemplate["title"] = "L'Assemblée Permanente valide la demande budgétaire de l'équipage &quot;Nom de l'équipage&quot;";
$motionTemplate["description"] = "Pour accéder à la demande budgétaire de cet équipage, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page.";
$motionTemplate["replace"] = "[{&quot;src&quot;:&quot;Nom de l'équipage&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[&quot;équipage&quot;,&quot;equipage&quot;]}]";
$motionTemplate["label"] = "Demande budgétaire d'un équipage";
$motionTemplates["Equipage"][] = $motionTemplate;

$motionTemplate = array();
$motionTemplate["title"] = "L'Assemblée Permanente valide le rapport de fonctionnement de l'équipage &quot;Nom de l'équipage&quot;";
$motionTemplate["description"] = "Pour accéder au rapport de fonctionnement de cet équipage, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page.";
$motionTemplate["replace"] = "[{&quot;src&quot;:&quot;Nom de l'équipage&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[&quot;rapport&quot;,&quot;fonctionnement&quot;,&quot;équipage&quot;,&quot;equipage&quot;]}]";
$motionTemplate["label"] = "Validation d'un rapport de fonctionnement";
$motionTemplates["Equipage"][] = $motionTemplate;

$motionTemplate = array();
$motionTemplate["title"] = "L'Assemblée Permanente valide la dissolution de l'équipage &quot;Nom de l'équipage&quot;";
$motionTemplate["description"] = "Pour accéder au rapport de fonctionnement de cet équipage et sa demande de dissolution, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page.";
$motionTemplate["replace"] = "[{&quot;src&quot;:&quot;Nom de l'équipage&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[&quot;rapport&quot;,&quot;fonctionnement&quot;,&quot;demande&quot;,&quot;dissolution&quot;,&quot;équipage&quot;,&quot;equipage&quot;]}]";
$motionTemplate["label"] = "Validation d'une demande de dissolution";
$motionTemplates["Equipage"][] = $motionTemplate;

// --- //

$motionTemplates["Candidature"] = array();

$motionTemplate = array();
$motionTemplate["title"] = "L'Assemblée Permanente nomme &quot;le candidat&quot; en tant que porte-parole.";
$motionTemplate["description"] = "Pour accéder à sa candidature, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page.";
$motionTemplate["replace"] = "[{&quot;src&quot;:&quot;le candidat&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[&quot;candidature&quot;,&quot;de&quot;,&quot;d'&quot;]}]";
$motionTemplate["label"] = "Candidature au porte-parolat";
$motionTemplates["Candidature"][] = $motionTemplate;

$motionTemplate = array();
$motionTemplate["title"] = "L'Assemblée Permanente inscrit &quot;le candidat&quot; au sein de l'équipe nom de l'équipe.";
$motionTemplate["description"] = "Pour accéder à sa candidature, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page.";
$motionTemplate["replace"] = "[{&quot;src&quot;:&quot;le candidat&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[&quot;candidature&quot;,&quot;de&quot;,&quot;d'&quot;]},{&quot;src&quot;:&quot;nom de l'équipe&quot;,&quot;val&quot;:&quot;parentAgendaLabel&quot;,&quot;rmv&quot;:[&quot;candidatures&quot;,&quot;candidature&quot;,&quot;pour&quot;,&quot;l'&quot;,&quot;equipe&quot;,&quot;équipe&quot;]}]";
$motionTemplate["label"] = "Candidature à une équipe";
$motionTemplates["Candidature"][] = $motionTemplate;

$motionTemplate = array();
$motionTemplate["title"] = "L'Assemblée Permanente inscrit &quot;le candidat&quot; au sein du conseil nom du conseil.";
$motionTemplate["description"] = "Pour accéder à sa candidature, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page.";
$motionTemplate["replace"] = "[{&quot;src&quot;:&quot;le candidat&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[&quot;candidature&quot;,&quot;de&quot;,&quot;d'&quot;]},{&quot;src&quot;:&quot;nom du conseil&quot;,&quot;val&quot;:&quot;parentAgendaLabel&quot;,&quot;rmv&quot;:[&quot;candidatures&quot;,&quot;candidature&quot;,&quot;pour&quot;,&quot;le&quot;,&quot;conseil&quot;]}]";
$motionTemplate["label"] = "Candidature à un conseil";
$motionTemplates["Candidature"][] = $motionTemplate;

// --- //

$motionTemplates["Motion simple"] = array();

$motionTemplate = array();
$motionTemplate["title"] = "Le Parti Pirate signe le texte &quot;Nom du texte&quot;.";
$motionTemplate["description"] = "Pour accéder au contenu du texte, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page.";
$motionTemplate["replace"] = "[{&quot;src&quot;:&quot;Nom du texte&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[&quot;signature&quot;,&quot;signer&quot;]}]";
$motionTemplate["label"] = "Signature de texte";
$motionTemplates["Motion simple"][] = $motionTemplate;

$motionTemplate = array();
$motionTemplate["title"] = "Le Parti Pirate soutient &quot;Nom du soutien&quot;.";
$motionTemplate["description"] = "Pour accéder au sujet du soutien, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page.";
$motionTemplate["replace"] = "[{&quot;src&quot;:&quot;Nom du soutien&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[]}]";
$motionTemplate["label"] = "Soutien";
$motionTemplates["Motion simple"][] = $motionTemplate;

// --- //

$motionTemplates["Motion programmatique"] = array();

$motionTemplate = array();
$motionTemplate["title"] = "L'Assemblée permanente ajoute le point programme &quot;Point programme&quot;";
$motionTemplate["description"] = "Pour accéder au point programme, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page.";
$motionTemplate["replace"] = "[{&quot;src&quot;:&quot;Point programme&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[]}]";
$motionTemplate["label"] = "Ajout d'un point programme";
$motionTemplates["Motion programmatique"][] = $motionTemplate;

$motionTemplate = array();
$motionTemplate["title"] = "L'Assemblée permanente supprime le point programme &quot;Point programme&quot;";
$motionTemplate["description"] = "Pour accéder à l'argumentaire, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page.";
$motionTemplate["replace"] = "[{&quot;src&quot;:&quot;Point programme&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[&quot;suppression&quot;,&quot;point&quot;,&quot;programme&quot;]}]";
$motionTemplate["label"] = "Suppression d'un point programme";
$motionTemplates["Motion programmatique"][] = $motionTemplate;

// --- //

$motionTemplates["Motion statutaire"] = array();

$motionTemplate = array();
$motionTemplate["title"] = "L'Assemblée Statutaire valide la modification des statuts avec l'amendement &quot;Nom du texte&quot;";
$motionTemplate["description"] = "Pour accéder à l'amendement, cliquez sur le point à l'ordre du jour dont il est question ici en pied de page.";
$motionTemplate["replace"] = "[{&quot;src&quot;:&quot;Nom du texte&quot;,&quot;val&quot;:&quot;title&quot;,&quot;rmv&quot;:[]}]";
$motionTemplate["label"] = "Amendement statutaire";
$motionTemplates["Motion statutaire"][] = $motionTemplate;


