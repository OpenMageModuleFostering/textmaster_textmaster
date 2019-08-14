<?php
/**
 * Copyright (c) 2014 Textmaster
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Addonline
 * @package     Textmaster_Textmaster
 * @copyright   Copyright (c) 2014 Textmaster
 * @author 	    Addonline (http://www.addonline.fr)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Textmaster_Textmaster
 *
 * @category    Addonline
 * @package     Textmaster_Textmaster
 * @copyright   Copyright (c) 2014 Textmaster
 * @author 	    Addonline (http://www.addonline.fr)
 */
class Textmaster_Textmaster_Model_Api extends Mage_Core_Model_Abstract
{

    protected $_api_instance;

    protected $_userinfo = false;

    protected $_categories = false;

    protected $_locales = false;

    protected $_languages = false;

    protected $_author = false;

    protected $_authors = false;

    protected $_prices = false;

    protected $_users_me = false;

    const TEXTMASTER_API_VERSION = 'v1';

    const TEXTMASTER_PROD_API_URI = 'http://api.textmaster.com';

    const TEXTMASTER_STAGING_API_URI = 'http://api.staging.textmaster.com';

    const TEXTMASTER_SANDBOX_API_URI = 'http://api.sandbox.textmaster.com';

    const TEXTMASTER_PROD_EU_URI = 'http://eu.textmaster.com';

    const TEXTMASTER_STAGING_EU_URI = 'http://eu.staging.textmaster.com';

    const TEXTMASTER_SANDBOX_EU_URI = 'http://eu.sandbox.textmaster.com';

    const TEXTMASTER_TRACKER_ID = '53e3587bcc39f7000200025c';

    const TEXTMASTER_DEFAULT_LOCALE = 'en-EU';

    const TEXTMASTER_API_TIMEOUT_IN_SECONDS = 30;

    public function __construct ()
    {
        $this->_name = 'name_' . rand(1, 1000000);
        $this->api_key = Mage::getStoreConfig('textmaster/textmaster/api_key');
        ;
        $this->api_secret = Mage::getStoreConfig(
                'textmaster/textmaster/api_secret');
    }

