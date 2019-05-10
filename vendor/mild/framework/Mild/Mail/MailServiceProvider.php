<?php

namespace Mild\Mail;

use Mild\Mail\Transport\SendMail;
use Mild\Mail\Transport\Smtp;
use Mild\Supports\ServiceProvider;

class MailServiceProvider extends ServiceProvider
{
    /**
     * {inheritdoc}
     */
    public function register()
    {
        $this->app->set('mail', function ($app) {
            $config = $app->get('config')->get('mail');
            switch ($default = $config['default']) {
                case 'smtp':
                    $transport = new Smtp($config['drivers'][$default]);
                    break;
                case 'sendmail':
                    $transport = new SendMail($config['drivers'][$default]);
                    break;
                default:
                    throw new MailException('Unsupported ['.$default.'] driver.');
                    break;
            }
            return new Mailer($transport);
        });
    }
}