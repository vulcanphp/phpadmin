<?php

namespace VulcanPhp\PhpAdmin\Extensions\SimpleAuth\Models;

use App\Models\User;
use App\Models\UserMeta;
use VulcanPhp\Core\Crypto\Encryption;
use VulcanPhp\Core\Crypto\Hash;
use VulcanPhp\Core\Helpers\Mail;
use VulcanPhp\PhpAdmin\Extensions\QForm\QForm;

class RecoverPasswordModel extends User
{
    public function labels(): array
    {
        return [
            'email' => 'Email Address',
        ];
    }

    public function rules(): array
    {
        return [
            'email' => [self::RULE_REQUIRED, self::RULE_EMAIL],
        ];
    }

    public function sendResetLink(): bool
    {
        $user = parent::find(['email' => $this->email]);

        if ($user === false) {
            $this->addError('email', 'User does not exist with this email address');
            return false;
        } elseif ($user->status != config('auth.status.activated')) {
            $this->addError('email', sprintf("Your Account Has Been %s.", array_search($user->status, config('auth.status'))));
            return false;
        }

        $token = Hash::random(32, 'alphanumeric');
        $data  = [
            'name' => $user->getDisplayName(),
            'link' => auth_url('reset') . '?token=' . Encryption::encryptArray(['id' => $user->id, 'token' => $token]),
        ];

        $send = [
            'subject' => 'You have requested to reset your password',
            'email'   => $user->email,
            'name'    => $user->getDisplayName(),
        ];

        if (Mail::template('reset', $data)->to($send)->send()) {
            return UserMeta::saveMeta(['reset_token' => encode_string(['token' => $token, 'expire' => strtotime('+ 1 day')])], $user->id);
        }

        $this->addError('email', 'Something Went\'t Wrong. Please Try Again Later.');

        return false;
    }

    public function getQForm(): QForm
    {
        return QForm::begin($this, ['method' => 'post', 'action' => auth_url('forget')])
            ->addInput(['type' => 'email', 'name' => 'email'])
            ->submit(['name' => 'Send Recover Password Link', 'class' => 'tw-btn-block']);
    }
}
