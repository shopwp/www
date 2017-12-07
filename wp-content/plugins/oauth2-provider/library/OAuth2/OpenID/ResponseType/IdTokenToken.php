<?php

namespace OAuth2\OpenID\ResponseType;

use OAuth2\ResponseType\AccessTokenInterface;
use OAuth2\Storage\WordPressdb as storage;

class IdTokenToken implements IdTokenTokenInterface {
	protected $accessToken;
	protected $idToken;

	public function __construct( AccessTokenInterface $accessToken, IdTokenInterface $idToken ) {
		$this->accessToken = $accessToken;
		$this->idToken = $idToken;
		$this->storage = new storage;
	}

	public function getAuthorizeResponse( $params, $user_id = null ) {
		$result = $this->accessToken->getAuthorizeResponse( $params, $user_id );

		$user_claims = $this->storage->getUserClaims( $user_id, $params['scope'] );
		$access_token = $result[1]['fragment']['access_token'];

		$id_token = $this->idToken->createIdToken( $params['client_id'], $user_id, $params['nonce'], $user_claims, $access_token );
		$result[1]['fragment']['id_token'] = $id_token;

		return $result;
	}
}
