<?php

class mailCronEntities {
    public $template = 'cron',
        $queryResult = null,
        $subject,
        $fileAttachment = null,
        $mode_smtp = false;
}

class mailCron {
    
    private $idLang, 
            $template, 
            $queryResult, 
            $templateVars, 
            $subject, 
            $to, 
            $toName, 
            $from, 
            $fromName, 
            $fileAttachment, 
            $mode_smtp;
    
    public function __construct(mailCronEntities $_obj) {
        $this->idLang = Context::getContext()->language->id;
        $this->template = $_obj->template;
        $this->subject = $_obj->subject;
        $this->queryResult = $_obj->queryResult;
        $this->to = Configuration::get('PS_SHOP_EMAIL');
        $this->toName = Configuration::get('PS_SHOP_NAME');
        $this->from = Configuration::get('PS_SHOP_EMAIL');
        $this->fromName = Configuration::get('PS_SHOP_NAME');
        $this->fileAttachment = $_obj->fileAttachment;
        $this->mode_smtp = $_obj->mode_smtp;
        $this->prepareTemplateVars();
    }
    
    public function sendEmail() {     
        
        return MailCore::Send(
                    $this->idLang, 
                    $this->template, 
                    $this->subject, 
                    $this->templateVars, 
                    $this->to, 
                    $this->toName, 
                    $this->from, 
                    $this->fromName, 
                    $this->fileAttachment, 
                    $this->mode_smtp
                );
    }
    
    private function prepareQueryResult() {
        $html = '<table style="width:100%">
                    <tr>
                      <th>' . 'Ip' . '</th>
                      <th>' . 'Fecha' . '</th>
                      <th>' . 'Visitas' . '</th>
                      <th>' . 'Navegador' . '</th>
                    <tr>';                
        if(is_array($this->queryResult)) {
            foreach($this->queryResult as $queryIp) {
                $html .= '<tr>';
                    $html .= '<td>' . $queryIp[ipEntitiesNames::IP] . '</td>';
                    $html .= '<td>' . $queryIp[ipEntitiesNames::DATE] . '</td>';
                    $html .= '<td>' . $queryIp[ipEntitiesNames::NUM_VISITS] . '</td>';
                    $html .= '<td>' . $queryIp[ipEntitiesNames::BROWSER] . '</td>';
                $html .= '</tr>';
            }
        }                
        $html .= '</table>';
        return $html;
    }
    
    private function prepareTemplateVars() {
        if (!is_array($this->templateVars)) {
            $this->templateVars = array();
        }
        
        $this->templateVars = array_map(array('Tools', 'htmlentitiesDecodeUTF8'), $this->templateVars);
        $this->templateVars = array_map(array('Tools', 'stripslashes'), $this->templateVars);
        
        $idShop = (int)Context::getContext()->shop->id;
        $message = \Swift_Message::newInstance();
        
        $logo = null;
        if (Configuration::get('PS_LOGO_MAIL') !== false && file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO_MAIL', null, null, $idShop))) {
                $logo = _PS_IMG_DIR_.Configuration::get('PS_LOGO_MAIL', null, null, $idShop);
            } else {
                if (file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO', null, null, $idShop))) {
                    $logo = _PS_IMG_DIR_.Configuration::get('PS_LOGO', null, null, $idShop);
                } else {
                    $this->templateVars['{shop_logo}'] = '';
                }
            }
            ShopUrl::cacheMainDomainForShop((int) $idShop);
            if (isset($logo)) {
                $this->templateVars['{shop_logo}'] = $message->embed(\Swift_Image::fromPath($logo));
            }

            if ((Context::getContext()->link instanceof Link) === false) {
                Context::getContext()->link = new Link();
            }

            $this->templateVars['{shop_name}'] = Tools::safeOutput(Configuration::get('PS_SHOP_NAME', null, null, $idShop));
            $this->templateVars['{shop_url}'] = Context::getContext()->link->getPageLink('index', true, Context::getContext()->language->id, null, false, $idShop);
            $this->templateVars['{html}'] = $this->prepareQueryResult();
    }
}
