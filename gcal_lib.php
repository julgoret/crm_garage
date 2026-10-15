<?php
// Poussée des événements vers Google Agenda via l'API Calendar (compte de service)
// PHP procédural, sans librairie externe : JWT signé en OpenSSL + cURL.

include_once('./gcal_config.php');

// fabrique un token d'accès OAuth2 a partir de la clé du compte de service
function gcal_access_token()
{
  global $GCAL_SERVICE_ACCOUNT_FILE, $GCAL_ENABLED;
  if (!$GCAL_ENABLED) { return false; }
  if (!file_exists($GCAL_SERVICE_ACCOUNT_FILE)) { return false; }

  $sa = json_decode(file_get_contents($GCAL_SERVICE_ACCOUNT_FILE), true);
  if (!$sa || !isset($sa['private_key']) || !isset($sa['client_email'])) { return false; }

  $now = time();
  $header = array('alg' => 'RS256', 'typ' => 'JWT');
  $claims = array(
    'iss'   => $sa['client_email'],
    'scope' => 'https://www.googleapis.com/auth/calendar.events',
    'aud'   => 'https://oauth2.googleapis.com/token',
    'exp'   => $now + 3600,
    'iat'   => $now
  );

  $segments = array(
    gcal_b64(rtrim(str_replace(array('+', '/'), array('-', '_'), base64_encode(json_encode($header))), '=')),
    gcal_b64(rtrim(str_replace(array('+', '/'), array('-', '_'), base64_encode(json_encode($claims))), '='))
  );
  $sign_input = implode('.', $segments);

  openssl_sign($sign_input, $signature, $sa['private_key'], 'sha256WithRSAEncryption');
  $segments[] = gcal_b64(rtrim(str_replace(array('+', '/'), array('-', '_'), base64($signature)), '='));

  $post = array(
    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
    'assertion'  => implode('.', $segments)
  );

  $ch = curl_init('https://oauth2.googleapis.com/token');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  $res = curl_exec($ch);
  curl_close($ch);
  if (!$res) { return false; }
  $json = json_decode($res, true);
  if (!isset($json['access_token'])) { return false; }
  return $json['access_token'];
}

// base64 URL-safe sans padding
function gcal_b64($s)
{
  return rtrim(str_replace(array('+', '/'), array('-', '_'), base64_encode($s)), '=');
}

// appel a l'API Calendar ; $method = POST|PUT|DELETE, $path apres /calendars/<id>/events
function gcal_call($method, $path, $body = null)
{
  global $GCAL_CALENDAR_ID, $GCAL_ENABLED;
  if (!$GCAL_ENABLED) { return array('error' => 'integration desactivee'); }
  $token = gcal_access_token();
  if (!$token) { return array('error' => 'authentification impossible (verifier gcal_service_account.json)'); }

  $url = 'https://www.googleapis.com/calendar/v3/calendars/' . urlencode($GCAL_CALENDAR_ID) . '/events' . $path;
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json'
  ));
  if ($body !== null) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
  }
  $res = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  $json = $res ? json_decode($res, true) : null;
  return array('code' => $code, 'body' => $json);
}

// construit l'objet evenement Google a partir d'une ligne prospects
function gcal_build_event($row)
{
  $date_beg = ($row['time_beg'] > 1) ? date('Y-m-d', $row['time_beg']) : null;
  $date_end = ($row['time_end'] > 1) ? date('Y-m-d', $row['time_end']) : null;
  if (!$date_beg) { return null; }
  if (!$date_end) { $date_end = $date_beg; }

  $description = array();
  if (!empty($row['horaires'])) { $description[] = "Horaires : " . $row['horaires']; }
  if (!empty($row['horaire_technique'])) { $description[] = "Horaires techniques : " . $row['horaire_technique']; }
  if (!empty($row['name'])) { $description[] = "Organisateur : " . $row['name']; }
  if (!empty($row['tel'])) { $description[] = "Tel : " . $row['tel']; }
  if (!empty($row['mail'])) { $description[] = "Mail : " . $row['mail']; }
  if (!empty($row['comment_deroule'])) { $description[] = "\n" . $row['comment_deroule']; }

  return array(
    'summary'     => $row['title_event'],
    'description' => implode("\n", $description),
    'start' => array('date' => $date_beg, 'timeZone' => 'Europe/Paris'),
    'end'   => array('date' => date('Y-m-d', strtotime($date_end) + 86400), 'timeZone' => 'Europe/Paris')
  );
}

// pousse (cree ou met a jour) l'evenement ; retourne l'id Google ou false
function gcal_push_event($link, $row)
{
  $event = gcal_build_event($row);
  if (!$event) { return false; }

  if (!empty($row['gcal_event_id']))
  {
    // mise a jour de l'evenement existant
    $res = gcal_call('PUT', '/' . $row['gcal_event_id'], $event);
    if ($res['code'] == 200 && isset($res['body']['id']))
    {
      return $res['body']['id'];
    }
    // evenement distant disparu : on recree
  }

  $res = gcal_call('POST', '', $event);
  if ($res['code'] == 200 && isset($res['body']['id']))
  {
    // memoriser l'id Google dans la fiche
    $req = "UPDATE `prospects` SET `gcal_event_id`='" . addslashes($res['body']['id']) . "' WHERE `id`='" . $row['id'] . "' LIMIT 1";
    mysqli_query($link, $req);
    return $res['body']['id'];
  }
  return false;
}

// supprime l'evenement Google lie a la fiche
function gcal_delete_event($link, $row)
{
  if (empty($row['gcal_event_id'])) { return true; }
  $res = gcal_call('DELETE', '/' . $row['gcal_event_id']);
  $req = "UPDATE `prospects` SET `gcal_event_id`='' WHERE `id`='" . $row['id'] . "' LIMIT 1";
  mysqli_query($link, $req);
  return ($res['code'] == 204 || $res['code'] == 200 || $res['code'] == 404);
}
?>
