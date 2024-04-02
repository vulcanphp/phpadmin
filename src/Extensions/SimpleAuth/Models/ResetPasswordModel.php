<?php

namespace VulcanPhp\PhpAdmin\Extensions\SimpleAuth\Models;

use VulcanPhp\PhpAdmin\Extensions\QForm\QForm;
use App\Models\User;
use App\Models\UserMeta;
use VulcanPhp\Core\Crypto\Encryption;
use VulcanPhp\SimpleDb\Model\BaseModel;

class ResetPasswordModel extends BaseModel
{
    public function labels(): array
    {
        return [
            'password'    => 'New Password',
            'confirmPassword' => 'Confirm Password',
        ];
    }

    public function rules(): array
    {
        return [
            'password'        => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 6], [self::RULE_MAX, 'max' => 48]],
            'confirmPassword' => [self::RULE_REQUIRED, [self::RULE_MATCH, 'match' => 'password']],
        ];
    }

    public static function fillable(): array
    {
        return ['confirmPassword', 'password', 'token', 'user'];
    }

    public function tokenValidate(): bool
    {
        $user_token = [];
        $token      = (array) Encryption::decryptArray($this->token ?? '');
        $this->user = User::find($token['id'] ?? 0);

        if ($this->user !== false) {
            $user_token = (array) decode_string($this->user->meta('reset_token'));
        }

        // check if token is valid and expire
        return ($token['token'] ?? '') === ($user_token['token'] ?? null) && time() < ($user_token['expire'] ?? 0);
    }

    public function reset(): bool
    {
        $this->user->password = $this->password;

        if ($this->user->save() && UserMeta::removeMeta(['reset_token'], $this->user->id)) {
            return true;
        } else {
            session()->setFlash('error', 'Something wen\'t wrong, please try again later.');
        }

        return false;
    }

    public function getQForm(): QForm
    {
        return QForm::begin($this, ['method' => 'post', 'action' => auth_url('reset')])
            ->addInput(['type' => 'hidden', 'name' => 'token'])
            ->addInput(['type' => 'password', 'name' => 'password'])
            ->addInput(['type' => 'password', 'name' => 'confirmPassword'])
            ->submit(['name' => 'Save Password', 'class' => 'tw-btn-block']);
    }
}
