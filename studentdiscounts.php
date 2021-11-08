<?php
/**
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once('classes/StudentDomainClass.php');
require_once('classes/StudentDomains.php');
require_once('classes/StudentDiscountClass.php');
require_once('classes/StudentDiscountRepo.php');

class Studentdiscounts extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'studentdiscounts';
        $this->tab = 'others';
        $this->version = '1.0.0';
        $this->author = 'Michał Drożdżyński';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Student discounts');
        $this->description = $this->l('The module allows users to create a student account that entitles you to discounts');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');

        $request = 'SELECT `id_group` FROM `' . _DB_PREFIX_ . 'group_lang` WHERE name = "Studenci"';
        $group = Db::getInstance()->getRow($request);
        

        /** @var array $result */
        if (!$group) {
            $result = Db::getInstance()->getRow($request);
            $my_group = new Group();
            $my_group->name = array(Configuration::get('PS_LANG_DEFAULT') => 'Studenci');
            $my_group->price_display_method = 1;
            $my_group->add();
        }
        $request = 'SELECT `id_group` FROM `' . _DB_PREFIX_ . 'group_lang` WHERE name = "Studenci"';
        $group = Db::getInstance()->getRow($request);
        $groupId = $group['id_group'];

        Configuration::updateValue('STUDENT_GROUP', $groupId);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('actionStudentDiscount') &&
            $this->registerHook('displayStudentAccount')
            && $this->installTab();
    }

    public function hookDisplayStudentAccount() {
        $email = $this->context->customer->email;

        if (StudentDiscountRepo::existStudentWithEmail($email)) {
            return $this->display(__FILE__, 'views/templates/hook/studentaccount.tpl');
        }
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        Configuration::deleteByName('STUDENT_GROUP');

        return parent::uninstall() &&
            $this->uninstallTab();
    }

    public function enable($force_all = false)
    {
        return parent::enable($force_all)
            && $this->installTab()
        ;
    }

    public function disable($force_all = false)
    {
        return parent::disable($force_all)
            && $this->uninstallTab()
        ;
    }

    private function installTab()
    {
        $tabId = (int) Tab::getIdFromClassName('AdminStudentDomain');
        if (!$tabId) {
            $tabId = null;
        }

        $tab = new Tab($tabId);
        $tab->active = 1;
        $tab->class_name = 'AdminStudentDomain';
        $tab->name = array();
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans('Student Domains', array(), 'Modules.MyModule.Admin', $lang['locale']);
        }
        $tab->id_parent = (int) Tab::getIdFromClassName('ShopParameters');
        $tab->module = $this->name;

        return $tab->save();
    }

    private function uninstallTab()
    {
        $tabId = (int) Tab::getIdFromClassName('AdminStudentDomain');
        if (!$tabId) {
            return true;
        }

        $tab = new Tab($tabId);

        return $tab->delete();
    }
    public function getContent()
    {
        if (Tools::isSubmit('submitGroupConfiguration')) {
            $groupId= Tools::getValue('group');
            Configuration::updateValue('STUDENT_GROUP', $groupId);
        }
        
        if (Tools::isSubmit('addDomains')){
            return $this->addDomain();
        }

        if (Tools::isSubmit('viewstudentdiscounts')){
            return $this->viewStudent();
        }

        if (Tools::isSubmit('submitViewStudent')){
            if(Tools::getValue('active')) {
                StudentDiscountRepo::active(Tools::getValue('studentId'));
            }
        }

        if (Tools::isSubmit('submitAddDomain')) {
            $domain= Tools::getValue('domain');
            if(strlen($domain) > 3) {
                StudentDomains::add($domain);
            }
            $file = $_FILES['csv-file'];
            if (isset($file)) {
                $target_dir = _PS_MODULE_DIR_. 'studentdiscounts/upload/';
                $target_file = $target_dir . basename($file["name"]);
                $file_name = basename($file["name"]);
                $uploadOk = 1;
                $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

                if($imageFileType != "csv" ) {
                    $uploadOk = 0;
                }
                // Check if $uploadOk is set to 0 by an error
                if ($uploadOk == 1)
                {
                    if (move_uploaded_file($file["tmp_name"], $target_file))
                    {
                        $domains = array_map('str_getcsv', file($target_file));

                        foreach($domains as $domain) {
                            if(strlen($domain[0]) > 3) {
                                StudentDomains::add($domain[0]);
                            }
                        }
                    }
                }
            }
        }

        $delete = Tools::getValue('del'); 
        $confirm = Tools::getValue('confirm'); 
        $id = Tools::getValue('id');
        $domain = Tools::getValue('domain');
        if ($id && $confirm == 'confirm') {
            StudentDiscountRepo::confirm($id);
            if ($domain) {
                StudentDiscountRepo::confirmByDomain($domain);
            }
        } else if ($id && $delete == 'delete') {
            StudentDiscountRepo::delete($id);
        }

        $studentId = Tools::getValue("id_studentdiscounts");
        $deleteStudentDiscounts = Tools::getValue("deletestudentdiscounts");
        $statusstudentdiscounts = Tools::getValue("statusstudentdiscounts");
        $activestudentdiscounts = Tools::getValue("activestudentdiscounts");
        if ($deleteStudentDiscounts !== false) {
            StudentDiscountRepo::delete($studentId);
        } else if ($statusstudentdiscounts !== false) {
            StudentDiscountRepo::confirm($studentId);
        } else if ($activestudentdiscounts !== false) {
            StudentDiscountRepo::active($studentId);
        }

        $domainId = Tools::getValue("id_student_domain");
        $deleteStudentDomain = Tools::getValue("deletestudent_domain");
        if ($deleteStudentDomain !== false) {
            StudentDomains::delete($domainId);
        }/*
        $this->context->smarty->assign('settingStudentDiscount', $this->renderForm());
        $this->context->smarty->assign('studentDomains', $this->domainList());
        $this->context->smarty->assign('studentEmailVerification', $this->studentList());*/
        //return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/menu.tpl');
        return $this->renderForm() . $this->domainList() . $this->studentList() . $this->studentActiveList();
    }

    private function viewStudent() {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('View student'),
                    'icon' => 'icon-link',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Active'),
                        'name' => 'active',
                        'is_bool' => true,
                        'default_value' => 1,
                        'values' => array(
                          array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                          ),
                          array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                          )
                                  )
                          ],
                    [
                        'type' => 'hidden',
                        'name' => 'studentId'
                    ],
                ],
                'submit' => [
                    'name' => 'submitViewStudent',
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
            ],
        ];
       

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = 'studentdiscounts';
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = 'id_studentdiscounts';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', true) .
            '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];
        $studentId = Tools::getValue("id_studentdiscounts");
        $helper->fields_value = array(
            'active' => StudentDiscountRepo::isActive($studentId),
            'studentId' => $studentId,
       );
        
        $studentCart = [];
        $images = $this->getImageById($studentId);
        foreach ($images as $image) {
            array_push($studentCart, $image['image']);
        }

        $this->context->smarty->assign('studentCart', $studentCart);
        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/studentdiscounts.tpl');
        return $output . $helper->generateForm([$fields_form]);
    }

    private function getImageById($id) {
        $query = 'SELECT image FROM `' . _DB_PREFIX_ . 'studentdiscounts_image` WHERE 	id_studentdiscounts  = '.$id . ' LIMIT 2';
        $result = Db::getInstance()->executeS($query);
        
        return $result; 
    }

    public function studentActiveList() {
        $students = StudentDiscountRepo::getValidatedAccount();

        $fields_list = array(
          'id_studentdiscounts'=> array(
              'title' => "ID",
              'align' => 'center',
              'class' => 'fixed-width-xs',
              'search' => false,
            ),
          'email' => array(
              'title' => 'Email',
              'orderby' => true,
              'class' => 'fixed-width-xxl',
              'search' => false,
            ),
            'active' => array(
                'title' => 'Active',
                'orderby' => true,
                'class' => 'fixed-width-xxl',
                'search' => false,
                'align' => 'center',
        			'active' => 'active',
        			'type' => 'bool',
              ),
              'images' => array(
                'title' => 'Images',
                'class' => 'fixed-width-xxl',
                'callback' => 'displayImages',
                'callback_object' => $this,
              ),
              'account_validity_period' => array(
                'title' => 'Account validity period',
                'class' => 'fixed-width-xs',
                'search' => false,
                'orderby' => true,
              ),
        );
  
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->identifier = 'id_studentdiscounts';
        $helper->table = 'studentdiscounts';
        $helper->actions = ['view', 'delete'];
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->title = $this->l('Students with verificated mail and valid domain');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        return $helper->generateList($students, $fields_list);    
    }

    public function displayImages($images) {
        $img = '';

        foreach ($images as $image) {
            $img .= '<img width="100px" src="/presta/modules/studentdiscounts/upload/studentcarts/' . $image['image'] . '" alt="student cart"/>';
        }
        return $img;
    }

    public function renderForm()
    {
        $groups = Group::getGroups(Context::getContext()->language->id);
        $options = [];
        $i = 0;
        foreach ($groups as $group) {
             $options[$i] = [
                'id_option' => $group['id_group'],
                'name' => $group['name'],
              ];
              $i++;
         }

            $fields_form = [
                'form' => [
                    'legend' => [
                        'title' => $this->l('Configuration'),
                        'icon' => 'icon-link',
                    ],
                    'input' => [
                        [
                            'type' => 'select',
                            'label' => $this->l('Group'),
                            'name' => 'group',
                            'size' => 1,
                            'desc'     => '<a href="'. $this->context->link->getAdminLink('AdminGroups', false) . '&token='. Tools::getAdminTokenLite('AdminGroups') .'">' . $this->l('Kliknij, aby edytować lub dodać nową grupę.').'</a>',
                            'required' => true,
                            'options' => [
                                'query' => $options,
                                'id' => 'id_option',
                                'name' => 'name',
                            ]
                        ],
                    ],
                    'submit' => [
                        'name' => 'submitGroupConfiguration',
                        'title' => $this->trans('Save', [], 'Admin.Actions'),
                    ],
                ],
            ];
       

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];
        $helper->fields_value = array(
            'group' => Configuration::get('STUDENT_GROUP'),
       );

        return $helper->generateForm([$fields_form]);
    }

    public function addDomain() {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Add Domain'),
                    'icon' => 'icon-link',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Domain name'),
                        'name' => 'domain',
                        'class' => 'fixed-width-xxl'
                    ],
                    [
                        'type' => 'file',
                        'name' => 'csv-file',
                        'multiple' => false,
                        'label' => $this->l('Choose .csv file'),
                        'lang' => true,
                        'id' => 'csv-file'
                    ],
                ],
                'submit' => [
                    'name' => 'submitAddDomain',
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
            ],
        ];
       

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', true) .
            '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function studentList() {
        $students = StudentDiscountRepo::getVerificatedAccount();

        $fields_list = array(
          'id_studentdiscounts'=> array(
              'title' => "ID",
              'align' => 'center',
              'class' => 'fixed-width-xs',
              'search' => false,
            ),
          'email' => array(
              'title' => 'Email',
              'orderby' => true,
              'class' => 'fixed-width-xxl',
              'search' => false,
            ),
            'validated' => array(
                'title' => 'Validated',
                'orderby' => true,
                'class' => 'fixed-width-xxl',
                'search' => false,
                'align' => 'center',
        			'active' => 'status',
        			'type' => 'bool',
              ),
        );
  
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->identifier = 'id_studentdiscounts';
        $helper->table = 'studentdiscounts';
        $helper->actions = ['delete'];
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->title = $this->l('Students with not valid domain');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        return $helper->generateList($students, $fields_list);
    }

    public function domainList() {
        $domains = StudentDomains::gets();

        $fields_list = array(
          'id_student_domain'=> array(
              'title' => "ID",
              'align' => 'center',
              'class' => 'fixed-width-xs',
              'search' => false,
            ),
          'domain' => array(
              'title' => 'Domain',
              'orderby' => true,
              'class' => 'fixed-width-xxl',
              'search' => false,
            ),
        );
  
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'id_student_domain';
        $helper->table = 'student_domain';
        $helper->actions = ['delete'];
        $helper->show_toolbar = false;
        $helper->toolbar_btn['new'] = [
            'href' => $this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name, 'module_name' => $this->name, 'addDomains' => '']),
            'desc' => $this->trans('Add New Criterion', [], 'Modules.Productcomments.Admin'),
        ];
        $helper->module = $this;
        $helper->className = "StudentDomainClass";
        $helper->title = $this->l('Domain list');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        return $helper->generateList($domains, $fields_list);
    }

    public function confirm()  {
        return '<a><button type="button" class="btn btn-primary">ZATWIERDŹ</button></a><a><button type="button" class="btn btn-success">ZATWIERDŹ I DODAJ DOMENĘ</button></a>';
    }

    public function confirmAndAddDomain()  {
        return '<a><button type="button" class="btn btn-success">ZATWIERDŹ I DODAJ DOMENĘ</button></a>';
    }
    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookActionStudentDiscount($params) {
        $email = $params['email'];
    	$token = Tools::getToken();
        $query = "INSERT INTO `"._DB_PREFIX_."studentdiscounts` (`email`, `validated`, `verificated`, `token`) VALUES (\"".$email."\", 0, 0, \"". $token."\")";
        Db::getInstance()->execute($query);
    	$link = Context::getContext()->link->getModuleLink('studentdiscounts', 'verification', array('email' => $email, 'token' => $token));
    	$this->sendMail($email, $link);
    }

	public function sendMail($email, $link) {
    	 Mail::Send(
            (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
            'account_activation', // email template file to be use
            'Weryfikacja e-maila', // email subject
            array(
                '{email}' => Configuration::get('PS_SHOP_EMAIL'), // sender email address
                '{message}' => 'Hello world', // email content
            	'{link}' => $link,
            ),
            $email, // receiver email address
            NULL, //receiver name
            NULL, //from email address
            NULL,  //from name
            NULL, //file attachment
            NULL, //mode smtp
            _PS_MODULE_DIR_ . 'studentdiscounts/mails' //custom template path
        );
    }
}
