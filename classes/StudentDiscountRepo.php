<?php
 
class StudentDiscountRepo{
    public static function getVerificatedAccount() {
        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'studentdiscounts` WHERE verificated = 1 AND validated = 0';
        $result = Db::getInstance()->executeS($query);
        return $result;
        }
    
    public static function delete($id) {
        $query =  'DELETE FROM `' . _DB_PREFIX_ . 'studentdiscounts` WHERE id_studentdiscounts = ' . $id; 
        Db::getInstance()->execute($query);
    }

    public static function confirm($id) {
        $query = 'UPDATE `' . _DB_PREFIX_ . 'studentdiscounts` SET validated = 1 WHERE verificated = 1 AND id_studentdiscounts = ' . $id;
        Db::getInstance()->execute($query);
    
        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'studentdiscounts` WHERE id_studentdiscounts = ' . $id;
        $result = Db::getInstance()->getRow($query);
        $email = $result['email'];
    
        $customerId = StudentDiscountRepo::getCustomerId($email);
        if ($customerId) {
            $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'customer_group` WHERE id_customer = ' . $customerId . 'AND id_group = ' . Configuration::get('STUDENT_GROUP');
            $result = Db::getInstance()->getRow($query);
                
                if (!$result) {
                Db::getInstance()->insert("customer_group", [
                        'id_customer' => $customerId,
                    'id_group' => Configuration::get('STUDENT_GROUP'),
                ]);
            }
        }
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
    
        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'studentdiscounts` WHERE email LIKE \'%' . $domain .'\'';
        $results = Db::getInstance()->executeS($query);
    
        foreach ($results as $result) {
                $email = $result['email'];
            
                $customerId = StudentDiscountRepo::getCustomerId($email);
            if ($customerId) {
                $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'customer_group` WHERE id_customer = ' . $customerId . 'AND id_group = ' . Configuration::get('STUDENT_GROUP');
                $res = Db::getInstance()->getRow($query);
                if (!$res) {
                    Db::getInstance()->insert("customer_group", [
                            'id_customer' => $customerId,
                        'id_group' => Configuration::get('STUDENT_GROUP'),
                    ]);
                }
            }
        }
    }

    public static function getCustomerId($email) {
        $query = 'SELECT id_customer FROM `' . _DB_PREFIX_ . 'customer` WHERE email = \''.$email . '\'';
        $result = Db::getInstance()->getRow($query);
        $id = $result['id_customer'];
    
        return false;
    }
}