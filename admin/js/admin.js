/**
 * Feed URL Manager Pro - Admin JavaScript Controller
 *
 * @package FeedURLManagerPro
 */

/* global jQuery, fwmAdminData */
(function ($) {
	'use strict';

	$(document).ready(function () {
		var modal = $('#fwm-rule-modal');
		var form = $('#fwm-rule-form');
		var matchTypeSelect = $('#fwm-rule-match-type');
		var actionSelect = $('#fwm-rule-action');
		var redirectGroup = $('#fwm-redirect-url-group');
		var regexWarning = $('#fwm-regex-warning');

		// Toggle redirect field visibility in modal based on action code
		function updateModalFields() {
			var action = actionSelect.val();
			if (action === '301' || action === '302') {
				redirectGroup.slideDown(200);
			} else {
				redirectGroup.slideUp(200);
			}

			var matchType = matchTypeSelect.val();
			if (matchType === 'regex') {
				regexWarning.slideDown(200);
			} else {
				regexWarning.slideUp(200);
			}
		}

		actionSelect.on('change', updateModalFields);
		matchTypeSelect.on('change', updateModalFields);

		// Open Add Rule Modal
		$('#fwm-btn-add-rule').on('click', function (e) {
			e.preventDefault();
			form[0].reset();
			$('#fwm-rule-id').val('');
			$('#fwm-modal-title').text('Add Feed URL Rule');
			updateModalFields();
			modal.fadeIn(200);
		});

		// Close Modal
		$('#fwm-modal-close-btn, #fwm-modal-cancel-btn').on('click', function () {
			modal.fadeOut(200);
		});

		$(document).on('keyup', function (e) {
			if (e.key === 'Escape' && modal.is(':visible')) {
				modal.fadeOut(200);
			}
		});

		// Edit Rule Click
		$(document).on('click', '.fwm-btn-edit-rule', function (e) {
			e.preventDefault();
			var row = $(this).closest('tr');
			var ruleId = row.data('rule-id');
			var pattern = row.data('pattern');
			var matchType = row.data('match-type');
			var action = row.data('action');
			var redirectUrl = row.data('redirect-url');
			var status = row.data('status');

			$('#fwm-rule-id').val(ruleId);
			$('#fwm-rule-pattern').val(pattern);
			$('#fwm-rule-match-type').val(matchType);
			$('#fwm-rule-action').val(action);
			$('#fwm-rule-redirect-url').val(redirectUrl);
			$('#fwm-rule-status').val(status);

			$('#fwm-modal-title').text('Edit Feed URL Rule');
			updateModalFields();
			modal.fadeIn(200);
		});

		// Save Rule via AJAX
		form.on('submit', function (e) {
			e.preventDefault();
			var saveBtn = $('#fwm-modal-save-btn');
			saveBtn.prop('disabled', true).text('Saving...');

			var data = {
				action: 'fwm_save_rule',
				nonce: fwmAdminData.nonce,
				rule_id: $('#fwm-rule-id').val(),
				pattern: $('#fwm-rule-pattern').val(),
				match_type: $('#fwm-rule-match-type').val(),
				action_code: $('#fwm-rule-action').val(),
				redirect_url: $('#fwm-rule-redirect-url').val(),
				status: $('#fwm-rule-status').val()
			};

			$.post(fwmAdminData.ajaxUrl, data, function (response) {
				saveBtn.prop('disabled', false).text('Save Rule');
				if (response.success) {
					modal.fadeOut(200);
					window.location.reload();
				} else {
					alert(response.data.message || fwmAdminData.strings.errorOccurred);
				}
			}).fail(function () {
				saveBtn.prop('disabled', false).text('Save Rule');
				alert(fwmAdminData.strings.errorOccurred);
			});
		});

		// Delete Rule via AJAX
		$(document).on('click', '.fwm-btn-delete-rule', function (e) {
			e.preventDefault();
			if (!confirm(fwmAdminData.strings.confirmDeleteRule)) {
				return;
			}

			var row = $(this).closest('tr');
			var ruleId = $(this).data('rule-id');

			$.post(fwmAdminData.ajaxUrl, {
				action: 'fwm_delete_rule',
				nonce: fwmAdminData.nonce,
				rule_id: ruleId
			}, function (response) {
				if (response.success) {
					row.fadeOut(300, function () {
						$(this).remove();
						if ($('#fwm-rules-tbody tr').length === 0) {
							window.location.reload();
						}
					});
				} else {
					alert(response.data.message || fwmAdminData.strings.errorOccurred);
				}
			});
		});

		// Toggle Rule Status switch
		$(document).on('change', '.fwm-rule-toggle-status', function () {
			var checkbox = $(this);
			var ruleId = checkbox.data('rule-id');
			var isChecked = checkbox.is(':checked');
			var newStatus = isChecked ? 'active' : 'disabled';
			var statusText = checkbox.closest('td').find('.fwm-status-text');

			$.post(fwmAdminData.ajaxUrl, {
				action: 'fwm_toggle_rule',
				nonce: fwmAdminData.nonce,
				rule_id: ruleId,
				status: newStatus
			}, function (response) {
				if (response.success) {
					if (isChecked) {
						statusText.removeClass('fwm-text-muted').addClass('fwm-text-success').text('Active');
					} else {
						statusText.removeClass('fwm-text-success').addClass('fwm-text-muted').text('Disabled');
					}
				} else {
					checkbox.prop('checked', !isChecked);
					alert(response.data.message || fwmAdminData.strings.errorOccurred);
				}
			});
		});

		// Safe Feed Scanner Execution
		$('#fwm-btn-run-scanner').on('click', function (e) {
			e.preventDefault();
			var btn = $(this);
			btn.prop('disabled', true).text(fwmAdminData.strings.scanning);

			$.post(fwmAdminData.ajaxUrl, {
				action: 'fwm_run_feed_scanner',
				nonce: fwmAdminData.nonce
			}, function (response) {
				btn.prop('disabled', false).html('<span class="dashicons dashicons-search"></span> Scan Endpoints Now');
				if (response.success && response.data.results) {
					var tbody = $('#fwm-scanner-tbody');
					tbody.empty();

					$.each(response.data.results, function (i, item) {
						var decisionBadge = (item.decision === 'block')
							? '<span class="fwm-badge fwm-badge-danger">BLOCKED (' + item.response_code + ')</span>'
							: '<span class="fwm-badge fwm-badge-success">ALLOWED</span>';

						var row = '<tr>' +
							'<td><strong>' + escapeHtml(item.name) + '</strong></td>' +
							'<td><code>' + escapeHtml(item.path) + '</code></td>' +
							'<td>' + decisionBadge + '</td>' +
							'<td><small>' + escapeHtml(item.rule_name || 'None') + '</small></td>' +
							'</tr>';
						tbody.append(row);
					});

					$('#fwm-scanner-placeholder').hide();
					$('#fwm-scanner-results').slideDown(200);
				}
			}).fail(function () {
				btn.prop('disabled', false).text('Scan Endpoints Now');
				alert(fwmAdminData.strings.errorOccurred);
			});
		});

		// Live Single HTTP Test
		$('#fwm-btn-test-live').on('click', function (e) {
			e.preventDefault();
			var btn = $(this);
			var url = $('#fwm-test-url-input').val();
			var resultContainer = $('#fwm-live-test-result');

			if (!url) {
				alert('Please enter a valid URL.');
				return;
			}

			btn.prop('disabled', true).text(fwmAdminData.strings.testing);
			resultContainer.html('<div class="fwm-text-muted">Connecting to ' + escapeHtml(url) + '...</div>').show();

			$.post(fwmAdminData.ajaxUrl, {
				action: 'fwm_test_single_feed',
				nonce: fwmAdminData.nonce,
				url: url
			}, function (response) {
				btn.prop('disabled', false).text('Test Live URL');
				if (response.success) {
					var d = response.data;
					var statusClass = (d.status_code === 404 || d.status_code === 410) ? 'fwm-text-danger' : (d.status_code === 301 || d.status_code === 302 ? 'fwm-text-warning' : 'fwm-text-success');
					var html = '<div class="fwm-callout fwm-callout-info">' +
						'<strong>HTTP Status Code:</strong> <span class="' + statusClass + '" style="font-size: 16px; font-weight: 700;">' + d.status_code + '</span><br>' +
						'<strong>X-Robots-Tag:</strong> <code>' + (d.x_robots ? escapeHtml(d.x_robots) : 'None') + '</code><br>' +
						(d.location ? '<strong>Redirect Location:</strong> <code>' + escapeHtml(d.location) + '</code>' : '') +
						'</div>';
					resultContainer.html(html);
				} else {
					resultContainer.html('<div class="fwm-callout fwm-callout-warning">' + escapeHtml(response.data.message || 'Request failed.') + '</div>');
				}
			}).fail(function () {
				btn.prop('disabled', false).text('Test Live URL');
				resultContainer.html('<div class="fwm-callout fwm-callout-warning">' + fwmAdminData.strings.errorOccurred + '</div>');
			});
		});

		// Clear Debug Log
		$('#fwm-btn-clear-log').on('click', function (e) {
			e.preventDefault();
			var btn = $(this);
			btn.prop('disabled', true);

			$.post(fwmAdminData.ajaxUrl, {
				action: 'fwm_clear_debug_log',
				nonce: fwmAdminData.nonce
			}, function (response) {
				if (response.success) {
					$('#fwm-log-tbody').html('<tr><td colspan="6" class="fwm-text-center fwm-text-muted" style="padding: 24px;">Log cleared.</td></tr>');
				}
			});
		});

		// Copy Diagnostics
		$('#fwm-btn-copy-diagnostics').on('click', function (e) {
			e.preventDefault();
			var raw = $('#fwm-diagnostics-raw').val();
			if (navigator.clipboard) {
				navigator.clipboard.writeText(raw).then(function () {
					alert(fwmAdminData.strings.copied);
				});
			} else {
				var temp = $('<textarea>');
				$('body').append(temp);
				temp.val(raw).select();
				document.execCommand('copy');
				temp.remove();
				alert(fwmAdminData.strings.copied);
			}
		});

		// Import Config
		$('#fwm-btn-import').on('click', function (e) {
			e.preventDefault();
			var json = $('#fwm-import-textarea').val();
			if (!json) {
				alert('Please paste a JSON configuration.');
				return;
			}

			var btn = $(this);
			btn.prop('disabled', true).text('Importing...');

			$.post(fwmAdminData.ajaxUrl, {
				action: 'fwm_import_config',
				nonce: fwmAdminData.nonce,
				config_json: json
			}, function (response) {
				btn.prop('disabled', false).text('Import Configuration');
				if (response.success) {
					alert(fwmAdminData.strings.configImported);
					window.location.reload();
				} else {
					alert(response.data.message || fwmAdminData.strings.errorOccurred);
				}
			}).fail(function () {
				btn.prop('disabled', false).text('Import Configuration');
				alert(fwmAdminData.strings.errorOccurred);
			});
		});

		// Toggle Response tab global redirect row
		$('#fwm_default_response').on('change', function () {
			var val = $(this).val();
			if (val === '301' || val === '302') {
				$('#fwm-global-redirect-row').slideDown(200);
			} else {
				$('#fwm-global-redirect-row').slideUp(200);
			}
		});

		function escapeHtml(text) {
			if (!text) return '';
			return $('<div>').text(text).html();
		}
	});
})(jQuery);
