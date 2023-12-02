<?php

namespace VulcanPhp\PhpAdmin\Models;

use App\Models\User;
use App\Models\UserMeta;

class Profile extends User
{
    public $oldPassword, $confirmPassword, $language;

    public function labels(): array
    {
        return [
            'name'            => 'Display name',
            'oldPassword'     => 'Setup New Password',
            'password'        => 'New Password',
            'confirmPassword' => 'Confirm Password',
        ];
    }

    public function saveProfile(): bool
    {
        $this->id = user('id');
        $saved_profile = false;

        if ($this->validate()) {

            $data = [
                'name'     => $this->name,
                'username' => $this->username,
                'email'    => $this->email,
            ];

            if (isset($this->password) && !empty($this->password)) {

                if (!isset($this->oldPassword) || empty($this->oldPassword)) {
                    $this->addError('oldPassword', 'This field is required');
                    return false;
                }

                if (strlen($this->password) < 6 || strlen($this->password) > 48) {
                    $this->addError('password', 'Password must be between 6 to 48 charecter.');
                    return false;
                }

                if (!isset($this->confirmPassword) || empty($this->confirmPassword)) {
                    $this->addError('confirmPassword', 'This field is required');
                    return false;
                }


                if ($this->password != $this->confirmPassword) {
                    $this->addError('confirmPassword', 'Password confirmation failed');
                    return false;
                }

                if (password(strval($this->oldPassword), strval(user('password'))) !== true) {
                    $this->addError('oldPassword', 'Incorrect Password');
                    return false;
                } else {
                    $data['password'] = password($this->password);

                    unset($this->password, $this->oldPassword, $this->confirmPassword);
                }
            }

            if (parent::put($data, ['id' => $this->id])) {
                auth()->getDriver()->StartCacheDB()->RemoveCacheUser(user('id'))->CloseCacheDB();
                $saved_profile = true;
            }

            if (UserMeta::saveMeta(['language' => $this->language], $this->id)) {
                $saved_profile = true;
            }
        }

        return $saved_profile;
    }
}
