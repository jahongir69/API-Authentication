<?php

namespace App\Jobs;

use App\Models\User;
use App\Mail\VerificationEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendVerificationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $user;
    
    public function __construct(User $user)
    {
        $this->user = $user;
    }
    
    public function handle()
    {
    
        $verificationUrl = route('verification.verify', [
            'id'   => $this->user->id,
            'hash' => sha1($this->user->email),
        ]);
        
        Mail::to($this->user->email)->send(new VerificationEmail($this->user,  $verificationUrl));
    }
}
