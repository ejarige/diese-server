<?php
include_once('../dbconfig.php');
include_once('../Eventful.php');
header('Access-Control-Allow-Origin:*');

if(!$_POST['date'])     $_POST['date']      = 'Future';
if(!$_POST['location']) $_POST['location']  = 'France';

$params = array(
    'date'        => $_POST['date'],
    'location'    => $_POST['location'],
    'image_sizes' => 'large,thumb',
    'category'    => 'music',
    'page_size'   => '25'
);

if(isset($_POST['keywords']) && $_POST['keywords'])
    $params['keywords'] = urlencode($_POST['keywords']);


if(isset($_POST['page']) && $_POST['page'])
    $params['page_number'] = urlencode($_POST['page']);

$search = new Eventful();
$search
    ->service('search')
    ->setParams($params)
    ->exec();

$res = $search->get();

$tab = json_decode($res);

// Mise en cache
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pwd);

    $values = [];
    if($tab->events->event != null){
        foreach($tab->events->event as $event) {
            $start_time = $event->all_day ? 1 : date("U", strtotime($event->start_time));

            $values[] = array(
                ":eventful_id"      => $event->id                           ? $event->id                          : '',
                ":description"      => $event->description                  ? $event->description                 : '',
                ":title"            => $event->title                        ? $event->title                       : '',
                ":img"              => $event->image->large->url            ? $event->image->large->url           : '',
                ":thumb"            => $event->image->thumb->url            ? $event->image->thumb->url           : '',
                ":start_time"       => $start_time                          ? $start_time                         : '',
                ":venue_name"       => $event->venue_name                   ? $event->venue_name                  : '',
                ":venue_address"    => $event->venue_address                ? $event->venue_address               : '',
                ":city_name"        => $event->city_name                    ? $event->city_name                   : '',
                ":region_name"      => $event->region_name                  ? $event->region_name                 : '',
                ":postal_code"      => $event->postal_code                  ? $event->postal_code                 : '',
                ":country_name"     => $event->country_name                 ? $event->country_name                : '',
                ":artist"           => $event->performers->performer->id    ? $event->performers->performer->id   : '',
                ":artist_name"      => $event->performers->performer->name  ? $event->performers->performer->name : ''
            );
        }

        $stmt = $pdo->prepare("INSERT INTO concerts (
                          eventful_id,
                          description,
                          title,
                          img,
                          thumb,
                          start_time,
                          venue_name,
                          venue_address,
                          city_name,
                          region_name,
                          postal_code,
                          country_name,
                          artist_eventful_id,
                          artist_name
                        ) VALUES (
                          :eventful_id,
                          :description,
                          :title,
                          :img,
                          :thumb,
                          :start_time,
                          :venue_name,
                          :venue_address,
                          :city_name,
                          :region_name,
                          :postal_code,
                          :country_name,
                          :artist
                          :artist_name
                        )");

        foreach($values as $v){
            $stmt->execute($v);
        }
    }

} catch (PDOException $e) {
    http_response_code(503);
}

http_response_code(200);
echo $tab->events->event != null ? $res : '[]';

		