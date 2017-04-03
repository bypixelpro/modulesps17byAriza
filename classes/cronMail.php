<?php

class cronMail {    
    public function __construct($_saveIp) {
        $mailEntities = new mailCronEntities();
        $mailEntities->subject = 'Usuarios registrados';
        $mailEntities->queryResult = $_saveIp;
        $sendMail = new mailCron($mailEntities);
        return $sendMail->sendEmail();
    }
}
