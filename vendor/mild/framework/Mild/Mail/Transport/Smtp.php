<?php
/**
 * Mild Framework component
 *
 * @author Mochammad Riyadh Ilham Akbar Pasya
 * @link https://github.com/mildphp/mild
 * @copyright 2018
 * @license https://github.com/mildphp/mild/blob/master/LICENSE (MIT Licence)
 */
namespace Mild\Mail\Transport;

use Mild\Mail\MailException;

class Smtp implements TransportInterface
{
    /**
     * @var array
     */
    protected $config;
    /**
     * @var resource
     */
    private $connection;

    /**
     * Smtp constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param \Mild\Mail\Message $message
     * @return bool
     * @throws MailException
     */
    public function send($message)
    {
        $this->connect();
        $this->command('MAIL FROM: <'.$message->getFrom().'>', 250);
        foreach (array_merge($message->getTo(), $message->getCc(), $message->getBcc()) as $value) {
            $this->command('RCPT TO: <'.$value.'>', [250, 251]);
        }
        $this->command('DATA', 354);
        $this->command($message->__toString());
        $this->command('.', 250);
        $this->disconnect();
        return true;
    }

    /**
     * @param null $key
     * @param null $default
     * @return mixed
     */
    public function getConfig($key = null, $default = null)
    {
        if ($key === null) {
            return $this->config;
        }
        if (!isset($this->config[$key])) {
            return $default;
        }
        return $this->config[$key];
    }

    /**
     * @return void
     * @throws MailException
     */
    protected function connect()
    {
        $host = $this->getConfig('host');
        $port = $this->getConfig('port');
        $username = $this->getConfig('username');
        $password = $this->getConfig('password');
        $timeout = $this->getConfig('timeout', 30);
        $ehlo = $this->getConfig('ehlo', $_SERVER['SERVER_NAME']);
        $encryption = strtolower($this->getConfig('encryption'));
        $auth = strtoupper($this->getConfig('auth', 'LOGIN'));
        if ($encryption === 'ssl' || strpos($host, 'ssl') === false) {
            $host = 'ssl://'.$host;
        }
        $this->connection = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if ($this->connection === false) {
            throw new MailException($errstr);
        }
        stream_set_timeout($this->connection, $timeout, 0);
        $this->response();
        try {
            $this->command('EHLO '.$ehlo, 250);
        } catch (MailException $e) {
            $this->command('HELO '.$ehlo, 250);
        }
        if ($encryption === 'tls') {
            $this->command('STARTTLS', 220);
            stream_socket_enable_crypto($this->connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        }
        switch ($auth) {
            case 'PLAIN':
                $this->command('AUTH PLAIN', 334);
                $this->command(base64_encode("\0".$username."\0".$password), 235);
                break;
            case 'LOGIN':
                $this->command('AUTH LOGIN', 334);
                $this->command(base64_encode($username), 334);
                $this->command(base64_encode($password), 235);
                break;
            case 'CRAM-MD5':
                $this->command(base64_encode($username.' '.hash_hmac('md5', base64_decode(substr($this->command('AUTH CRAM-MD5', 334), 4)), $password, false)), 235);
                break;
            default:
                throw new MailException('Unsupported ['.$auth.'] auth type.');
                break;
        }
    }

    /**
     * @param $command
     * @param array $code
     * @return string
     * @throws MailException
     */
    protected function command($command, $code = [])
    {
        fwrite($this->connection, $command."\r\n");
        if ($code === []) {
            return '';
        }
        if (!is_array($code)) {
            $code = [$code];
        }
        if (!in_array(substr($response = $this->response(), 0, 3), $code)) {
            throw new MailException($response);
        }
        return $response;
    }

    /**
     * @return string
     */
    protected function response()
    {
        $response = '';
        while($message = fgets($this->connection, 515)) {
            $response .= $message;
            if (substr($message, 3, 1) == ' ') {
                break;
            }
        }
        return $response;
    }

    /**
     * @return void
     * @throws MailException
     */
    protected function disconnect()
    {
        $this->command('QUIT');
        fclose($this->connection);
    }
}
