/**
 * Plugin deactivation feedback modal for the WordPress Plugins screen (free).
 */
( function ( $ ) {
	'use strict';

	if ( typeof smackDeactivationFeedbackFree === 'undefined' ) {
		return;
	}

	var config = smackDeactivationFeedbackFree;
	var strings = config.strings || {};
	var idPrefix = config.idPrefix || 'smack-deactivation-feedback-free';
	var reasonFieldName = 'smack_deactivation_feedback_reason_free';
	var $overlay = null;
	var $modal = null;
	var $body = null;
	var $success = null;
	var $error = null;
	var $otherWrap = null;
	var $comment = null;
	var $submitBtn = null;
	var $skipBtn = null;
	var $spinner = null;
	var deactivateUrl = '';
	var lastFocusedElement = null;
	var isSubmitting = false;
	var successDelayMs = 1500;

	function cacheElements() {
		$overlay = $( '#' + idPrefix + '-overlay' );
		$modal = $( '#' + idPrefix + '-modal' );
		$body = $( '#' + idPrefix + '-body' );
		$success = $( '#' + idPrefix + '-success' );
		$error = $( '#' + idPrefix + '-error' );
		$otherWrap = $( '#' + idPrefix + '-other-wrap' );
		$comment = $( '#' + idPrefix + '-comment' );
		$submitBtn = $overlay.find( '.smack-deactivation-feedback-submit' );
		$skipBtn = $overlay.find( '.smack-deactivation-feedback-skip' );
		$spinner = $overlay.find( '.smack-deactivation-feedback-spinner' );
	}

	function getDeactivateLinkSelector() {
		var encodedPlugin = encodeURIComponent( config.pluginBasename );

		return (
			'a[href*="action=deactivate"][href*="' + encodedPlugin + '"],' +
			'tr[data-plugin="' + config.pluginBasename + '"] .deactivate a,' +
			'#the-list tr[data-plugin="' + config.pluginBasename + '"] .row-actions .deactivate a,' +
			'#network-plugins tr[data-plugin="' + config.pluginBasename + '"] .row-actions .deactivate a'
		);
	}

	function isPluginDeactivateLink( $link ) {
		var href = $link.attr( 'href' ) || '';

		if ( href.indexOf( 'action=deactivate' ) === -1 ) {
			return false;
		}

		return (
			href.indexOf( encodeURIComponent( config.pluginBasename ) ) !== -1 ||
			href.indexOf( config.pluginBasename ) !== -1
		);
	}

	function toggleOtherField() {
		var selected = $( 'input[name="' + reasonFieldName + '"]:checked' ).val();

		if ( selected === 'other' ) {
			$otherWrap.removeAttr( 'hidden' );
		} else {
			$otherWrap.attr( 'hidden', 'hidden' );
		}
	}

	function setSubmitting( submitting ) {
		isSubmitting = submitting;
		$submitBtn.prop( 'disabled', submitting );
		$skipBtn.prop( 'disabled', submitting );

		if ( submitting ) {
			$spinner.addClass( 'is-active' );
			$submitBtn.addClass( 'is-busy' );
		} else {
			$spinner.removeClass( 'is-active' );
			$submitBtn.removeClass( 'is-busy' );
		}
	}

	function resetModalState() {
		$body.removeAttr( 'hidden' );
		$success.attr( 'hidden', 'hidden' );
		$error.attr( 'hidden', 'hidden' ).text( '' );
		$( 'input[name="' + reasonFieldName + '"][value="temporary_testing"]' ).prop( 'checked', true );
		$comment.val( '' );
		toggleOtherField();
		setSubmitting( false );
	}

	function showSuccessState( message ) {
		$body.attr( 'hidden', 'hidden' );
		$error.attr( 'hidden', 'hidden' );
		$success.removeAttr( 'hidden' );

		if ( message ) {
			$success.find( '.smack-deactivation-feedback-success-message' ).text( message );
		}
	}

	function showError( message ) {
		$error.text( message || strings.ajaxError ).removeAttr( 'hidden' );
	}

	function openModal( url ) {
		deactivateUrl = url;
		lastFocusedElement = document.activeElement;
		resetModalState();

		$overlay.removeAttr( 'hidden' ).attr( 'aria-hidden', 'false' );
		$( 'body' ).addClass( 'smack-deactivation-feedback-open' );
		$modal.trigger( 'focus' );
	}

	function closeModal() {
		if ( isSubmitting ) {
			return;
		}

		$overlay.attr( 'hidden', 'hidden' ).attr( 'aria-hidden', 'true' );
		$( 'body' ).removeClass( 'smack-deactivation-feedback-open' );
		deactivateUrl = '';
		resetModalState();

		if ( lastFocusedElement && typeof lastFocusedElement.focus === 'function' ) {
			lastFocusedElement.focus();
		}
	}

	function proceedWithDeactivation() {
		if ( ! deactivateUrl ) {
			return;
		}

		window.location.href = deactivateUrl;
	}

	function submitFeedback() {
		if ( isSubmitting || ! deactivateUrl ) {
			return;
		}

		var reason = $( 'input[name="' + reasonFieldName + '"]:checked' ).val() || '';
		var comment = $comment.val() || '';

		$error.attr( 'hidden', 'hidden' ).text( '' );
		setSubmitting( true );

		$.ajax( {
			url: config.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: config.action,
				nonce: config.nonce,
				feedback_reason: reason,
				feedback_comment: comment,
			},
		} )
			.done( function ( response ) {
				if ( response && response.success ) {
					setSubmitting( false );
					showSuccessState(
						( response.data && response.data.message ) ? response.data.message : strings.successMessage
					);
					window.setTimeout( proceedWithDeactivation, successDelayMs );
					return;
				}

				setSubmitting( false );
				showError(
					( response && response.data && response.data.message ) ? response.data.message : strings.ajaxError
				);
			} )
			.fail( function ( xhr ) {
				setSubmitting( false );

				var message = strings.ajaxError;

				if ( xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ) {
					message = xhr.responseJSON.data.message;
				}

				showError( message );
			} );
	}

	function bindEvents() {
		$( document ).on( 'click', getDeactivateLinkSelector(), function ( event ) {
			var $link = $( this );

			if ( ! isPluginDeactivateLink( $link ) ) {
				return;
			}

			event.preventDefault();
			event.stopPropagation();
			openModal( $link.attr( 'href' ) );
		} );

		$overlay.on( 'change', 'input[name="' + reasonFieldName + '"]', toggleOtherField );

		$submitBtn.on( 'click', submitFeedback );

		$skipBtn.on( 'click', function () {
			if ( isSubmitting ) {
				return;
			}
			proceedWithDeactivation();
		} );

		$overlay.on( 'click', function ( event ) {
			if ( event.target === $overlay.get( 0 ) ) {
				closeModal();
			}
		} );

		$overlay.find( '.smack-deactivation-feedback-close' ).on( 'click', closeModal );

		$( document ).on( 'keydown', function ( event ) {
			if ( ! $overlay.length || $overlay.is( '[hidden]' ) ) {
				return;
			}

			if ( event.key === 'Escape' || event.keyCode === 27 ) {
				event.preventDefault();
				closeModal();
			}
		} );
	}

	$( function () {
		cacheElements();

		if ( ! $overlay.length || ! $modal.length ) {
			return;
		}

		bindEvents();
	} );
}( jQuery ) );
