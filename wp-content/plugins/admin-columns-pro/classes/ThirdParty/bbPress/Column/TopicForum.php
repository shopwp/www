<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_ThirdParty_bbPress_Column_TopicForum extends AC_Column
	implements ACP_Column_EditingInterface {

	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'bbp_topic_forum' );
	}

	public function editing() {
		return new ACP_ThirdParty_bbPress_Editing_TopicForum( $this );
	}

	public function is_valid() {
		return 'topic' === $this->get_post_type();
	}

}
