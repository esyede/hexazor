<?php

namespace System\Libraries\Mail;

defined('DS') or exit('No direct script access allowed.');

use Exception;
use System\Core\Config;

class Mail
{
    const CRLF = "\r\n";

    const TLS = 'tcp';

    const SSL = 'ssl';

    const OK = 250;

    protected $config;

    protected $socket;

    protected $subject;

    protected $recipients = [];

    protected $cc = [];

    protected $bcc = [];

    protected $sender = [];

    protected $replyTo = [];

    protected $attachments = [];

    protected $protocol = null;

    protected $port = null;

    protected $messageText = null;

    protected $messageHtml = null;

    protected $isHtml = false;

    protected $usingTLS = false;

    protected $logs = [];

    protected $charset = 'UTF-8';

    protected $headers = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->config = Config::get('email');

        $this->charset($this->config['charset']);
        $this->protocol($this->config['protocol']);
        $this->port($this->config['port']);

        $this->headers['MIME-Version'] = '1.0';
        $this->headers['X-Mailer'] = 'Hexazor Mail';

        if (filled($this->config['mailer'])) {
            $this->headers['X-Mailer'] = $this->config['mailer'];
        }
    }

    /**
     * Set pengirim email.
     *
     * @param string $address
     * @param string $name
     *
     * @return $this
     */
    public function from($address, $name = null)
    {
        $this->sender = [$address, $name];

        return $this;
    }

    /**
     * Set penerima.
     *
     * @param string $address
     * @param string $name
     *
     * @return $this
     */
    public function to($address, $name = null)
    {
        $this->recipients[] = [$address, $name];

        return $this;
    }

    /**
     * Set copy carbon.
     *
     * @param string $address
     * @param string $name
     *
     * @return $this
     */
    public function cc($address, $name = null)
    {
        $this->cc[] = [$address, $name];

        return $this;
    }

    /**
     * Set back copy carbon.
     *
     * @param string $address
     * @param string $name
     *
     * @return $this
     */
    public function bcc($address, $name = null)
    {
        $this->bcc[] = [$address, $name];

        return $this;
    }

    /**
     * Set reply-to.
     *
     * @param string $address
     * @param string $name
     *
     * @return $this
     */
    public function replyto($address, $name = null)
    {
        $this->replyTo[] = [$address, $name];

        return $this;
    }

    /**
     * Lampirkan file.
     *
     * @param string $path
     *
     * @return $this
     */
    public function attach($path)
    {
        if (!is_file($path)) {
            throw new Exception('Attachment not found: '.$path);
        }

        $this->attachments[] = $path;

        return $this;
    }

    /**
     * Set character set email.
     *
     * @param string $charset
     *
     * @return $this
     */
    public function charset($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Set protokol pengiriman email.
     *
     * @param int $protocol
     *
     * @return $this
     */
    public function protocol($protocol = null)
    {
        if (self::TLS === $protocol) {
            $this->usingTLS = true;
        }

        $this->protocol = $protocol;

        return $this;
    }

    /**
     * Set port pengiriman file.
     *
     * @param int $port
     *
     * @return $this
     */
    public function port($port = 587)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Set subject email.
     *
     * @param string $subject
     *
     * @return $this
     */
    public function subject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Set teks pesan isi email (plain text).
     *
     * @param string $msg
     *
     * @return $this
     */
    public function text($msg)
    {
        $this->messageText = $msg;
        $this->isHtml = false;

        return $this;
    }

    /**
     * Set teks pesan isi email (html).
     *
     * @param string $msg
     *
     * @return $this
     */
    public function html($msg)
    {
        $this->messageHtml = $msg;
        $this->isHtml = true;

        return $this;
    }

    /**
     * Kirim email.
     *
     * @return bool
     */
    public function send()
    {
        $this->socket = fsockopen(
            $this->getServer(),
            $this->port,
            $errorNumber,
            $errorMessage,
            $this->config['connection_timeout']
        );

        if (blank($this->socket)) {
            return false;
        }

        $this->logs['CONNECTION'] = $this->getResponse();
        $this->logs['HELLO'][1] = $this->sendCommand('EHLO '.$this->config['host']);

        if ($this->usingTLS) {
            $this->logs['STARTTLS'] = $this->sendCommand('STARTTLS');
            stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->logs['HELLO'][2] = $this->sendCommand('EHLO '.$this->config['host']);
        }

        $this->logs['AUTH'] = $this->sendCommand('AUTH LOGIN');
        $this->logs['USERNAME'] = $this->sendCommand(base64_encode($this->config['username']));
        $this->logs['PASSWORD'] = $this->sendCommand(base64_encode($this->config['password']));
        $this->logs['MAIL_FROM'] = $this->sendCommand('MAIL FROM: <'.$this->sender[0].'>');

        $recipients = array_merge($this->recipients, $this->cc, $this->bcc);

        foreach ($recipients as $address) {
            $this->logs['RECIPIENTS'][] = $this->sendCommand('RCPT TO: <'.$address[0].'>');
        }

        $this->headers['Date'] = date('r');
        $this->headers['Subject'] = $this->subject;
        $this->headers['From'] = $this->formatAddress($this->sender);
        $this->headers['Return-Path'] = $this->formatAddress($this->sender);
        $this->headers['To'] = $this->formatAddressList($this->recipients);

        if (filled($this->replyTo)) {
            $this->headers['Reply-To'] = $this->formatAddressList($this->replyTo);
        }

        if (filled($this->cc)) {
            $this->headers['Cc'] = $this->formatAddressList($this->cc);
        }

        if (filled($this->bcc)) {
            $this->headers['Bcc'] = $this->formatAddressList($this->bcc);
        }

        $boundary = md5(uniqid(random_int(9, 999), true));
        $msg = '';

        if (filled($this->attachments)) {
            $this->headers['Content-Type'] = 'multipart/mixed; boundary="mixed-'.$boundary.'"';
            $msg .= '--mixed-'.$boundary.self::CRLF;
            $msg .= 'Content-Type: multipart/alternative; '.
                'boundary="alt-'.$boundary.'"'.self::CRLF.self::CRLF;
        } else {
            $this->headers['Content-Type'] = 'multipart/alternative; boundary="alt-'.$boundary.'"';
        }

        if (filled($this->messageText)) {
            $msg .= '--alt-'.$boundary.self::CRLF;
            $msg .= 'Content-Type: text/plain; charset='.$this->charset.self::CRLF;
            $msg .= 'Content-Transfer-Encoding: base64'.self::CRLF.self::CRLF;
            $msg .= chunk_split(base64_encode($this->messageText)).self::CRLF;
        }

        if (filled($this->messageHtml)) {
            $msg .= '--alt-'.$boundary.self::CRLF;
            $msg .= 'Content-Type: text/html; charset='.$this->charset.self::CRLF;
            $msg .= 'Content-Transfer-Encoding: base64'.self::CRLF.self::CRLF;
            $msg .= chunk_split(base64_encode($this->messageHtml)).self::CRLF;
        }

        $msg .= '--alt-'.$boundary.'--'.self::CRLF.self::CRLF;

        if (filled($this->attachments)) {
            foreach ($this->attachments as $attachment) {
                $filename = pathinfo($attachment, PATHINFO_BASENAME);
                $contents = file_get_contents($attachment);
                $type = get_mime($attachment);

                $msg .= '--mixed-'.$boundary.self::CRLF;
                $msg .= 'Content-Type: '.$type.'; name="'.$filename.'"'.self::CRLF;
                $msg .= 'Content-Disposition: attachment; filename="'.$filename.'"'.self::CRLF;
                $msg .= 'Content-Transfer-Encoding: base64'.self::CRLF.self::CRLF;
                $msg .= chunk_split(base64_encode($contents)).self::CRLF;
            }

            $msg .= '--mixed-'.$boundary.'--';
        }

        $headers = '';

        foreach ($this->headers as $k => $v) {
            $headers .= $k.': '.$v.self::CRLF;
        }

        $data = $headers.self::CRLF.$msg.self::CRLF.'.';

        $this->logs['MESSAGE'] = $msg;
        $this->logs['HEADERS'] = $headers;
        $this->logs['DATA'][1] = $this->sendCommand('DATA');
        $this->logs['DATA'][2] = $this->sendCommand($data);
        $this->logs['QUIT'] = $this->sendCommand('QUIT');
        fclose($this->socket);

        return self::OK == substr($this->logs['DATA'][2], 0, 3);
    }

    /**
     * Lihat log pengiriman email (untuk debugging).
     *
     * @return array
     */
    public function logs()
    {
        return $this->logs;
    }

    /**
     * Ambil smtp server.
     *
     * @return string
     */
    protected function getServer()
    {
        return filled($this->protocol)
            ? $this->protocol.'://'.$this->config['host']
            : $this->config['host'];
    }

    /**
     * Ambil response dari mail server.
     *
     * @return string
     */
    protected function getResponse()
    {
        stream_set_timeout($this->socket, $this->config['response_timeout']);

        $response = '';
        while (false !== ($line = fgets($this->socket, 515))) {
            $response .= trim($line)."\n";
            if (' ' == substr($line, 3, 1)) {
                break;
            }
        }

        return trim($response);
    }

    /**
     * Kirim command smtp.
     *
     * @return string
     */
    protected function sendCommand($command)
    {
        fwrite($this->socket, $command.self::CRLF);

        return $this->getResponse();
    }

    /**
     * Format address email.
     *
     * @param string $address
     *
     * @return string
     */
    protected function formatAddress($address)
    {
        return (blank($address[1])) ? $address[0] : '"'.$address[1].'" <'.$address[0].'>';
    }

    /**
     * Format array address email.
     *
     * @param array $addresses
     *
     * @return string
     */
    protected function formatAddressList(array $addresses)
    {
        $data = [];
        foreach ($addresses as $address) {
            $data[] = $this->formatAddress($address);
        }

        return implode(', ', $data);
    }
}
