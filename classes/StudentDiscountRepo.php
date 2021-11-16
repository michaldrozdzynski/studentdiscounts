<?php
 
class StudentDiscountRepo{
    public static function getVerificatedAccount() {
        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'studentdiscounts` WHERE verificated = 1 AND validated = 0';
        $result = Db::getInstance()->executeS($query);
        return $result;
    }
    public static function getValidatedAccount() {
        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'studentdiscounts` WHERE verificated = 1 AND validated = 1';
        $students = Db::getInstance()->executeS($query);

        for($i = 0; $i<count($students); $i++) {
            $id = $students[$i]['id_studentdiscounts'];
            $query = 'SELECT image FROM `' . _DB_PREFIX_ . 'studentdiscounts_image` WHERE id_studentdiscounts = ' . $id . ' LIMIT 2';
            $images = Db::getInstance()->executeS($query);
            $students[$i]['images'] = $images;
        }

        return $students;
    }
    
    public static function delete($id) {;
        StudentDiscountRepo::deleteImageById($id);
        $query =  'DELETE FROM `' . _DB_PREFIX_ . 'studentdiscounts` WHERE id_studentdiscounts = ' . $id; 
        Db::getInstance()->execute($query);
    }

    public static function confirm($id) {
        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'studentdiscounts` WHERE id_studentdiscounts = ' . $id;
        $students = Db::getInstance()->executeS($query);
        $email = $students[0]['email'];
        $domain = explode('@', $email)[1];
        
        StudentDiscountRepo::confirmByDomain($domain);
    }

    public static function confirmByDomain($domain) {
        $query = 'UPDATE `' . _DB_PREFIX_ . 'studentdiscounts` SET validated = 1 WHERE verificated = 1 AND email LIKE \'%' . $domain .'\'';
        Db::getInstance()->execute($query);
    
        $query = 'SELECT COUNT(domain) FROM `' . _DB_PREFIX_ . 'student_domain` WHERE domain = "'.$domain.'"';
        $db = Db::getInstance();
        $result = $db->executeS($query);
        $result = (int) $result[0]['COUNT(domain)'];
        if ($result == 0) {
            $db->insert("student_domain", [
                'domain' => $domain,
            ]);
        }
    }

    public static function existStudentWithCustomerId($customerId) {
        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'studentdiscounts` WHERE id_customer = ' . $customerId;
        $result = Db::getInstance()->executeS($query);
        
        return count($result) > 0;
    }

    public static function active($id) {
        $date = date('Y-m-d', strtotime('+1 year'));
        $query = 'UPDATE `' . _DB_PREFIX_ . 'studentdiscounts` SET active = 1, 	account_validity_period = \'' .$date. '\' WHERE verificated = 1 AND validated = 1 AND id_studentdiscounts = ' . $id;
        Db::getInstance()->execute($query);

        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'studentdiscounts` WHERE id_studentdiscounts = ' . $id;
        $result = Db::getInstance()->getRow($query);
        $email = $result['email'];
    
        $customerId = $result['id_customer'];
        if ($customerId) {
            $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'customer_group` WHERE id_customer = ' . $customerId . ' AND id_group = ' . Configuration::get('STUDENT_GROUP');
            $result = Db::getInstance()->getRow($query);
                
                if (!$result) {
                Db::getInstance()->insert("customer_group", [
                        'id_customer' => $customerId,
                    'id_group' => Configuration::get('STUDENT_GROUP'),
                ]);
            }
        }
    }

    public static function desactive($id) {
        $query = 'UPDATE `' . _DB_PREFIX_ . 'studentdiscounts` SET active = 0, 	account_validity_period = NULL WHERE id_studentdiscounts = ' . $id;
        Db::getInstance()->execute($query);

        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'studentdiscounts` WHERE id_studentdiscounts = ' . $id;
        $result = Db::getInstance()->getRow($query);
        $email = $result['email'];
    
        $customerId = $result['id_customer'];
        if ($customerId) {
            $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'customer_group` WHERE id_customer = ' . $customerId . ' AND id_group = ' . Configuration::get('STUDENT_GROUP');
            $result = Db::getInstance()->getRow($query);
                
                if ($result) {
                    $query = 'DELETE FROM ' . _DB_PREFIX_ . 'customer_group WHERE id_customer = ' . $customerId . ' AND id_group = ' . Configuration::get('STUDENT_GROUP');
                    Db::getInstance()->execute($query);
            }
        }
    }

    public static function isActive($id) {
        $query = 'SELECT active FROM `' . _DB_PREFIX_ . 'studentdiscounts` WHERE id_studentdiscounts = ' . $id;
        $result = Db::getInstance()->getRow($query);
        $active = $result['active'];

        return $active;
    }

    public static function deleteImageById($id) {
        $query = 'SELECT image FROM `' . _DB_PREFIX_ . 'studentdiscounts_image` WHERE 	id_studentdiscounts  = ' . $id;
        $images = Db::getInstance()->executeS($query);
        $query = 'DELETE FROM ' . _DB_PREFIX_ . 'studentdiscounts_image WHERE id_studentdiscounts = '.$id ;
        Db::getInstance()->execute($query);
        foreach ($images as $image) {
            $filename = $image['image'];
            $filename = _PS_MODULE_DIR_ .'studentdiscounts/upload/studentcarts/' . $filename;
            if (file_exists($filename)) {
                unlink($filename);
            }
        }
    }

    public static function checkIsAccountActive() {
        $date = date("Y-m-d");
        $query = 'SELECT id_studentdiscounts, email FROM ' . _DB_PREFIX_ . 'studentdiscounts WHERE active  = 1 AND account_validity_period < \''. $date.'\'';
        $results = Db::getInstance()->executeS($query);

        foreach ($results as $result) {
            $id = $result['id_studentdiscounts'];

            StudentDiscountRepo::desactive($id);
        }

        return $results;
    }

    public static function getEmailById($id) {
        $query = 'SELECT email FROM `' . _DB_PREFIX_ . 'studentdiscounts` WHERE id_studentdiscounts = ' . $id;
        $result = Db::getInstance()->getRow($query);
        $email = $result['email'];

        return $email;
    }
}