<?php

namespace crocodicstudio\crudbooster\contracts\Mail;

interface TemplateMailerInterface
{
    /**
     * Sends the cms_email_templates template `$config['template']` to `$config['to']`, replacing each
     * `[key]` with `$config['data']` (an array, object or model). With `send_at` the email is queued in
     * cms_email_queues instead and true is returned. `attachments` is a list of file paths.
     */
    public function send(array $config): ?bool;

    /**
     * Sends a cms_email_queues row.
     */
    public function sendQueued(object $queue): void;

    /**
     * The Laravel mailer for the "Mail Driver" setting, or null for the application's default mailer.
     */
    public function mailer(): ?string;
}