    public function getAPIConnection ()
    {
        if ($this->api_key == '' && $this->api_secret == '')
            return false;
        if ($this->api_key or $this->api_secret) {
            if (! $this->isConnected()) {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    private function _initConnection ($name, $public, $clients, 
            $version = self::TEXTMASTER_API_VERSION)
    {
        $date = gmdate('Y-m-d H:i:s');
        $signature = sha1($this->api_secret . $date);
        
        $header = array(
                'Content-Type: application/json',
                'Accept: application/json',              
                "HTTP_AGENT: tm-magento-app",
                "apikey: {$this->api_key}",
                'signature: ' . $signature,
                "date: $date"
        );
        $uri = $this->getApiUri() . ($version ? "/$version" : '') . '/' .
                 ($clients ? 'clients/' : '') . ($public ? 'public/' : '') .
                 $name;
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_URL, $uri);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
            curl_setopt($curl, CURLOPT_USERAGENT,'tm-magento-app');
            curl_setopt($curl, CURLOPT_TIMEOUT, 
                    self::TEXTMASTER_API_TIMEOUT_IN_SECONDS);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            return $curl;
        } else
            return false;
    }

    private function _curlexec ($name, $data, $method = 'post')
    {
        $public = false;
        $clients = true;
        $version = self::TEXTMASTER_API_VERSION;
        $command = 'curl -i ';
        $date = gmdate('Y-m-d H:i:s');
        $content = '{"project":{"ctype":"translation","options":{"language_level":"regular"},"language_from":"en","language_to":"fr"}}';
        $signature = sha1($this->api_secret . $date);
        $command .= '-H "Host: api.sandbox.textmaster.com" ';
        $command .= '-H "Content-Type: application/json" ';
        $command .= '-H "HTTP_AGENT: tm-magento-app" ';
        $command .= '-A "tm-magento-app" ';
        $command .= '-H "Accept: application/json" ';
        $command .= '-H "apikey: ' . $this->api_key . '" ';
        $command .= '-H "signature: ' . $signature . '" ';
        $command .= '-H "date: ' . $date . '" ';
        $command .= '-d \'' . $content . '\' ';
        $uri = $this->getApiUri() . ($version ? "/$version" : '') . '/' .
                 ($clients ? 'clients/' : '') . ($public ? 'public/' : '') .
                 $name;
        
        $command .= $uri;
        $r1 = exec($command, $m1, $m2);
        
    }

    private function _request ($name, $public = false, $clients = false, 
            $version = self::TEXTMASTER_API_VERSION)
    {
        if (isset($this->_data[$name]))
            return $this->_data[$name];
        $debutlog = microtime(true);
        $curl = $this->_initConnection($name, $public, $clients, $version);
        $content = curl_exec($curl);
        $finlog = microtime(true);
        Mage::log('Log API ' . $name . ' : ' . ($finlog - $debutlog), null, 
                'textmaster.log');
        $info = curl_getinfo($curl);
        if ($info['http_code'] == '500') {
            $result['error'] = 'WS Indisponible';
            return $result;
        }
        
        
        curl_close($curl);
        try {
            $this->_data[$name] = Mage::helper('core')->jsonDecode($content);
        } catch (Exception $e) {
            $result['error'] = 'WS Indisponible';
            return $result;
        }
        return $this->_data[$name];
    }

    private function _post ($name, $data, $method = 'post')
    {
        $debutlog = microtime(true);
        $curl = $this->_initConnection($name, false, true);
        
        if ($method == 'put')
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        elseif ($method == 'delete')
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        elseif ($method == 'get') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        } else
            curl_setopt($curl, CURLOPT_POST, 1);
        
        if ($data && $data != null)
            curl_setopt($curl, CURLOPT_POSTFIELDS, 
                    Mage::helper('core')->jsonEncode($data));
        else
            curl_setopt($curl, CURLOPT_POSTFIELDS, 
                    Mage::helper('core')->jsonEncode(''));
        $content = curl_exec($curl);
        
        $result = Mage::helper('core')->jsonDecode($content);
        $finlog = microtime(true);
        Mage::log(
                'Log API ' . $method . ' ' . $name . ' : ' .
                         ($finlog - $debutlog), null, 'textmaster.log');
        
        $info = curl_getinfo($curl);

       // Mage::log(Mage::helper('core')->jsonEncode($data),null,'textmaster.log');
       //Mage::log($info,null,'textmaster.log');
        
        
        if ($info['http_code'] == '500') {
            $result['error'] = 'WS Indisponible';
            $result['type'] = 'error';
            return $result;
        }
        
        if (curl_errno($curl) || $info['http_code'] >= 300) {
            if (isset($result['message'])) {
                $result['error'] = (is_array($result['message'])) ? implode(' ', 
                        $result['message']) : $result['message'];
                $result['type'] = 'message';
            } elseif (isset($result['error'])) {
                $result['error'] = (is_array($result['error'])) ? implode(' ', 
                        $result['error']) : $result['error'];
                $result['type'] = 'error';
            } elseif (isset($result['base'])) {
                $result['error'] = (is_array($result['base'])) ? implode(' ', 
                        $result['base']) : $result['base'];
                $result['type'] = 'base';
            } else {
                $error_msg = '';
                if (is_array($result)) {
                    if (isset($result['errors']) &&
                             isset($result['errors']['base'])) {
                        $result['error'] = reset($result['errors']['base']);
                        $result['type'] = 'base';
                    } elseif (isset($result['errors'])) {
                        $count = count($result);
                        foreach ($result['errors'] as $fieldname => $message)
                            $error_msg .= $fieldname . ' : ' . reset($message) .
                                     ((-- $count) ? ', ' : '');
                        $result['error'] = $error_msg;
                        $result['type'] = 'error';
                    }
                }
            }
        }
        
        curl_close($curl);
        if (isset($result['error'])) {
            Mage::log($result['error'], null, 'textmaster.log');
        }
        return $result;
    }

    public function getApiUri ()
    {
        $sandbox = Mage::getConfig()->getNode('adminhtml/api/sandbox')->asArray();
        $staging = Mage::getConfig()->getNode('adminhtml/api/staging')->asArray();
        if ($sandbox) {
            return self::TEXTMASTER_SANDBOX_API_URI/*.'/'.self::TEXTMASTER_API_VERSION.''*/;
        } elseif ($staging) {
            return self::TEXTMASTER_STAGING_API_URI/*.'/'.self::TEXTMASTER_API_VERSION.''*/;
        } else {
            return self::TEXTMASTER_PROD_API_URI/*.'/'.self::TEXTMASTER_API_VERSION.''*/;
        }
    }

