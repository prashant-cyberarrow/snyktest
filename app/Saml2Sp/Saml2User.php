<?php

namespace App\Saml2Sp;


class Saml2User
{

    protected $auth;

    function __construct($auth)
    {
        $this->auth = $auth;
    }

    /**
     * @return string User Id retrieved from assertion processed this request
     */
    function getUserId()
    {
        $authUser = $this->auth;

        return $authUser->getNameId();

    }

    /**
     * @return array attributes retrieved from assertion processed this request
     */
    function getAttributes()
    {
        $authUser = $this->auth;

        return $authUser->getAttributes();
    }

    /**
     * Returns the requested SAML attribute
     *
     * @param string $name The requested attribute of the user.
     * @return array|null Requested SAML attribute ($name).
     */
    function getAttribute($name) {
        $authUser = $this->auth;

        return $authUser->getAttribute($name);
    }
    
    /**
     * @return array attributes retrieved from assertion processed this request
     */
    function getAttributesWithFriendlyName()
    {
        $authUser = $this->auth;

        return $authUser->getAttributesWithFriendlyName();
    }

    /**
     * @return string the saml assertion processed this request
     */
    function getRawSamlAssertion()
    {
        return app('request')->input('SAMLResponse'); //just this request
    }

    function getIntendedUrl()
    {
        $relayState = app('request')->input('RelayState'); //just this request

        $url = app('Illuminate\Contracts\Routing\UrlGenerator');

        if ($relayState && $url->full() != $relayState) {

            return $relayState;
        }
    }

    /**
     * Parses a SAML property and adds this property to this user or returns the value
     *
     * @param string $samlAttribute
     * @param string $propertyName
     * @return array|null
     */
    function parseUserAttribute($samlAttribute = null, $propertyName = null) {
        if(empty($samlAttribute)) {
            return null;
        }
        if(empty($propertyName)) {
            return $this->getAttribute($samlAttribute);
        }

        return $this->{$propertyName} = $this->getAttribute($samlAttribute);
    }

    /**
     * Parse the saml attributes and adds it to this user
     *
     * @param array $attributes Array of properties which need to be parsed, like this ['email' => 'urn:oid:0.9.2342.19200300.100.1.3']
     */
    function parseAttributes($attributes = array()) {
        foreach($attributes as $propertyName => $samlAttribute) {
            $this->parseUserAttribute($samlAttribute, $propertyName);
        }
    }

    function getSessionIndex()
    {
        return $this->auth->getSessionIndex();
    }

    function getNameId()
    {
        return $this->auth->getNameId();
    }

}