<?php

namespace SlowProg\Mailer;

use Swift_Image;
use Swift_Message;
use Swift_Attachment;

/**
 * Class Message
 *
 * @package SlowProg\Mailer
 *
 * @method Swift_Message setBody($body, $contentType = null, $charset = null)
 * @method Swift_Message setFrom($emails, $name = null)
 * @method Swift_Message addPart($body, $contentType = null, $charset = null)
 */
class Message
{
    /**
     * The Swift Message instance.
     *
     * @var Swift_Message
     */
    protected $swift;

    /**
     * Create a new message instance.
     *
     * @param Swift_Message $swift
     */
    public function __construct(Swift_Message $swift)
    {
        $this->swift = $swift;
    }

    /**
     * Add a "from" email to the message.
     *
     * @param string $email
     * @param string $name
     *
     * @return Message
     */
    public function from($email, $name = null)
    {
        $this->swift->setFrom($email, $name);

        return $this;
    }

    /**
     * Set the "sender" of the message.
     *
     * @param string $email
     * @param string $name
     *
     * @return Message
     */
    public function sender($email, $name = null)
    {
        $this->swift->setSender($email, $name);

        return $this;
    }

    /**
     * Set the "return path" of the message.
     *
     * @param string $email
     *
     * @return Message
     */
    public function returnPath($email)
    {
        $this->swift->setReturnPath($email);

        return $this;
    }

    /**
     * Add a recipient to the message.
     *
     * @param string|array $email
     * @param string       $name
     *
     * @return Message
     */
    public function to($email, $name = null)
    {
        return $this->addEmails($email, $name, 'To');
    }

    /**
     * Add a carbon copy to the message.
     *
     * @param string $email
     * @param string $name
     *
     * @return Message
     */
    public function cc($email, $name = null)
    {
        return $this->addEmails($email, $name, 'Cc');
    }

    /**
     * Add a blind carbon copy to the message.
     *
     * @param string $email
     * @param string $name
     *
     * @return Message
     */
    public function bcc($email, $name = null)
    {
        return $this->addEmails($email, $name, 'Bcc');
    }

    /**
     * Add a reply to email to the message.
     *
     * @param string $email
     * @param string $name
     *
     * @return Message
     */
    public function replyTo($email, $name = null)
    {
        return $this->addEmails($email, $name, 'ReplyTo');
    }

    /**
     * Add a recipient to the message.
     *
     * @param string|array $email
     * @param string       $name
     * @param string       $type
     *
     * @return Message
     */
    protected function addEmails($email, $name, $type)
    {
        if (is_array($email)) {
            $this->swift->{"set{$type}"}($email, $name);
        } else {
            $this->swift->{"add{$type}"}($email, $name);
        }

        return $this;
    }

    /**
     * Set the subject of the message.
     *
     * @param string $subject
     *
     * @return Message
     */
    public function subject($subject)
    {
        $this->swift->setSubject($subject);

        return $this;
    }

    /**
     * Set the message priority level.
     *
     * @param int $level
     *
     * @return Message
     */
    public function priority($level)
    {
        $this->swift->setPriority($level);

        return $this;
    }

    /**
     * Attach a file to the message.
     *
     * @param string $file
     * @param array  $options
     *
     * @return Message
     */
    public function attach($file, array $options = [])
    {
        $attachment = $this->createAttachmentFromPath($file);

        return $this->prepAttachment($attachment, $options);
    }

    /**
     * Create a Swift Attachment instance.
     *
     * @param string $file
     *
     * @return Swift_Attachment
     */
    protected function createAttachmentFromPath($file)
    {
        return Swift_Attachment::fromPath($file);
    }

    /**
     * Attach in-memory data as an attachment.
     *
     * @param string $data
     * @param string $name
     * @param array  $options
     *
     * @return Message
     */
    public function attachData($data, $name, array $options = [])
    {
        $attachment = $this->createAttachmentFromData($data, $name);

        return $this->prepAttachment($attachment, $options);
    }

    /**
     * Create a Swift Attachment instance from data.
     *
     * @param string $data
     * @param string $name
     *
     * @return Swift_Attachment
     */
    protected function createAttachmentFromData($data, $name)
    {
        return Swift_Attachment::newInstance($data, $name);
    }

    /**
     * Embed a file in the message and get the CID.
     *
     * @param string $file
     *
     * @return string
     */
    public function embed($file)
    {
        return $this->swift->embed(Swift_Image::fromPath($file));
    }

    /**
     * Embed in-memory data in the message and get the CID.
     *
     * @param string $data
     * @param string $name
     * @param string $contentType
     *
     * @return string
     */
    public function embedData($data, $name, $contentType = null)
    {
        $image = Swift_Image::newInstance($data, $name, $contentType);

        return $this->swift->embed($image);
    }

    /**
     * Prepare and attach the given attachment.
     *
     * @param Swift_Attachment $attachment
     * @param array            $options
     *
     * @return Message
     */
    protected function prepAttachment(Swift_Attachment $attachment, array $options = [])
    {
        // First we will check for a MIME type on the message, which instructs the
        // mail client on what type of attachment the file is so that it may be
        // downloaded correctly by the user. The MIME option is not required.
        if (isset($options['mime'])) {
            $attachment->setContentType($options['mime']);
        }

        // If an alternative name was given as an option, we will set that on this
        // attachment so that it will be downloaded with the desired names from
        // the developer, otherwise the default file names will get assigned.
        if (isset($options['as'])) {
            $attachment->setFilename($options['as']);
        }

        $this->swift->attach($attachment);

        return $this;
    }

    /**
     * Get the underlying Swift Message instance.
     *
     * @return Swift_Message
     */
    public function getSwiftMessage()
    {
        return $this->swift;
    }

    /**
     * Dynamically pass missing methods to the Swift instance.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, array $parameters)
    {
        return call_user_func_array([$this->swift, $method], $parameters);
    }
}