    public function getInterfaceUri ()
    {
        $sandbox = Mage::getConfig()->getNode('adminhtml/api/sandbox')->asArray();
        $staging = Mage::getConfig()->getNode('adminhtml/api/staging')->asArray();
        if ($sandbox) {
            return 'http://eu.app.sandbox.textmaster.com/';
        } elseif ($staging) {
            return 'http://eu.app.staging.textmaster.com/';
        } else {
            return 'http://eu.app.textmaster.com/';
        }
    }

    public function getEuUri ()
    {
        $sandbox = Mage::getConfig()->getNode('adminhtml/api/sandbox')->asArray();
        $staging = Mage::getConfig()->getNode('adminhtml/api/staging')->asArray();
        if ($sandbox) {
            return self::TEXTMASTER_SANDBOX_EU_URI;
        } elseif ($staging) {
            return self::TEXTMASTER_STAGING_EU_URI;
        } else {
            return self::TEXTMASTER_PROD_EU_URI;
        }
    }

    public function getAuth2Token ($email, $password)
    {
        if (! function_exists('curl_init')) {
            throw new Exception('CURL non activé');
            return false;
        }
        $uri = $this->getEuUri() . '/oauth/token';
        $header = "grant_type=password" . "&user[email]={$email}" .
                 "&user[password]={$password}" . "&client_id=" .
                 $this->getClientId() . "&client_secret=" .
                 $this->getClientSecret();

        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $uri);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10); // timeout in seconds
        curl_setopt($curl, CURLOPT_POSTFIELDS, $header);
        $response = curl_exec($curl);
        
        try {
            Mage::log($response,null,'textmaster.log');
            return Mage::helper('core')->jsonDecode($response);
        } catch (Exception $e) {
            Mage::log('Exception : ' . $uri,null,'textmaster.log');
            Mage::log($header,null,'textmaster.log');
            Mage::log($response,null,'textmaster.log');
            return false;
        }
    }

    function getAuth2TokenForCreation ()
    {
        $uri = $this->getEuUri() . '/oauth/token';
        $header = "grant_type=client_credentials" . "&client_id=" .
                 $this->getClientId() . "&client_secret=" .
                 $this->getClientSecret();
        
        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $uri);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10); // timeout in seconds
        curl_setopt($curl, CURLOPT_POSTFIELDS, $header);
        $response = curl_exec($curl);
        try {
            return Mage::helper('core')->jsonDecode($response);
        } catch (Exception $e) {
            Mage::log('Exception : ' . $uri,null,'textmaster.log');
            Mage::log($header,null,'textmaster.log');
            Mage::log($response,null,'textmaster.log');
            return false;
        }
    }

