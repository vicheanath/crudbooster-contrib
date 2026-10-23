<?php

namespace crocodicstudio\crudbooster\mail;

use crocodicstudio\crudbooster\contracts\Mail\TemplateMailerInterface;
use crocodicstudio\crudbooster\contracts\Settings\SettingsRepositoryInterface;
use crocodicstudio\crudbooster\models\CmsEmailQueue;
use crocodicstudio\crudbooster\models\CmsEmailTemplate;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\MailManager;
use Illuminate\Mail\Message;
use InvalidArgumentException;

final class TemplateMailer implements TemplateMailerInterface
{
    public function __construct(
        private readonly MailManager $mail,
        private readonly SettingsRepositoryInterface $settings,
        private readonly CmsEmailTemplate $templates,
        private readonly CmsEmailQueue $queues,
    ) {
    }

    public function send(array $config): ?bool
    {
        $slug = $config['template'] ?? null;
        $template = $this->templates->newQuery()->toBase()->where('slug', $slug)->first();
        if (! $template) {
            throw new InvalidArgumentException("Email template [$slug] does not exist.");
        }

        $html = $template->content;
        $subject = $template->subject;
        foreach ($this->replacements($config['data'] ?? []) as $key => $value) {
            if (! is_scalar($value) && $value !== null) {
                continue;
            }
            $html = str_replace('['.$key.']', $value, $html);
            $subject = str_replace('['.$key.']', $value, $subject);
        }

        $to = $config['to'] ?? null;
        $attachments = ($config['attachments'] ?? null) ?: [];

        if (($config['send_at'] ?? null) != null) {
            $this->queues->newQuery()->toBase()->insert([
                'send_at' => $config['send_at'],
                'email_recipient' => $to,
                'email_from_email' => $template->from_email ?: $this->settings->get('email_sender'),
                'email_from_name' => $template->from_name ?: $this->settings->get('appname'),
                'email_cc_email' => $template->cc_email,
                'email_subject' => $subject,
                'email_content' => $html,
                'email_attachments' => serialize($attachments),
                'is_sent' => 0,
            ]);

            return true;
        }

        $fromName = $template->from_email ? ($template->from_name ?: $this->settings->get('appname')) : null;
        $this->deliver($html, $to, $subject, $template->from_email, $fromName, $template->cc_email, $attachments);

        return null;
    }

    public function sendQueued(object $queue): void
    {
        // Attachments are file paths; objects are never revived from the queue table.
        $attachments = @unserialize((string) $queue->email_attachments, ['allowed_classes' => false]);

        $this->deliver(
            $queue->email_content,
            $queue->email_recipient,
            $queue->email_subject,
            $queue->email_from_email,
            $queue->email_from_name,
            $queue->email_cc_email,
            is_array($attachments) ? $attachments : []
        );
    }

    /**
     * Laravel 7+ ignores the old mail.driver/mail.host keys, so SMTP settings are applied as a
     * dedicated "cb_smtp" mailer; the "mail" driver keeps the app's default mailer.
     */
    public function mailer(): ?string
    {
        $driver = $this->settings->get('smtp_driver');

        if ($driver == 'smtp' && $this->settings->get('smtp_host')) {
            config(['mail.mailers.cb_smtp' => [
                'transport' => 'smtp',
                'host' => $this->settings->get('smtp_host'),
                'port' => $this->settings->get('smtp_port') ?: 587,
                'username' => $this->settings->get('smtp_username'),
                'password' => $this->settings->get('smtp_password'),
                'timeout' => null,
            ]]);
            $this->mail->purge('cb_smtp');

            return 'cb_smtp';
        }

        if ($driver == 'sendmail' && config('mail.mailers.sendmail')) {
            return 'sendmail';
        }

        return null;
    }

    private function deliver(?string $html, $to, ?string $subject, ?string $fromEmail, ?string $fromName, ?string $cc, array $attachments): void
    {
        $this->mail->mailer($this->mailer())->send('crudbooster::emails.blank', ['content' => $html], function (Message $message) use ($to, $subject, $fromEmail, $fromName, $cc, $attachments) {
            $message->priority(1);
            $message->to($to);
            if ($fromEmail) {
                $message->from($fromEmail, $fromName);
            }
            if ($cc) {
                $message->cc($cc);
            }
            foreach ($attachments as $attachment) {
                $message->attach($attachment);
            }
            $message->subject($subject);
        });
    }

    /**
     * Placeholder values from an array, a plain object or a model. A model's attributes are used as-is
     * (iterating the model itself yields its public properties, so `[password]` was never replaced).
     */
    private function replacements(mixed $data): array
    {
        return match (true) {
            $data instanceof Model => $data->getAttributes(),
            $data instanceof Arrayable => $data->toArray(),
            default => (array) $data,
        };
    }
}
