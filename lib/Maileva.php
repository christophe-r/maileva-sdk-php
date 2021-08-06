<?php

/*
 * API pour envoyer et suivre des Lettres Recommandées En Ligne distribuées par le facteur  Elles comprennent les fonctions clés pour :   
 * - créer un envoi,  
 * - ajouter des documents et des destinataires,  
 * - choisir ses options (noir & blanc ou couleur, recto ou recto-verso, avec ou sans AR …).  
 * - suivre la production (date de planification, suivi de l’envoi…).  Voir la documentation \"notification_center\".
 */

namespace Maileva\Client;

use GuzzleHttp\ClientInterface;
use Maileva\Client\Configuration;
use Maileva\Client\Api\EnvoiApi;
use Maileva\Client\Api\DocumentsApi;
use Maileva\Client\Api\DestinatairesApi;

/**
 * Description of Maileva
 *
 * @author ceddyg
 */
class Maileva
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var Configuration
     */
    protected $config;
    
    protected $authUrl;
    
    public function __construct(
        $username,
        $password,
        $client_id,
        $client_secret,
        ClientInterface $client, 
        Configuration $config,
        $authUrl='https://api.sandbox.maileva.net/authentication/oauth2/token'
    )
    {
        $this->client = $client;
        $this->config = $config;
        
        $this->config->setUsername($username);
        $this->config->setPassword($password);
        $this->authUrl = $authUrl;
        
        $this->connexion($client_id, $client_secret);
    }
    
    protected function connexion($client_id, $client_secret)
    {       
        $response = $this->client->send(
            new \GuzzleHttp\Psr7\Request(
                'POST',
                $this->authUrl,
                [
                    'Cache-Control' => 'no-cache',
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                \GuzzleHttp\Psr7\Query::build(
                    [
                        'username' => $this->config->getUsername(),
                        'password' => $this->config->getPassword(),
                        'grant_type' => 'password',
                        'client_id' => $client_id,
                        'client_secret' => $client_secret
                    ]
                )
            )
        )->getBody()->getContents();

        $this->config->setAccessToken(
            json_decode($response)->access_token
        );
    }
    
    /**
     * 
     * @return EnvoiApi
     */
    public function envoi()
    {
        return new EnvoiApi(
            $this->client,
            $this->config
        );
    }
    
    /**
     * 
     * @return DocumentsApi
     */
    public function documents()
    {
        return new DocumentsApi(
            $this->client,
            $this->config
        );
    }
    
    /**
     * 
     * @return DestinatairesApi
     */
    public function destinataires()
    {
        return new DestinatairesApi(
            $this->client,
            $this->config
        );
    }
}