//     Clé publique: 8de45500fd370ed35c0269749ebb872149f8929d0d78cc5b5016bcd584c9058b
//     Clé privée: 75324f2849b25c7112d374a1a51a8b442220726639d64583196a7c2385227007

    public function getClientId ()
    {
        $sandbox = Mage::getConfig()->getNode('adminhtml/api/sandbox')->asArray();
        $staging = Mage::getConfig()->getNode('adminhtml/api/staging')->asArray();
        if ($sandbox) {
            return '8de45500fd370ed35c0269749ebb872149f8929d0d78cc5b5016bcd584c9058b';
            // return '97ff3df474ff8776e346e38e322ab2300e96429a4efc88c305078a6213902f21';
        } elseif ($staging) {
            return '8de45500fd370ed35c0269749ebb872149f8929d0d78cc5b5016bcd584c9058b';
            // return '97ff3df474ff8776e346e38e322ab2300e96429a4efc88c305078a6213902f21';
        } else {
            return '8de45500fd370ed35c0269749ebb872149f8929d0d78cc5b5016bcd584c9058b';
        }
    }

    public function getClientSecret ()
    {
        $sandbox = Mage::getConfig()->getNode('adminhtml/api/sandbox')->asArray();
        $staging = Mage::getConfig()->getNode('adminhtml/api/staging')->asArray();
        if ($sandbox) {
            return '75324f2849b25c7112d374a1a51a8b442220726639d64583196a7c2385227007';
            // return 'f089333f59275789afc763e60d436fdd740a03f07fe168bf5569ebed0380b6a6';
        } elseif ($staging) {
            return '75324f2849b25c7112d374a1a51a8b442220726639d64583196a7c2385227007';
            // return 'f089333f59275789afc763e60d436fdd740a03f07fe168bf5569ebed0380b6a6';
        } else {
            return '75324f2849b25c7112d374a1a51a8b442220726639d64583196a7c2385227007';
        }
    }

    public function getAPIKeys ($oAuthToken)
    {
        // Mage::log('getAPIKeys', null, 'textmaster.log');
        $uri = $this->getApiUri() . '/admin/users/me';
        $header = array(
                "Authorization: Bearer {$oAuthToken}"
        );
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $uri);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10); // timeout in seconds
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $response = curl_exec($curl);
        try {
            return Mage::helper('core')->jsonDecode($response);
        } catch (Exception $e) {
            Mage::log('Exception',null,'textmaster.log');
            Mage::log($oAuthToken,null,'textmaster.log');
            Mage::log($response,null,'textmaster.log');
            return false;
        }
    }

    function createUser ($token, $email, $password, $phone = null)
    {
        $uri = $this->getApiUri() . '/admin/users';
        
        $header = array(
                "Content-Type: application/json",
                "Authorization: Bearer {$token}",
                "Accept: application/json",
                "AGENT: tm-magento-app/agent v1.0"
        );
        
        $aData = $phone ? array(
                'user' => array(
                        'locale' => $this->getFullLocale(true),
                        'email' => $email,
                        'password' => $password,
                        'referer_tracker_id' => self::TEXTMASTER_TRACKER_ID,
                        'group' => 'clients',
                        'contact_information_attributes' => array(
                                'phone_number' => $phone
                        )
                )
        ) : array(
                'user' => array(
                        'locale' => $this->getFullLocale(true),
                        'email' => $email,
                        'referer_tracker_id' => self::TEXTMASTER_TRACKER_ID,
                        'password' => $password,
                        'group' => 'clients'
                )
        );
        try {
            $jData = Mage::helper('core')->jsonEncode($aData);
        } catch (Exception $e) {
            return false;
        }
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $uri);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10); // timeout in seconds
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jData);
        
        try {
            return Mage::helper('core')->jsonDecode($response);
        } catch (Exception $e) {
            Mage::log('Exception : ' . $uri,null,'textmaster.log');
            Mage::log($header,null,'textmaster.log');
            Mage::log($response,null,'textmaster.log');
            return false;
        }
    }

    public function getFullLocale ($registration = false)
    {
        if (! $registration) {
            $user_info = $this->getUserInfo();
            return $user_info['locale'];
        }
        
        $locales = $this->getLocales();
        $admin_locale_code = Mage::getStoreConfig('general/locale/code');
        foreach ($locales as $locale) {
            if ($admin_locale_code == str_replace('-', '_', $locale['code'])) {
                return $locale['code'];
            }
        }
        
        return self::TEXTMASTER_DEFAULT_LOCALE;
    }

    public function getUserInfo ()
    {
        // Mage::getSingleton('adminhtml/session')->unsTextmasterUserInfos();
        if ($this->_userinfo)
            return $this->_userinfo;
        $this->_userinfo = Mage::getSingleton('adminhtml/session')->getTextmasterUserInfos();
        
        //Mage::log($this->_userinfo, null, 'textmaster.log');
        
        if ($this->_userinfo && is_array($this->_userinfo) &&
                 ! isset($this->_userinfo['error']))
            return $this->_userinfo;
        else
            Mage::getSingleton('adminhtml/session')->unsTextmasterUserInfos();
        
        $this->_userinfo = $this->_request('users/me', false, true);
        Mage::getSingleton('adminhtml/session')->setTextmasterUserInfos(
                $this->_userinfo);
        return $this->_userinfo;
    }

    public function isUserConnected ()
    {
        if ($this->api_key == '' || $this->api_secret == '')
            return false;
        
        $user = $this->getUserInfo();
        $tmp = ( ! isset($user['errors']) && ! empty($user) &&
                 isset($user['email']));
        
        return $tmp;
    }

    public function isConnected ()
    {
        if (! isset($this->_connection))
            $this->_connection = $this->testConnection();
        return $this->_connection;
    }

    public function testConnection ()
    {
        $result = $this->getUserInfo();
        return ! empty($result) && ! isset($result['errors']);
    }

    public function getCategories ()
    {
        if ($this->_categories)
            return $this->_categories;
        
        $this->_categories = Mage::getSingleton('adminhtml/session')->getTextmasterCategories();
        
        if ($this->_categories && ! isset($this->_categories['error']))
            return $this->_categories;
        
        $locale = Mage::app()->getLocale()->getLocaleCode();
        
        $data = $this->_request(
                'categories?locale=' . str_replace('_', '-', $locale), true);
        $this->_categories = $data['categories'];
        if (! isset($this->_categories['error']))
            Mage::getSingleton('adminhtml/session')->setTextmasterCategories(
                    $this->_categories);
        return $this->_categories;
    }

    public function getCategory ($value)
    {
        if (! $this->_categories) {
            $this->getCategories();
        }
        // Mage::log($this->_categories,null,'textmaster.log');
        foreach ($this->_categories as $cat) {
            if ($cat['code'] == $value)
                return $cat['value'];
        }
        
        // if(isset($this->_categories[$value])) return
        // $this->_categories[$value];
        return $value;
    }

    public function getLocales ()
    {
        if ($this->_locales)
            return $this->_locales;
        
        $this->_locales = Mage::getSingleton('adminhtml/session')->getTextmasterLocales();
        if ($this->_locales && ! isset($this->_locales['error']))
            return $this->_locales;
        
        $this->_locales = $this->_request("locales", true);
        if (! isset($this->_locales['error']))
            Mage::getSingleton('adminhtml/session')->setTextmasterLocales(
                    $this->_locales);
        return $this->_locales;
    }

    public function getLanguages ()
    {
        if ($this->_languages)
            return $this->_languages;
        
        $this->_languages = Mage::getSingleton('adminhtml/session')->getTextmasterLanguages();
        if ($this->_languages && ! isset($this->_languages['error']))
            return $this->_languages;
        
        $locale = Mage::app()->getLocale()->getLocaleCode();
        $data = $this->_request(
                'languages?locale=' . str_replace('_', '-', $locale), true);
        $textmaster_languages = $data['languages'];
        
        $this->_languages = $textmaster_languages;
        if (! isset($this->_languages['error']))
            Mage::getSingleton('adminhtml/session')->setTextmasterLanguages(
                    $this->_languages);
        return $this->_languages;
    }

    public function getLanguage ($value)
    {
        if (! $this->_languages) {
            $this->getLanguages();
        }
        if (isset($this->_languages[$value]))
            return $this->_languages[$value];
        return $value;
    }

    public function getAuthor ($id_author)
    {
        if (isset($this->_author[$id_author]))
            return $this->_author[$id_author];
        $this->_author[$id_author] = $this->_post("my_authors/" . $id_author, 
                null, 'get');
        return $this->_author[$id_author];
    }

    public function getAuthors ()
    {
        if ($this->_authors)
            return $this->_authors;
        $this->_authors = Mage::getSingleton('adminhtml/session')->getTextmasterMyAuthors();
        if ($this->_authors && ! isset($this->_authors['error']))
            return $this->_authors;
        $this->_authors = $this->_request("my_authors", false, true);
        Mage::log($this->_authors,null,'textmaster.log');
        if (! isset($this->_authors['error']))
            Mage::getSingleton('adminhtml/session')->setTextmasterMyAuthors(
                    $this->_authors);
        return $this->_authors;
    }

    public function getAuthorsFilter ($data)
    {
        $data = array(
                'project' => $data
        );
        return $this->_post("authors", $data, 'get');
        // return $this->_authors;
    }

    public function getMyAuthorsByProject ($id_project_api)
    {
        if(isset($this->_myauthors)) return $this->_myauthors;
        $name = "projects/" . $id_project_api . "/my_authors";
        $this->_myauthors = $this->_request($name, false, true);
        return $this->_myauthors;
    }

    public function getServiceLevels ()
    {
        if (isset($this->_service_levels))
            return $this->_service_levels; // return
                                               // vocabulary
                                               // levels
                                               // from
                                               // cache
                                               // if
                                               // exists
            
        /* put service levels into array */
        $this->_service_levels = array(
                'regular' => Mage::helper('textmaster')->__('Regular'),
                'premium' => Mage::helper('textmaster')->__('Premium')
        );
        return $this->_service_levels;
    }

    public function getServiceLevel ($value)
    {
        if (! isset($this->_service_levels)) {
            $this->getServiceLevels();
        }
        if (isset($this->_service_levels[$value]))
            return $this->_service_levels[$value];
        return $value;
    }

    /**
     * Puts available vocabulary levels into array
     *
     * @return Array vocabulary levels
     */
    public function getVocabularyLevels ()
    {
        if (isset($this->_vocabulary_levels))
            return $this->_vocabulary_levels; // return
                                                  // vocabulary
                                                  // levels
                                                  // from
                                                  // cache
                                                  // if
                                                  // exists
            
        /* put vocabulary levels into cache */
        $this->_vocabulary_levels = array(
                'not_specified' => Mage::helper('textmaster')->__(
                        'Not specified'),
                'popular' => Mage::helper('textmaster')->__('Popular'),
                'technical' => Mage::helper('textmaster')->__('Technique'),
                'fictional' => Mage::helper('textmaster')->__('Fictional')
        );
        return $this->_vocabulary_levels;
    }

    public function getVocabularyLevel ($value)
    {
        if (! isset($this->_vocabulary_levels)) {
            $this->getVocabularyLevels();
        }
        if (isset($this->_vocabulary_levels[$value]))
            return $this->_vocabulary_levels[$value];
        return $value;
    }

    /**
     * Puts available grammatical persons into array
     *
     * @return Array grammatical persons
     */
    public function getGrammaticalPersons ()
    {
        if (isset($this->_grammatical_persons))
            return $this->_grammatical_persons; // return
                                                    // grammatical
                                                    // persons
                                                    // from
                                                    // cache
                                                    // if
                                                    // exists
            
        /* put grammatical persons into cache */
        $this->_grammatical_persons = array(
                'not_specified' => Mage::helper('textmaster')->__(
                        'Not specified'),
                'first_person_singular' => Mage::helper('textmaster')->__('I'),
                'second_person_singular' => Mage::helper('textmaster')->__(
                        'You'),
                'third_person_singular_masculine' => Mage::helper('textmaster')->__(
                        'He'),
                'third_person_singular_feminine' => Mage::helper('textmaster')->__(
                        'She'),
                'third_person_singular_neuter' => Mage::helper('textmaster')->__(
                        'One'),
                'first_person_plural' => Mage::helper('textmaster')->__('We'),
                'second_person_plural' => Mage::helper('textmaster')->__('You'),
                'third_person_plural' => Mage::helper('textmaster')->__('They')
        );
        
        return $this->_grammatical_persons;
    }

    public function getGrammaticalPerson ($value)
    {
        if (! isset($this->_grammatical_persons)) {
            $this->getGrammaticalPersons();
        }
        if (isset($this->_grammatical_persons[$value]))
            return $this->_grammatical_persons[$value];
        return $value;
    }

    public function getAudiences ()
    {
        if (isset($this->_audiences))
            return $this->_audiences; // return
                                          // audiences
                                          // from
                                          // cache if
                                          // exists
            
        /* put audieces into cache */
        $this->_audiences = array(
                'not_specified' => Mage::helper('textmaster')->__(
                        'Not specified'),
                'children' => Mage::helper('textmaster')->__(
                        'Children under 14 years old'),
                'teenager' => Mage::helper('textmaster')->__(
                        'Teenagers > between 14 and 18 years old'),
                'young_adults' => Mage::helper('textmaster')->__(
                        'Young adults > between 19 and 29 years old'),
                'adults' => Mage::helper('textmaster')->__(
                        'Adults > between 30 and 59 years old'),
                'old_adults' => Mage::helper('textmaster')->__(
                        'Seniors > 60 years old and beyond')
        );
        return $this->_audiences;
    }

    public function getAudience ($audience)
    {
        if (! isset($this->_audiences)) {
            $this->getAudiences();
        }
        if (isset($this->_audiences[$audience]))
            return $this->_audiences[$audience];
        return $audience;
    }

    public function getPricings ($word_count = 1)
    {
        if ($this->_prices)
            return $this->_prices;
        
        if($word_count==1) {
            $this->_prices = Mage::getSingleton('adminhtml/session')->getTextmasterPricings();
            if ($this->_prices && ! isset($this->_prices['error']))
                return $this->_prices;
        }
                        
        $prices = $this->_request("reference_pricings?word_count=$word_count", 
                true);
        
        $user = $this->getUserInfo();
        
        foreach ($prices['reference_pricings'] as $pricings) {
            if ($pricings['locale'] == $user['locale']) {
                foreach ($pricings['types'] as $type => $params) {
                    foreach ($params as $key => $param) {
                        $pricings['types'][$type][$param['name']] = $param['value'];
                        unset($pricings['types'][$type][$key]);
                    }
                }
                
                $this->_prices = $pricings;
                if($word_count==1 ) {
                    Mage::getSingleton('adminhtml/session')->setTextmasterPricings($this->_prices);
                }
                return $this->_prices;
            }
        }
        $this->_prices = array();
        return $this->_prices;
    }

    public function getProject ($id_project_api)
    {
       
        return $this->_request("projects/$id_project_api", false, true);
    }

    public function getProjects ($type = false, $filters = array(), $limit = array(0,20), $order = array('created_at','desc'))
    {
        $filter_request = 'where='.json_encode($filters['where']);
        $md5 = md5($type . '#' . $limit[1] . '#' . $limit[0] . '#' . $filter_request.'#'.$order[0].'#'.$order[1]);
        if (isset($this->{'projects_' . $md5}) && $this->{'projects_' . $type})
            return $this->{'projects_' . $md5}; // return projects from cache if
                                                    // exists
                                                    
        $request = 'projects/filter?order='.($order[1]=='desc'?'-':'').$order[0].'&per_page=' . $limit[1] . '&page=' . $limit[0];
        
        if (count($filters['where']))
            $request .= '&' . $filter_request;

        $this->{'projects_' . $md5} = $this->_request($request, false, true);
        return $this->{'projects_' . $md5};
    }

    public function addProject ($parameters, $quotation = false)
    {
        foreach ($parameters as $field => $value) {
            if ($value == '')
                unset($parameters[$field]);
        }
        
        $default_project_data = array(
                'same_author_must_do_entire_project' => 'true',
                'language_level' => 'regular',
                'quality' => 'false',
                #'expertise' => 'false',
                'vocabulary_type' => Mage::getStoreConfig(
                        'textmaster/defaultvalue/type_vocabulary'),
                'grammatical_person' => Mage::getStoreConfig(
                        'textmaster/defaultvalue/grammatical_person'),
                'target_reader_groups' => Mage::getStoreConfig(
                        'textmaster/defaultvalue/target_audience')
        );
        
        $parameters = array_merge($default_project_data, $parameters); // values,
                                                                       // sent
                                                                       // to
                                                                       // function
                                                                       // overides
                                                                       // the
                                                                       // default
                                                                       // values
        $options = array(
                'language_level' => $parameters['language_level'],
                'quality' => $parameters['quality'],
                // 'expertise' => $parameters['expertise'],
                'specific_attachment' => isset(
                        $parameters['specific_attachment']) ? $parameters['specific_attachment'] : 'false',
                'priority' => $parameters['priority']
        );
        
        unset($parameters['language_level'], $parameters['quality'], 
                $parameters['expertise']);
        
        $parameters['options'] = $options;
        
        if (isset($parameters['textmasters'])) {
            if (is_array($parameters['textmasters']) &&
                     count($parameters['textmasters']) == 0)
                unset($parameters['textmasters']);
            if (! is_array($parameters['textmasters']))
                unset($parameters['textmasters']);
        }
        
        $data = array(
                'project' => $parameters,
                'tracker' => self::TEXTMASTER_TRACKER_ID
        );
        
        if ($quotation)
            return $this->_post('projects/quotation', $data, 'get');
        return $this->_post('projects', $data);
    }

    public function updateProject ($id_project_api, $parameters)
    {
        foreach ($parameters as $field => $value) {
            if ($value == '')
                unset($parameters[$field]);
        }
        $options = array(
                'language_level' => $parameters['language_level'],
                'quality' => isset($parameters['quality']) ? $parameters['quality'] : '0',
                // 'expertise' => isset($parameters['expertise']) ? $parameters['expertise'] : '0',
                'specific_attachment' => isset(
                        $parameters['specific_attachment']) ? $parameters['specific_attachment'] : '0',
                'priority' => isset($parameters['priority']) ? $parameters['priority'] : '0'
        );
        unset($parameters['language_level'], $parameters['quality'], 
                $parameters['expertise']);
        $parameters['options'] = $options;
        if (isset($parameters['textmasters']) && ((is_array(
                $parameters['textmasters']) &&
                 count($parameters['textmasters']) == 0) ||
                 ! is_array($parameters['textmasters']))) {
            unset($parameters['textmasters']);
        }
        $parameters['callback'] = array(
                'project_in_progress' => array(
                        'url' => Mage::helper('textmaster')->getCallbackUrlInProgress(),
                        "format" => "json"
                )
        );
        
        $data = array(
                'project' => $parameters,
                'tracker' => self::TEXTMASTER_TRACKER_ID
        );
        
        $result = $this->_post("projects/{$id_project_api}", $data, 'put');
        return $result;
    }
    public function updateProjectTextmasters ($id_project_api, $textmasters)
    {
        $data = array(
            'project' => array('textmasters'=>$textmasters)
        );
        $result = $this->_post("projects/{$id_project_api}", $data, 'put');
        return $result;
    }

    public function addDocument ($id_project_api, $data)
    {
        $data['perform_word_count'] = $this->getUrl('');
        $data = array(
                'document' => $textmasters
        );
        return $this->_post("projects/$id_project_api/documents", $data);
    }

    public function addDocuments ($id_project_api, $data)
    {
        foreach ($data as &$item) {
            $item['word_count'] = 0;
            unset($item['word_count_rule']);
            $item['perform_word_count'] = true;
            $item['callback'] = array(
                    'word_count_finished' => array(
                            'url' => Mage::helper('textmaster')->getCallbackUrl(),
                            "format" => "json"
                    ),
                    'complete' => array(
                            'url' => Mage::helper('textmaster')->getCallbackCompletedUrl(),
                            "format" => "json"
                    )
                    
            );
        }
        
        $data = array(
                'documents' => $data
        );
        $reponse = $this->_post("projects/$id_project_api/batch/documents", 
                $data);
        return $reponse;
    }

    public function getDocuments ($id_project_api, $filters,$limit = array(0,20),$order=array('reference','desc'))
    {
        $filter_request = 'where='.json_encode($filters['where']);
        $request = 'projects/'.$id_project_api.'/documents/filter?order='.($order[1]=='desc'?'-':'').$order[0].'&per_page=' . $limit[1] . '&page=' . $limit[0];
        if (count($filters['where']))
            $request .= '&' . $filter_request;
        return $this->_request($request, false, true);
    }

    public function getDocument ($id_project_api, $id_document_api)
    {
        return $this->_request(
                "projects/$id_project_api/documents/$id_document_api", false, 
                true);
    }

    public function launchProject ($id_project_api, $asynchro = true)
    {
        if ($asynchro) {
            $result = $this->_post("projects/$id_project_api/async_launch", 
                    null);
            return $result;
        }
        return $this->_post("projects/$id_project_api/launch", null, 'put');
    }

    public function pauseProject ($id_project_api)
    {
        return $this->_post("projects/$id_project_api/pause", null, 'put');
    }

    public function resumeProject ($id_project_api)
    {
        return $this->_post("projects/$id_project_api/resume", null, 'put');
    }

    public function cancelProject ($id_project_api)
    {
        return $this->_post("projects/$id_project_api/cancel", null, 'put');
    }

    public function completeProject ($id_project_api)
    {
        return $this->_post("projects/$id_project_api/complete", null, 'put');
    }

    public function duplicateProject ($id_project_api)
    {
        return $this->_post("projects/$id_project_api/duplicate", null, 'post');
    }

    public function updateDocument ($id_project_api, $id_document_api, 
            $parameters)
    {
        $data = array(
                'document' => $parameters
        );
        return $this->_post(
                "projects/$id_project_api/documents/{$id_document_api}", $data, 
                'put');
    }

    public function deleteDocument ($id_project_api, $id_document_api)
    {
        return $this->_post(
                "projects/$id_project_api/documents/$id_document_api", null, 
                'delete');
    }

    public function approveDocument ($id_project_api, $id_document_api)
    {
        return $this->_post(
                "projects/$id_project_api/documents/$id_document_api/complete", 
                null, 'put');
    }

    public function commentDocument ($id_project_api, $id_document_api, $message)
    {
        return $this->_post(
                "projects/$id_project_api/documents/$id_document_api/support_messages", 
                array(
                        'support_message' => array(
                                'message' => $message
                        )
                ), 'post');
    }

    public function completeDocument ($id_project_api, $id_document_api,$callback = false,$message = false,$satisfaction = false)
    {
        $document_ids = array($id_document_api);        
        return $this->completeDocuments($id_project_api,$document_ids,$callback,$message,$satisfaction);
    }
    public function completeDocuments ($id_project_api, $document_ids,$callback = false,$message = false,$satisfaction = false)
    {
        $data = array('documents'=>$document_ids);
        if(!empty($message)) {
            $data['message'] = $message;
        }
        if(!empty($satisfaction)) {
            $data['satisfaction'] = $satisfaction;
        }
        
        return $this->_post("projects/$id_project_api/batch/documents/complete", $data, 'post');
    }

    public function getSupportMessages ($id_project_api, $id_document_api)
    {
        $name = "projects/" . $id_project_api .
                 "/documents/$id_document_api/support_messages";
        return $this->_request($name, false, true);
    }
}
