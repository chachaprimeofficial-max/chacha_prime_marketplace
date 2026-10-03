<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
 use Queueable,SerializesModels;
 public function __construct(public string $resetUrl){}
 public function build(){return $this->subject('Reset your Chacha Prime password')->html('<h2>Chacha Prime</h2><p>We received a request to reset your password.</p><p><a href="'.$this->resetUrl.'">Reset password</a></p><p>This link expires in 60 minutes. If you did not request this, you can ignore this email.</p>');}
}