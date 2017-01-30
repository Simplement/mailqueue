# Simplement \ MailQueue
Simplement\MailQueue is a small extension for Nette Framework, which will wrap default Nette `SendmailMailer` or `SmtpMailer` by it's own `Mailer` class. All outcoming mails are then redirected into mail queue, which is by default implemented by [Kdyby\Doctrine](https://github.com/Kdyby/Doctrine) entity. 

If you don't wan't to use default mail queue, you can simply implement your own by extending `Simplement\MailQueue\IEntry` and `Simplement\MailQueue\IQueue` interfaces and create e.g. file system mail queue instead of database system one.

Then you can setup cron, which will in given inteval send dose of fronted mails e.g. using command
```sh
php www/index.php mailqueue:sendfronted
```

## Requirements
[Nette Framework](https://nette.org/) and [Kdyby\Console](https://github.com/Kdyby/Console), recommended [Kdyby\Doctrine](https://github.com/Kdyby/Doctrine).
_______

## Benefits

 - All outcoming mails aren't sent inmediatelly, but they are moved to queue -> response to client is much faster.
 - You don't have to update you current app code. All mails are after setting this extension automatically redirected into mail queue.
 - If the app is unnable to send mail (due to loss of internet connection etc.) app will not crush, but reschedule mail and try it send again in set interval.
 - You can set priority to each mail.
 - You can set default `from` email address, which will be used if mail doesn't have it set.
______

## Configuration
```neon
extensions:							# Add MailQueue Extension
	mailqueue: Simplement\Bridges\MailDI\MailQueueExtension

mailqueue:
	timeLimit: 30					# Max executin time of cron script
	mailLimit: 50					# Max number of mails sent peer one cron call
	attemptLimit: 5					# Max number of attempts to send mail
	rescheduleTime: +30 minutes		# If unnable to send mail, try nex attempt after ...
	defaultSender: 'MyAwesomeApp <myawesomeapp@mail.com>'	# Optional



# If you want to use default Doctrine Mail Queue
services:
	mailQueue: Simplement\Bridges\DoctrineORM\MailQueue
	
doctrine:
	metadata:
		Simplement: %appDir%/../vendor/simplement/mailqueue/src/Bridges/DoctrineORM/Entity
```
______

## Usage
```php
 $message = new Nette\Mail\Message;
 
 :
 
 /** @var Nette\DI\Container $container */
 $container = ...;
 
 $mailer = $container->getByType('Nette\Mail\IMailer');
 or
 $mailer = $container->getService('mail.mailer');
 or
 $mailer = $container->getService('nette.mailer');
 
 /** @var Simplement\MailQueue\Mailer $mailer */
 $mailer->send($message, $priority = 1, $useQueue = TRUE);
```
