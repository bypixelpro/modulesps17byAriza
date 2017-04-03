<?php
require '../../../../config/config.inc.php';
require '../../../../init.php';
require '../../models/ipModel.php';
require '../../controllers/saveIp.php';
require '../../classes/mail.php';
require '../../classes/cronMail.php';

$date = date("Y-m-d 00:00:00");
$saveIp = saveIp::getCronSelect($date);
$mail = new cronMail($saveIp);
if($mail) {
    echo 'Ips Mandada';
} else {
    echo 'Ha ocurrido un error al enviar el email';
}