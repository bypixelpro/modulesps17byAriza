<?php

if(!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class mymodule extends Module implements WidgetInterface {
     
    public $controls = array();
    
    public $button = array();
    
    //put your code here
    public function __construct() {
        
        $this->name = "mymodule";
        
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Daniel Ariza';
        $this->need_instance = 0;
        
        $this->bootstrap = true;
      
        parent::__construct(); 

        $this->displayName = $this->trans('Mi primer módulo', array(), 'Module.mymodule');
        $this->description = $this->trans('Módulo desde cero con el equipo de Pixelpro.', array(), 'Module.mymodule');
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);    
        
        $this->createControls();
        
    }
    
    public function install() {
        Configuration::updateValue('MYMODULE_LIVE_MODE', false);
        
        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('footer') && 
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayHome') && 
            $this->registerHook('displayLeftColumn');
    }
    
    /**
     * Controls
     */
    protected function createControls() {
        
        $this->controls['MYMODULE_SAVE_NAME'] = array(
            'controlName' => 'MYMODULE_SAVE_NAME',
            'values' => null,
            'label' => $this->l('Name'),
            'desc' => $this->l('Enter your Name')
        );        
        $this->controls['MYMODULE_SAVE_LAST_NAME'] = array(
            'controlName' => 'MYMODULE_SAVE_LAST_NAME',
            'values' => null,
            'label' => $this->l('Last Name'),
            'desc' => $this->l('Enter your Last Name')
        );
        // Button Save
        $this->button['MYMODULE_SAVE_FORM'] = array(
            'controlName' => 'MYMODULE_SAVE_FORM',
            'label' => $this->l('Save'),
        );
    }

    public function uninstall() {
        Configuration::deleteByName('MYMODULE_LIVE_MODE');        
        return parent::uninstall();
    }
    
    public function getContent() {
        
        if((bool) Tools::isSubmit('submitMymodule')) {
            $this->postProcess();
        }
          /**
         * Custom Save
         */
        if((bool) Tools::isSubmit($this->button['MYMODULE_SAVE_FORM']['controlName'])) {
            $this->customPostProcess();
        }
        
        foreach($this->controls as $control) {
            $this->controls[$control['controlName']]['values'] = $this->getLangValues($control['controlName']);
        }
        
        $this->context->smarty->assign($this->name, array(
            'path' => $this->_path,
            'languagesArray' => $this->context->controller->getLanguages(),
            'currentLang' => $this->context->language->id,
            'customControls' => $this->controls,
            'saveButton' => $this->button,
            'postAction' => $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name .'&token='.Tools::getAdminTokenLite('AdminModules') 
        ));
        
        $customTpl = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        $autoGenerateTpl = $this->renderForm();
        
        return $autoGenerateTpl . $customTpl;
    }
    
     /**
     * Get Values
     * @param type $_controlName
     * @return type array
     */
    public function getLangValues($_controlName) {
        $languages = $this->context->controller->getLanguages();
        $values = array();
        foreach($languages as $lang) {
            $composeName = $_controlName . '_' . $lang["id_lang"];
            $values[$lang["id_lang"]] = Configuration::get($composeName);
        }
        return $values;
    }
    
    protected function getConfigForm() {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'MYMODULE_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),                
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'MYMODULE_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                        'lang' => true
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'MYMODULE_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password')
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }
    
    protected function getConfigFormValues () {
        $languages = Language::getLanguages(false);
        $custom_text = array();        
        foreach ($languages as $lang) {
            $custom_text[$lang['id_lang']] = Tools::getValue('MYMODULE_ACCOUNT_EMAIL_'.$lang['id_lang'], Configuration::get('MYMODULE_ACCOUNT_EMAIL', $lang['id_lang']));
        }
        return array(
            'MYMODULE_LIVE_MODE' => Tools::getValue('MYMODULE_LIVE_MODE', Configuration::get('MYMODULE_LIVE_MODE')),
            'MYMODULE_ACCOUNT_EMAIL' => $custom_text,
            'MYMODULE_ACCOUNT_PASSWORD' => Tools::getValue('MYMODULE_ACCOUNT_PASSWORD', Configuration::get('MYMODULE_ACCOUNT_PASSWORD')),
        );   
    }
    
    protected function renderForm() {
        $helper = new HelperForm();
        
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitMymodule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );   
    
        return $helper->generateForm(array($this->getConfigForm()));
    }
    
    protected function postProcess() {
        $formValues = $this->getConfigFormValues();        
        foreach(array_keys($formValues) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }
    
    /**
     * Save Custom Fields
     */
    protected function customPostProcess() {    
        $languages = $this->context->controller->getLanguages();
        foreach($this->controls as $control) {
            foreach($languages as $lang) {
                $composeName = $control['controlName'] . '_' . $lang["id_lang"];
                Configuration::updateValue($composeName, Tools::getValue($composeName));
            }
        }
    }
    
    public function hookHeader() {
        
    }
    
    public function hookFooter() {
        
    }
    
    public function hookBackOfficeHeader() {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addCSS($this->_path.'views/css/mymoduleAdmin.css');
        }
    }
    
    public function hookDisplayHome() {
         $this->context->smarty->assign($this->name, array(
            'path' => $this->_path
        ));
        
        return $this->context->smarty->fetch($this->local_path.'views/templates/hook/displayHome.tpl');
    }
    
    public function hookDisplayLeftColumn() {
        
    }

    public function renderWidget($hookName, array $configuration) {
        
    }
    
    public function getWidgetVariables($hookName, array $configuration) {
        
    }
}
