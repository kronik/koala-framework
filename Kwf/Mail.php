<?php
class Kwf_Mail extends Zend_Mail
{
    // die folgenden 5 sind für maillog
    protected $_ownFrom = '';
    protected $_ownTo = array();
    protected $_ownCc = array();
    protected $_ownBcc = array();
    protected $_attachments = array();
    protected $_attachImages = false;
    protected $_domain = null;

    public function __construct($mustNotBeSet = null)
    {
        if ($mustNotBeSet) {
            throw new Kwf_Exception("Kwf_Mail got replaced with Kwf_Mail_Template");
        }
        parent::__construct('utf-8');
    }

    public function getMailContent($type = Kwf_Model_Mail_Row::MAIL_CONTENT_AUTO)
    {
        if ($type == Kwf_Model_Mail_Row::MAIL_CONTENT_AUTO) {
            $ret = $this->getBodyHtml(true);
            if (!$ret) $ret = $this->getBodyText(true);
            return $ret;
        } else if ($type == Kwf_Model_Mail_Row::MAIL_CONTENT_HTML) {
            return $this->getBodyHtml(true);
        } else if ($type == Kwf_Model_Mail_Row::MAIL_CONTENT_TEXT) {
            return $this->getBodyText(true);
        }
        return null;
    }

    public function addCc($email, $name='')
    {
        $this->_ownCc[] = trim("$name <$email>");
        if (Kwf_Registry::get('config')->debug->sendAllMailsTo) {
            if ($name) {
                $this->addHeader('X-Real-Cc', $name ." <".$email.">");
            } else {
                $this->addHeader('X-Real-Cc', $email);
            }
        } else {
            parent::addCc($email, $name);
        }
        return $this;
    }

    public function addBcc($email)
    {
        $this->_ownBcc[] = $email;
        if (Kwf_Registry::get('config')->debug->sendAllMailsTo) {
            $this->addHeader('X-Real-Bcc', $email);
        } else {
            parent::addBcc($email);
        }
        return $this;
    }

    public function addTo($email, $name='')
    {
        $this->_ownTo[] = trim("$name <$email>");
        if (Kwf_Registry::get('config')->debug->sendAllMailsTo) {
            if ($name) {
                $this->addHeader('X-Real-Recipient', $name ." <".$email.">");
            } else {
                $this->addHeader('X-Real-Recipient', $email);
            }
        } else {
            parent::addTo($email, $name);
        }
        return $this;
    }

    public function setAttachImages($attachImages)
    {
        $this->_attachImages = $attachImages;
        return $this;
    }

    public function setDomain($domain)
    {
        $this->_domain = $domain;
        return $this;
    }

    public function getDomain()
    {
        if (!$this->_domain) {
            $this->_domain = Kwf_Config::getValue('server.domain');
        }
        return $this->_domain;
    }

    public function setBodyHtml($html, $charset = null, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE)
    {
        while (preg_match('/(img src|background)=\"\/(.*?)\"/i', $html, $matches)) {
            $path = '/' . $matches[2];
            if ($this->_attachImages) {
                if (substr($path, 0, 6) == '/media') {
                    $parts = explode('/', substr($path, 1));
                    $class = $parts[1];
                    $id = $parts[2];
                    $type = $parts[3];
                    $checksum = $parts[4];
                    $filename = $parts[6];
                    if ($checksum != Kwf_Media::getChecksum($class, $id, $type, $filename)) {
                        throw new Kwf_Exception_AccessDenied('Access to file not allowed.');
                    }
                    $output = Kwf_Media::getOutputWithoutCheckingIsValid($class, $id, $type);
                } else {
                    $loader = new Kwf_Assets_Loader();
                    $image = $loader->getDependencies()->getAssetPath(substr($path, 8));
                    $output = $loader->getFileContents($image);
                }
                if (isset($output['contents'])) {
                    $contents = $output['contents'];
                } else if (isset($output['file'])) {
                    $contents = file_get_contents($output['file']);
                } else {
                    throw new Kwf_Exception("didn't image contents");
                }
                $image = new Zend_Mime_Part($contents);
                $image->type = $output['mimeType'];
                $image->disposition = Zend_Mime::DISPOSITION_INLINE;
                $image->encoding = Zend_Mime::ENCODING_BASE64;
                $image->filename = substr(strrchr($path, '/'), 1);
                $image->id = md5($path);
                $this->setType(Zend_Mime::MULTIPART_RELATED);
                $this->addAttachment($image);
                $replace = "cid:{$image->id}";
            } else {
                $replace = "http://" . $this->getDomain() . $path;
            }
            $html = str_replace($matches[0], "{$matches[1]}=\"$replace\"", $html);
        }
        parent::setBodyHtml($html, $charset, $encoding);
    }

    public function addAttachment(Zend_Mime_Part $attachment)
    {
        $this->_attachments[] = $attachment;
        return parent::addAttachment($attachment);
    }

    public function setFrom($email, $name='')
    {
        if (empty($email)) {
            throw new Kwf_Exception("Email address '$email' cannot be set as from part in a mail. Empty or invalid address.");
        }
        $this->_ownFrom = trim("$name <$email>");
        parent::setFrom($email, $name);
        return $this;
    }

    public function send($transport = null)
    {
        $mailSendAll = Kwf_Registry::get('config')->debug->sendAllMailsTo;
        if ($mailSendAll) {
            parent::addTo($mailSendAll);
        }

        $mailSendAllBcc = Kwf_Registry::get('config')->debug->sendAllMailsBcc;
        if ($mailSendAllBcc) {
            parent::addBcc($mailSendAllBcc);
        }

        if ($this->getFrom() == null) {
            $sender = $this->getSenderFromConfig();
            $this->setFrom($sender['address'], $sender['name']);
        }

        // in service mitloggen wenn url vorhanden
        if (Kwf_Util_Model_MailLog::isAvailable()) {
            $r = Kwf_Model_Abstract::getInstance('Kwf_Util_Model_MailLog')->createRow();
            if (isset($_COOKIE['unitTest']) && $_COOKIE['unitTest']) {
                $r->identifier = $_COOKIE['unitTest'];
            }
            $attachmentFilenames = array();
            foreach ($this->_attachments as $attachment) {
                $attachmentFilenames[] = $attachment->filename;
            }
            $r->from = $this->_ownFrom;
            $r->return_path = $this->getReturnPath();
            $r->to = implode(';', $this->_ownTo);
            $r->cc = implode(';', $this->_ownCc);
            $r->bcc = implode(';', $this->_ownBcc);
            $r->attachment_filenames = implode(';', $attachmentFilenames);
            $r->subject = $this->getSubject();
            $r->body_text = $this->_bodyText;
            $r->body_html = $this->_bodyHtml;
            $r->save();
        }

        if ($this->getReturnPath() && !$transport) {
            $transport = new Zend_Mail_Transport_Sendmail('-f ' . $this->getReturnPath());
        }

        return parent::send($transport);
    }

    public static function getSenderFromConfig()
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        } else {
            $host = Kwf_Registry::get('config')->server->domain;
        }
        $hostNonWww = preg_replace('#^www\\.#', '', $host);
        return array(
            'address' => str_replace('%host%', $hostNonWww, Kwf_Registry::get('config')->email->from->address),
            'name' => str_replace('%host%', $hostNonWww, Kwf_Registry::get('config')->email->from->name)
        );
    }
}
