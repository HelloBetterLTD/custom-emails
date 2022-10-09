<?php

namespace SilverStripers\CustomEmails\Transport;

use SilverStripe\Assets\File;
use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\HTTP;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\View\ArrayData;
use SilverStripe\View\SSViewer;
use SilverStripers\CustomEmails\Model\NotificationEmail;

/**
 * Class Processor
 * @package SilverStripers\CustomEmails\Transport
 *
 * @property NotificationEmail $notification
 * @property string $to
 * @property string $from
 * @property string $cc
 * @property string $bcc
 * @property string $replyTo
 * @property array $data
 */
class Processor
{
    use Injectable;

    private $notification;
    private $to;
    private $from;
    private $cc;
    private $bcc;
    private $replyTo;
    private $attachments = [];
    private $data = [];


    public function __construct($notification)
    {
        if (is_string($notification)) {
            $notification = NotificationEmail::get()->find('Type', $notification);
        }
        if (!$notification) {
            throw new \Exception('$notification cannot be empty');
        }
        $this->notification = $notification;
        if ($notification->FromEmail) {
            $this->setFrom($notification->FromEmail);
        }
        if ($notification->ToEmail) {
            $this->setTo($notification->ToEmail);
        }
        if ($notification->ReplyToEmail) {
            $this->setReplyTo($notification->ReplyToEmail);
        }
        if ($notification->CCEmail) {
            $this->setCC($notification->CCEmail);
        }
        if ($notification->BCCEmail) {
            $this->setBCC($notification->BCCEmail);
        }
    }

    public function setTo($to) : Processor
    {
        $to = is_array($to) ? $to : explode(',', $to);
        $this->to = $to;
        return $this;
    }

    public function setFrom($from) : Processor
    {
        $this->from = $from;
        return $this;
    }

    public function setCC($cc) : Processor
    {
        $cc = is_array($cc) ? $cc : explode(',', $cc);
        $this->cc = $cc;
        return $this;
    }

    public function setBCC($bcc) : Processor
    {
        $bcc = is_array($bcc) ? $bcc : explode(',', $bcc);
        $this->bcc = $bcc;
        return $this;
    }

    public function setReplyTo($replyTo) : Processor
    {
        $this->replyTo = $replyTo;
        return $this;
    }

    public function setData($data) : Processor
    {
        $this->data = $data;
        return $this;
    }

    public function setAttachments($attachments) : Processor
    {
        $this->attachments = $attachments;
        return $this;
    }

    public function validate() : bool
    {
        $arguments = $this->notification->getArguments();
        $keys = is_array($this->data) ? array_keys($this->data) : [];
        foreach ($arguments as $argument) {
            if (!in_array($argument, $keys)) {
                return false;
            }
        }
        return true;
    }

    public function send() : bool
    {
        if (Director::isDev()) {
            if (!$this->validate()) {
                throw new \Exception(sprintf('Email "%s" doesnt have all the data fields', $this->notification->Type));
            }
            if (empty($this->to)) {
                throw new \Exception(sprintf('Email "%s" doesnt have a to address', $this->notification->Type));
            }
            if (empty($this->notification->Subject)) {
                throw new \Exception(sprintf('Email "%s" doesnt have a subject', $this->notification->Type));
            }
            if (empty($this->notification->Body)) {
                throw new \Exception(sprintf('Email "%s" doesnt have a body', $this->notification->Type));
            }
        }

        $template = $this->notification->getTemplate();

        $titleTemplate = SSViewer::fromString($this->notification->Subject);
        $bodyTemplate = SSViewer::fromString($this->notification->dbObject('Body')->forTemplate());

        $mergeData = array_merge(
            $this->data,
            [
                'Time' => DBDatetime::now(),
                'Year' => DBDatetime::now()->Format('yyyy'),
                'To' => implode(',', is_array($this->to) ? $this->to : [$this->to])
            ]
        );

        $data = ArrayData::create($mergeData);

        $email = Email::create();
        $email->setTo($this->to);
        if ($this->from) {
            $email->setFrom($this->from);
        }
        if ($this->replyTo) {
            $email->setReplyTo($this->replyTo);
        }
        if ($this->cc) {
            $email->setCC($this->cc);
        }
        if ($this->bcc) {
            $email->setBCC($this->bcc);
        }
        $email->setSubject(HTTP::absoluteURLs($data->renderWith($titleTemplate)));

        if ($template) {
            $body = HTTP::absoluteURLs($data->renderWith($bodyTemplate));
            $email->setHTMLTemplate($template);
            $email->setData(array_merge([
                'Layout' => $body
            ], $mergeData));
        } else {
            $email->setBody(HTTP::absoluteURLs($data->renderWith($bodyTemplate)));
        }

        if ($this->notification->Attachments()->count()) {
            /* @var $attachment File */
            foreach ($this->notification->Attachments() as $attachment) {
                $email->addAttachmentFromData(
                    stream_get_contents($attachment->File->getStream()),
                    $attachment->Name
                );
            }
        }

        foreach ($this->attachments as $attachment => $content) {
            $email->addAttachmentFromData(
                $content,
                $attachment
            );
        }

        return $email->send();
    }

}
