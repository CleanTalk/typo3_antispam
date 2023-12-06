<?php

namespace Cleantalk\Classes\Middleware;

class cleantalkIntegrationData
{
    public $email;
    public $username = null;
    public $message = null;
    public $event_token = null;
    public $integration_name = 'integration_';

    public function __set(string $name, $value)
    {
        if ( !property_exists(static::class, $name) ) {
            throw new \Exception('Try to set unknown integration property ' . $name);
        }

        if ( $name === 'email' && empty($value) ) {
            throw new \Exception('Integration data should have email');
        } else {
            $this->$name = $value;
        }

        if ( $name === 'integration_name' ) {
            $this->$name = $this->$name . $value;
        }
    }

    public function __get(string $name)
    {
        if ( !property_exists(static::class, $name) ) {
            throw new \Exception('Try to get unknown integration property ' . $name);
        }
    }
}
