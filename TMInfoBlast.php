<?php
/*
 *  Note: Think back if XML will handled by user or by this class.
 *  Author: Wan Zulkarnain Bin Wan Hasbullah
 *  Website: www.wanzul-hosting.com
 */
class TMInfoBlast {

    private $URL = array(
        'login' => 'http://www.infoblast.com.my/openapi/login.php',
        'logout' => 'http://www.infoblast.com.my/openapi/logout.php',
        'getmsglist' => 'http://www.infoblast.com.my/openapi/getmsglist.php',
        'getmsgdetail' => 'http://www.infoblast.com.my/openapi/getmsgdetail.php',
        'deletemessage' => 'http://www.infoblast.com.my/openapi/delmsg.php',
        'sendmsg' => 'http://www.infoblast.com.my/openapi/sendmsg.php',
    );
    var $logindata, $obj, $action, $messageType;

    function __construct() {
        $this->logindata = array();
        $this->obj = new curlaction;
    }

    function setAction($action) {
        $this->action = $action;
    }

    function setUsername($username) {
        $this->logindata['username'] = $username;
        return $this;
    }

    function setPassword($password) {
        $this->logindata['password'] = sha1($password);
        return $this;
    }

    function prepare() {
        if ($this->action == 'login') {
            $this->url = $this->URL['login'];
        } elseif ($this->action == 'logout') {
            $this->url = $this->URL['logout'];
        } elseif ($this->action == 'getmsglist') {
            $this->url = $this->URL['getmsglist'];
        } elseif ($this->action == 'getmsgdetail') {
            $this->url = $this->URL['getmsgdetail'];
        } elseif ($this->action == 'deletemessage') {
            $this->url = $this->URL['deletemessage'];
        } elseif ($this->action == 'sendmsg') {
            $this->url = $this->URL['sendmsg'];
        } else {
            exit('Error! Action not set!');
        }

        if (isset($this->messageType)) {
            if ($this->messageType == 'text') {
                $this->obj->setSendMessageOption('application/x-www-form-urlencoded');
            } elseif ($this->messageType == 'voice') {
                $this->obj->setSendMessageOption('multipart/form-data');
            } else {
                exit('Invalid Message Type: ' . $this->messageType);
            }
        }
        return $this;
    }

    /*
     *  You need to save the session id
     *  $param string
     *  return session id
     */

    function login() {
        $return = $this->obj->setURL($this->url)->setData($this->logindata)->process()->getData();
        $xml = simplexml_load_string($return);
        if ($xml->attributes()->status == 'fail') {
            exit($xml->err['returncode'] . ' ' . $xml->err['desc']);
        } else {
            return $xml->sessionid;
        }
    }

    function logout($sessionid) {
        unset($this->logindata);
        $return = $this->obj->setURL($this->url)->setData(array('sessionid' => $sessionid))->process()->getData();
        return $return; //Need to check here
    }

    function setMessageType($messagetype) {
        $this->messageType = $messagetype;
        return $this;
    }

}

class curlaction {

    var $header, $data, $url, $result;

    function setURL($url) {
        $this->url = $url;
        return $this;
    }

    function setSendMessageOption($header = 'application/x-www-form-urlencoded') {
        $this->header = $header;
        return $this;
    }

    function setData($data) {
        $this->data = $data;
        return $this;
    }

    function process() {
        $process = curl_init();
        curl_setopt($process, CURLOPT_URL, $this->url);
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_TIMEOUT, 10);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
        if (isset($this->header)) {
            curl_setopt($process, CURLOPT_HTTPHEADER, array($this->header));
        }
        curl_setopt($process, CURLOPT_POSTFIELDS, http_build_query($this->data));
        $this->result = curl_exec($process);
        curl_close($process);
        return $this;
    }

    function getData() {
        return $this->result;
    }

}
