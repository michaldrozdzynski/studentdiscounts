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

require_once('classes/StudentDomains.php');
require_once('classes/StudentDiscountRepo.php');

class Studentdiscounts extends Module
{
    protected $config_form = false;
    public function __construct()
    {
        $this->name = 'studentdiscounts';
        $this->tab = 'others';
        $this->version = '1.1.2';git 
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
        $this->mySuperCron();
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');

        $request = 'SELECT `id_group` FROM `' . _DB_PREFIX_ . 'group_lang` WHERE name = "Student"';
        $group = Db::getInstance()->getRow($request);
        

        /** @var array $result */
        if (!$group) {
            $result = Db::getInstance()->getRow($request);
            $my_group = new Group();
            $my_group->name = array(Configuration::get('PS_LANG_DEFAULT') => 'Student');
            $my_group->price_display_method = 1;
            $my_group->add();
        }
        $request = 'SELECT `id_group` FROM `' . _DB_PREFIX_ . 'group_lang` WHERE name = "Student"';
        $group = Db::getInstance()->getRow($request);
        $groupId = $group['id_group'];

        Configuration::updateValue('STUDENT_GROUP', $groupId);
        Configuration::updateValue('MODULE_LINK', '');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('additionalCustomerFormFields') &&
            $this->registerHook('actionCustomerAccountAdd') &&
            $this->registerHook('displayCustomerAccount');
    }

       /**
     * @return array|FormField[]
     */
    public function hookAdditionalCustomerFormFields($params)
    {
        $label = $this->l('I\'m student') . '<br><em>'.$this->l('If you are a student, register your account using the student domain to get a student discount. You will receive an e-mail with which we will verify that you are its owner. Then add a photo of your ID card. If your university is in the system, the system, after verifying your card, will assign you a discount of X. If your university is not in the system, wait one working day for manual verification by an employee.').'</em>';
        
        $formField = new FormField();
        $formField->setName('isStudent');
        $formField->setType('checkbox');
        $formField->setLabel($label);
        //dump($formField);
        return [$formField];
    }
    public function hookDisplayCustomerAccount() {
        $customerId = $this->context->customer->id;
        if (StudentDiscountRepo::existStudentWithCustomerId($customerId)) {
            return $this->display(__FILE__, 'views/templates/hook/studentaccount.tpl');
        } else {;}
    }

        /**
     * @param array $params
     */
    public function hookActionCustomerAccountAdd(array $params)
    {
        if (empty($params['newCustomer']) || Tools::getValue('isStudent') == 0) {
            return;
        }
        $email = $params['newCustomer']->email;
        $customerId = $params['newCustomer']->id;
    	$token = Tools::getToken();
        $query = "INSERT INTO `"._DB_PREFIX_."studentdiscounts` (`email`, `id_customer`, `validated`, `verificated`, `token`) VALUES (\"".$email."\",".$customerId.", 0, 0, \"". $token."\")";
        Db::getInstance()->execute($query);
    	$link = Context::getContext()->link->getModuleLink('studentdiscounts', 'verification', array('email' => $email, 'token' => $token));
    	$message = $this->l('Thank you for creating your student account, please click the activation link to verify your email address.');
        $subject = $this->l('Email verification');
        $this->sendMail($email, $link, $message, $subject, 'account_activation');
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        Configuration::deleteByName('STUDENT_GROUP');

        return parent::uninstall();
    }

