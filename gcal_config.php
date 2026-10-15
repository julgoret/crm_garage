<?php
// Configuration de l'intégration Google Agenda (compte de service)
// 1. Google Cloud Console : créer un projet, activer l'API Google Calendar
// 2. IAM & Admin -> Comptes de service -> créer, puis créer une clé JSON
//    et déposer ce fichier ici sous le nom gcal_service_account.json
// 3. Dans Google Agenda : partager l'agenda du garage avec l'email du compte
//    de service (role "Apporter des modifications a l'agenda des evenements")
// 4. Recopier l'ID de l'agenda (Google Agenda -> Parametres -> ID d'agenda)

$GCAL_SERVICE_ACCOUNT_FILE = './gcal_service_account.json';
$GCAL_CALENDAR_ID = 'YOUR_CALENDAR_ID@group.calendar.google.com';
$GCAL_ENABLED = false; // passer a true une fois les 3 etapes ci-dessus faites
