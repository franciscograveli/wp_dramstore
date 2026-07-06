<?php

namespace DgoraWcas\Admin\Promo;

use DgoraWcas\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NoticePolicy {

	public function isSettingsPage(): bool {
		return Helpers::isSettingsPage();
	}

	public function isFeedbackNoticeDismissed(): bool {
		return ! empty( get_option( FeedbackNotice::HIDE_NOTICE_OPT ) );
	}

	public function isPremium(): bool {
		return function_exists( 'dgoraAsfwFs' ) && dgoraAsfwFs()->is_premium();
	}

	public function shouldShowFeedbackNotice(): bool {
		if ( $this->isFeedbackNoticeDismissed() ) {
			return false;
		}

		if ( $this->isPremium() ) {
			return false;
		}

		if ( ! $this->isFiboSearch2TeaserDismissed() ) {
			return false;
		}

		$installDate = (int) get_option( FeedbackNotice::ACTIVATION_DATE_OPT );

		return $installDate > 0
			&& strtotime( '-7 days' ) >= $installDate
			&& current_user_can( 'install_plugins' );
	}

	public function isFiboSearch2TeaserDismissed( int $userId = 0 ): bool {
		$userId = $userId > 0 ? $userId : get_current_user_id();

		if ( $userId <= 0 ) {
			return true;
		}

		return (bool) get_user_meta( $userId, FiboSearch2Teaser::DISMISS_META_KEY, true );
	}

	public function shouldShowFiboSearch2Teaser(): bool {
		return $this->isSettingsPage()
			&& ! $this->isFiboSearch2TeaserDismissed();
	}
}