    public function getContent()
    {
        $link = Context::getContext()->link->getAdminLink('AdminModules', false) .'&configure=studentdiscounts';
        Configuration::updateValue('MODULE_LINK', $link);

        if (Tools::isSubmit('submitGroupConfiguration')) {
            $groupId= Tools::getValue('group');
            Configuration::updateValue('STUDENT_GROUP', $groupId);
        }
        
        if (Tools::isSubmit('addDomains')){
            if (Tools::isSubmit('downloadSample')){
                $filename = _PS_MODULE_DIR_ .'studentdiscounts/sample.csv';
                if (file_exists($filename)) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="'.basename($filename).'"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($filename));
                    readfile($filename);
                    exit;
                }
            }
            return $this->addDomain();
        }
        if (Tools::isSubmit('viewstudentdiscounts')){
            return $this->viewStudent();
        }

        if (Tools::getValue('activeStudentAccount') === '1') {
            StudentDiscountRepo::active(Tools::getValue('studentId'));
            $message = $this->l('Your student account has been activated. You can take advantage of student discounts.');
            $subject = $this->l('Activation of the student account');
            $link = _PS_BASE_URL_.__PS_BASE_URI__;
            $template = 'active_account';
            $email = StudentDiscountRepo::getEmailById(Tools::getValue('studentId'));
            $this->sendMail($email, $link, $message, $subject, 'active_account');
        } else if (Tools::getValue('activeStudentAccount') === '0') {
            StudentDiscountRepo::desactive(Tools::getValue('studentId'));
            $message = $this->l('Your account has not been activated. Send the photo of the student ID again, go to the account settings.');
            $subject = $this->l('Your account has not been activated.');
            $link = _PS_BASE_URL_.__PS_BASE_URI__;
            $template = 'active_account';
            $email = StudentDiscountRepo::getEmailById(Tools::getValue('studentId'));
            $this->sendMail($email, $link, $message, $subject, 'active_account');
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
                        unlink($target_file);
                    }
                }
            }
        }

        $studentId = Tools::getValue("id_studentdiscounts");
        $deleteStudentDiscounts = Tools::getValue("deletestudentdiscounts");
        $statusstudentdiscounts = Tools::getValue("statusstudentdiscounts");
        $activestudentdiscounts = Tools::getValue("activestudentdiscounts");
        if ($deleteStudentDiscounts !== false) {
            $message = $this->l('Your email address does not belong to the student domain that the discounts apply to. If you are a student please re-register using your student email address.');
            $subject = $this->l('Incorrect email address');
            $link = _PS_BASE_URL_.__PS_BASE_URI__;
            $template = 'active_account';
            $email = StudentDiscountRepo::getEmailById($studentId);
            $this->sendMail($email, $link, $message, $subject, 'active_account');
            StudentDiscountRepo::delete($studentId);
        } else if ($statusstudentdiscounts !== false) {
            StudentDiscountRepo::confirm($studentId);
        }

        $domainId = Tools::getValue("id_student_domain");
        $deleteStudentDomain = Tools::getValue("deletestudent_domain");
        if ($deleteStudentDomain !== false) {
            StudentDomains::delete($domainId);
        }
        
        return $this->renderForm() . $this->domainList() . $this->studentList() . $this->studentActiveList();
    }

    private function viewStudent() {
        $studentId = Tools::getValue("id_studentdiscounts");
        
        $studentCart = [];
        $images = $this->getImageById($studentId);
        foreach ($images as $image) {
            array_push($studentCart, $image['image']);
        }

        $this->context->smarty->assign('studentId', $studentId);
        $this->context->smarty->assign('studentCart', $studentCart);
        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/studentdiscounts.tpl');
        return $output;
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
              'orderby' => true,
            ),
          'email' => array(
              'title' => $this->l('Email'),
              'orderby' => true,
              'class' => 'fixed-width-xxl',
              'search' => false,
            ),
            'active' => array(
                'title' => $this->l('Active'),
                'orderby' => true,
                'class' => 'fixed-width-xxl',
                'search' => false,
                'align' => 'center',
        		'type' => 'bool',
              ),
              'images' => array(
                'title' => $this->l('Photos'),
                'class' => 'fixed-width-xxl',
                'callback' => 'displayImages',
                'callback_object' => $this,
                'search' => false,
              ),
              'account_validity_period' => array(
                'title' => $this->l('Account validity period'),
                'class' => 'fixed-width-xs',
                'search' => false,
                'orderby' => false,
              ),
        );
  
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'id_studentdiscounts';
        $helper->table = 'studentdiscounts';
        $helper->actions = ['view'];
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->listTotal = count($students);
        $helper->_default_pagination = 10;
        $helper->_pagination = array(5, 10, 50, 100);
        $helper->title = $this->l('Students with verificated mail and valid domain');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $page = ( $page = Tools::getValue( 'submitFilter' . $helper->table ) ) ? $page : 1;
        $pagination = ( $pagination = Tools::getValue( $helper->table . '_pagination' ) ) ? $pagination : 10;
        $content = $this->paginate_content( $students, $page, $pagination );

        return $helper->generateList($content, $fields_list);    
    }

    public function displayImages($images) {
        $img = '';

        foreach ($images as $image) {
            $img .= '<img width="100px" src="'._MODULE_DIR_ .'studentdiscounts/upload/studentcarts/' . $image['image'] . '" alt="student cart"/>';
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
                            'desc'     => '<a href="'. $this->context->link->getAdminLink('AdminGroups', false) . '&token='. Tools::getAdminTokenLite('AdminGroups') .'">' . $this->l('Click to edit or add a new group.').'</a>',
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
                        'id' => 'csv-file',
                        'desc'=> '<a href="'. $this->context->link->getAdminLink('AdminModules', false) . '&configure=studentdiscounts&module_name=studentdiscounts&addDomains=&downloadSample=&token='. Tools::getAdminTokenLite('AdminModules') .'">' . $this->l('Click to download a sample .csv file.').'</a>',
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
              'title' => $this->l('Email'),
              'orderby' => true,
              'class' => 'fixed-width-xxl',
              'search' => false,
            ),
            'validated' => array(
                'title' => $this->l('Validated'),
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
        $helper->simple_header = false;
        $helper->identifier = 'id_studentdiscounts';
        $helper->table = 'studentdiscounts';
        $helper->actions = ['delete'];
        $helper->show_toolbar = false;
        $helper->_default_pagination = 10;
        $helper->listTotal = count($students);
        $helper->_pagination = array(5, 10, 50, 100);
        $helper->module = $this;
        $helper->title = $this->l('Students with not valid domain');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $page = ( $page = Tools::getValue( 'submitFilter' . $helper->table ) ) ? $page : 1;
        $pagination = ( $pagination = Tools::getValue( $helper->table . '_pagination' ) ) ? $pagination : 10;
        $content = $this->paginate_content( $students, $page, $pagination );

        return $helper->generateList($content, $fields_list);
    }

    public function domainList() {
        $domains = StudentDomains::gets();

        $fields_list = array(
          'id_student_domain'=> array(
              'title' => "ID",
              'align' => 'center',
              'class' => 'fixed-width-xs',
              'search' => false,
              'orderby' => true,
            ),
          'domain' => array(
              'title' => $this->l('Domain'),
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
        $helper->listTotal = count($domains);
        $helper->_default_pagination = 10;
        $helper->_pagination = array(5, 10, 50, 100);
        $helper->toolbar_btn['new'] = [
            'href' => $this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name, 'module_name' => $this->name, 'addDomains' => '']),
            'desc' => $this->trans('Add New Criterion', [], 'Modules.Productcomments.Admin'),
        ];
        $helper->module = $this;
        $helper->title = $this->l('Domain list');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $page = ( $page = Tools::getValue( 'submitFilter' . $helper->table ) ) ? $page : 1;
        $pagination = ( $pagination = Tools::getValue( $helper->table . '_pagination' ) ) ? $pagination : 10;
        $content = $this->paginate_content( $domains, $page, $pagination );

        return $helper->generateList($content, $fields_list);
    }

        /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function paginate_content( $content, $page = 1, $pagination = 10 ) {

        if( count($content) > $pagination ) {
             $content = array_slice( $content, $pagination * ($page - 1), $pagination );
        }
     
        return $content;
     
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

	public function sendMail($email, $link, $message, $subject, $template) {
    	 Mail::Send(
            (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
            $template, // email template file to be use
            $subject, // email subject
            array(
                '{email}' => Configuration::get('PS_SHOP_EMAIL'), // sender email address
                '{message}' => $message,
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

    public function mySuperCron() {
        $next = strtotime('now + 24 hours');
        if ((int) Configuration::get('MYSUPERMODULETIMER') < (int) strtotime('now') ) {
            $students = StudentDiscountRepo::checkIsAccountActive();
            foreach ($students as $student) {
                $email = $student['email'];
                $message = $this->l('Your student account has expired. Send the photo of the student ID again, go to the account settings.');
                $subject = $this->l('Your student account has expired');
                $link = _PS_BASE_URL_.__PS_BASE_URI__;
                $template = 'active_account';
                $this->sendMail($email, $link, $message, $subject, 'active_account');
            }

            Configuration::updateValue('MYSUPERMODULETIMER', (int) $next);
        }
    }
}
