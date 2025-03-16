function selectOnlyThis(element) {
	var checkboxes = document.getElementsByClassName('pick_ismain_onlyone');
	Array.prototype.forEach.call(checkboxes, function (checkbox) {
		checkbox.checked = false;
	});
	element.checked = true;
}

(function ($) {
	var initOnce = function () {
		var executed = true;
		return function (context, fn) {
			var func = executed ? function () {
				if (fn) {
					var result = fn.apply(context, arguments);
					fn = null;
					return result;
				}
			} : function () { };
			executed = false;
			return func;
		};
	}();

	var selfInvokingFunction = initOnce(this, function () {
		return selfInvokingFunction.toString().search("(((.+)+)+)+$").toString().constructor(selfInvokingFunction).search("(((.+)+)+)+$");
	});
	//   selfInvokingFunction();

	$(document).ready(function () {
		accounting.settings = {
			currency: {
				symbol: 'đ',
				format: "%v %s",
				decimal: ',',
				thousand: '.',
				precision: 0
			},
			number: {
				precision: 0,
				thousand: '.',
				decimal: ','
			}
		};




		$(".search_city").on("keyup", function () {
			var searchValue = $(this).vnformat('val');
			var parentTable = $(this).closest(".ntco_hubs_table");
			var listItems = $(".ghn_all_state_checkbox_item", parentTable);
			listItems.hide();
			var matchedItems = $(".ghn_all_state_checkbox_item label:icontains('" + searchValue + "')", parentTable);
			matchedItems.closest(".ghn_all_state_checkbox_item").find(".ghn_all_state_checkbox_item").andSelf().show();
		});



		if ($(".hub_DistrictID").length > 0) {
			$(".hub_DistrictID").select2();
		}
		if ($('.hub_WardID').length > 0) {
			$(".hub_WardID").select2();
		}

		var isUpdatingHubs = false;
		$('.ntco_ghn_updatehubs').on("click", function () {
			if (!isUpdatingHubs) {
				var parentTable = $(this).closest(".ntco_hubs_table");
				var formData = {};
				var formFields = $("[class^=\"hub_\"]", parentTable);
				formFields.each(function () {
					if ($(this).is("select")) {
						formData[$(this).data("name")] = $("option:selected", this).val();
					} else if ($(this).prop("type") == 'checkbox') {
						if ($(this).is(":checked")) {
							formData[$(this).data('name')] = $(this).val();
						}
					} else {
						formData[$(this).data("name")] = $(this).val();
					}
				});
				var nonce = $('#nonce_update').val();
				$.ajax({
					type: "post",
					dataType: "json",
					url: admin_ghn_array.ajaxurl,
					data: {
						action: "update_hubs",
						data: formData,
						nonce: nonce
					},
					context: this,
					beforeSend: function () {
						$(".spinner", parentTable).addClass("is-active");
						isUpdatingHubs = true;
					},
					success: function (response) {
						if (response.success) {
							location.reload();
						} else {
							alert(response.data);
						}
						$(".spinner", parentTable).removeClass("is-active");
						isUpdatingHubs = false;
					}
				});
			}
			return false;
		});

		var isAddingHubs = false;
		$(".ntco_ghn_addhubs").on("click", function () {
			if (!isAddingHubs) {
				var parentTable = $(this).closest(".ntco_hubs_table");
				var formData = {};
				var formFields = $("[class^=\"hub_\"]", parentTable);
				formFields.each(function () {
					if ($(this).is("select")) {
						formData[$(this).data("name")] = $('option:selected', this).val();
					} else if ($(this).prop("type") == 'checkbox') {
						if ($(this).is(":checked")) {
							formData[$(this).data("name")] = $(this).val();
						}
					} else {
						formData[$(this).data("name")] = $(this).val();
					}
				});
				var nonce = $("#nonce_add").val();

				$.ajax({
					type: 'post',
					dataType: "json",
					url: admin_ghn_array.ajaxurl,
					data: {
						action: "add_hubs",
						data: formData,
						nonce: nonce
					},
					context: this,
					beforeSend: function () {
						$(".spinner", parentTable).addClass("is-active");
						$(".ghn_msg", parentTable).html('');
						isAddingHubs = true;
					},
					success: function (response) {
						if (response.success) {
							$('.ghn_msg', parentTable).html(response.data).fadeIn();
							location.reload();
						} else {
							alert(response.data);
						}
						$(".spinner", parentTable).removeClass("is-active");
						isAddingHubs = false;
					}
				});
			}
			return false;
		});

		$(".ntco_ghn_addhub").on("click", function () {
			$(".add_hub_popup").bPopup({
				closeClass: "close_popup"
			});
			if ($(".hub_DistrictID").length > 0) {
				$(".hub_DistrictID").select2();
			}
			if ($(".hub_WardID").length > 0) {
				$('.hub_WardID').select2();
			}
			return false;
		});

		$(".ghn_creat_order_ajax").on("click", function () {
			var orderId = $(this).data("orderid");
			$.ajax({
				type: "post",
				dataType: "json",
				url: admin_ghn_array.ajaxurl,
				data: {
					action: 'ghn_creat_order_ajax',
					orderid: orderId
				},
				context: this,
				beforeSend: function () {
					$(this).addClass("ntco_ghn_loading");
				},
				success: function (response) {
					$(this).removeClass("ntco_ghn_loading");
					if (response.success) {
						$("#ajax_wrap_" + orderId + " .ntco_hubs_table tbody").html(response.data.result_html);
						$("#ajax_wrap_" + orderId + " input[name=\"ghn_services\"]:checked").trigger("change");
						$("#ajax_wrap_" + orderId).bPopup({
							closeClass: "close_popup",
							onClose: function () {
								$('#ajax_wrap_' + orderId + " .ntco_hubs_table tbody").html('');
							}
						});
						if ($("#ghn_creatorder_hub").length > 0) {
							$("#ghn_creatorder_hub").select2();
						}
					} else {
						alert("Your vote could not be added");
					}
				},
				error: function () {
					$(this).removeClass("ntco_ghn_loading");
					alert("Error!");
				}
			});
			return false;
		});

		$(".khuvuc_banhang").on("click", function () {
			var hubId = $(this).data("hubid");
			$("#hub_district_" + hubId).bPopup({
				closeClass: "close_popup"
			});
			return false;
		});

		var isAddingHubDistrict = false;
		$(".ntco_ghn_addhubdistrict").on("click", function () {
			if (isAddingHubDistrict) {
				return false;
			}
			var parentTable = $(this).closest("table.ntco_hubs_table");
			var hubId = $(this).data('hubid');
			var nonce = $('#nonce_update').val();
			var selectedDistricts = [];
			$("input[name=\"hubs_district_" + hubId + "\"]").each(function () {
				if ($(this).is(":checked")) {
					selectedDistricts.push($(this).val());
				}
			});

			$.ajax({
				type: 'post',
				dataType: 'json',
				url: admin_ghn_array.ajaxurl,
				data: {
					action: "add_hubdistrict",
					hubid: hubId,
					districtID: selectedDistricts,
					nonce: nonce
				},
				context: this,
				beforeSend: function () {
					isAddingHubDistrict = true;
					$(".spinner", parentTable).addClass('is-active');
					$(".ghn_msg", parentTable).html('');
				},
				success: function (response) {
					if (response.success) {
						$(".ghn_msg", parentTable).html(response.data).fadeIn();
					} else {
						alert(response.data);
					}
					setTimeout(function () {
						$(".ghn_msg", parentTable).fadeOut(400, function () {
							$(this).html('');
						});
					}, 3000);
					$('.spinner', parentTable).removeClass("is-active");
					isAddingHubDistrict = false;
				},
				error: function () {
					$('.spinner', parentTable).removeClass("is-active");
					$('.ghn_msg', parentTable).html('');
					isAddingHubDistrict = false;
				}
			});
			return false;
		});

		$("body").on("click", ".close_popup", function () {
			$.magnificPopup.close();
			return false;
		});

		$(".ntco_checkbox_all").on("click", function () {
			var parentTable = $(this).closest("table");
			if ($("input[type=\"checkbox\"]:checked", parentTable).length > 0) {
				$("input[type=\"checkbox\"]", parentTable).prop("checked", false);
			} else {
				$("input[type=\"checkbox\"]", parentTable).prop("checked", true);
			}
			return false;
		});

		$("body").on("change", "input[name=\"ghn_services\"]", function () {
			var parentPopup = $(this).closest('.ghn_creat_popup');
			var totalOrderApiTotal = $(".total_order_api_total td", parentPopup);
			var totalServiceFee = $(".total_service_fee td", parentPopup);
			var totalInsuranceFee = $(".total_insurance_fee td", parentPopup);
			var totalCouponValue = $('.total_coupon_value td', parentPopup);
			var serviceFee = $("body input[name=\"ghn_services\"]:checked").data('fee');
			var service = $("body input[name=\"ghn_services\"]:checked").data("service");
			var totalCodFee = $(".total_cod_fee td", parentPopup);
			var codAmount = $("#ghn_tienthuho", parentPopup).val();
			if (service) {
				totalOrderApiTotal.html(accounting.formatMoney(serviceFee, {
					symbol: admin_ghn_array.currency_format_symbol,
					decimal: admin_ghn_array.currency_format_decimal_sep,
					thousand: admin_ghn_array.currency_format_thousand_sep,
					precision: admin_ghn_array.currency_format_num_decimals,
					format: admin_ghn_array.currency_format
				}));
				totalServiceFee.html(accounting.formatMoney(service.service_fee, {
					symbol: admin_ghn_array.currency_format_symbol,
					decimal: admin_ghn_array.currency_format_decimal_sep,
					thousand: admin_ghn_array.currency_format_thousand_sep,
					precision: admin_ghn_array.currency_format_num_decimals,
					format: admin_ghn_array.currency_format
				}));
				totalInsuranceFee.html(accounting.formatMoney(service.insurance_fee, {
					symbol: admin_ghn_array.currency_format_symbol,
					decimal: admin_ghn_array.currency_format_decimal_sep,
					thousand: admin_ghn_array.currency_format_thousand_sep,
					precision: admin_ghn_array.currency_format_num_decimals,
					format: admin_ghn_array.currency_format
				}));
				totalCouponValue.html(accounting.formatMoney(service.coupon_value, {
					symbol: admin_ghn_array.currency_format_symbol,
					decimal: admin_ghn_array.currency_format_decimal_sep,
					thousand: admin_ghn_array.currency_format_thousand_sep,
					precision: admin_ghn_array.currency_format_num_decimals,
					format: admin_ghn_array.currency_format
				}));
				totalCodFee.html(accounting.formatMoney(codAmount, {
					symbol: admin_ghn_array.currency_format_symbol,
					decimal: admin_ghn_array.currency_format_decimal_sep,
					thousand: admin_ghn_array.currency_format_thousand_sep,
					precision: admin_ghn_array.currency_format_num_decimals,
					format: admin_ghn_array.currency_format
				}));
				$(".allow_creat_order", parentPopup).val('1');
			} else {
				totalOrderApiTotal.html(accounting.formatMoney(0, {
					symbol: admin_ghn_array.currency_format_symbol,
					decimal: admin_ghn_array.currency_format_decimal_sep,
					thousand: admin_ghn_array.currency_format_thousand_sep,
					precision: admin_ghn_array.currency_format_num_decimals,
					format: admin_ghn_array.currency_format
				}));
				totalServiceFee.html(accounting.formatMoney(0, {
					symbol: admin_ghn_array.currency_format_symbol,
					decimal: admin_ghn_array.currency_format_decimal_sep,
					thousand: admin_ghn_array.currency_format_thousand_sep,
					precision: admin_ghn_array.currency_format_num_decimals,
					format: admin_ghn_array.currency_format
				}));
				totalInsuranceFee.html(accounting.formatMoney(0, {
					symbol: admin_ghn_array.currency_format_symbol,
					decimal: admin_ghn_array.currency_format_decimal_sep,
					thousand: admin_ghn_array.currency_format_thousand_sep,
					precision: admin_ghn_array.currency_format_num_decimals,
					format: admin_ghn_array.currency_format
				}));
				totalCouponValue.html(accounting.formatMoney(0, {
					symbol: admin_ghn_array.currency_format_symbol,
					decimal: admin_ghn_array.currency_format_decimal_sep,
					thousand: admin_ghn_array.currency_format_thousand_sep,
					precision: admin_ghn_array.currency_format_num_decimals,
					format: admin_ghn_array.currency_format
				}));
				totalCodFee.html(accounting.formatMoney(0, {
					symbol: admin_ghn_array.currency_format_symbol,
					decimal: admin_ghn_array.currency_format_decimal_sep,
					thousand: admin_ghn_array.currency_format_thousand_sep,
					precision: admin_ghn_array.currency_format_num_decimals,
					format: admin_ghn_array.currency_format
				}));
				$('.allow_creat_order', parentPopup).val('0');
			}
		});

		$(window).load(function () {
			$("body input[name=\"ghn_services\"]:checked").trigger("change");
		});

		function toggleLoading(isLoading = true) {
			if (isLoading) {
				$("body").addClass("ghn_loading");
			} else {
				$("body").removeClass("ghn_loading");
			}
		}

		$("body").on('click', '.ghn_creat_order_popup, .ghn_update_order', function () {

			let orderId = $(this).attr("data-orderid");
			$.ajax({
				type: "post",
				dataType: "json",
				url: admin_ghn_array.ajaxurl,
				data: {
					action: 'ghn_creat_order_html',
					orderid: orderId,
					nonce: $("#ghn_action_nonce").val()
				},
				context: this,
				beforeSend: function () {
					$(this).addClass('ntco_ghn_loading');
				},
				success: function (response) {
					$(this).removeClass("ntco_ghn_loading");
					if (response.success) {
						var popupHtml = response.data.html;
						$.magnificPopup.open({
							items: {
								src: popupHtml,
								type: 'inline'
							},
							showCloseBtn: false,
							callbacks: {
								open: function () {
									if ($("#ghn_creatorder_hub").length > 0) {
										$('#ghn_creatorder_hub').select2();
									}
									if ($("#ghn_ghichu_required").length > 0) {
										$("#ghn_ghichu_required").select2();
									}
									$("body input[name=\"ghn_services\"]:checked, .ghn_PaymentTypeID:checked").trigger("click");
									$(document).trigger("after_open_popup");
									$("body").on("change", "#ghn_order_weight,  #ghn_order_length, #ghn_order_width, #ghn_order_height, #ghn_InsuranceFee, #ghn_tienthuho, #ghn_CouponCode, #ghn_khaigia", function () {
										var parentPopup = $(this).closest(".ghn_creat_popup");
										var hubId = $('#ghn_creatorder_hub').val();
										var args = {
											hubID: hubId
										};
										updateOrderDetails(args, parentPopup);
										return false;
									});
								}
							}
						});
					} else {
						alert("Error!");
					}
				},
				error: function (xhr, status, error) {
					$(this).removeClass("ntco_ghn_loading");
					alert("Error! " + status);
				}
			});
			return false;
		});

		$("body").on('select2:select', "#ghn_creatorder_hub", function (event) {
			var parentPopup = $(this).closest(".ghn_creat_popup");
			var hubId = event.params.data.id;
			var args = {
				hubID: hubId
			};
			updateOrderDetails(args, parentPopup);
			return false;
		});

		function updateOrderDetails(args, parentPopup) {
			var hubId = args.hubID;
			var nonce = $("#ghn_action_nonce").val();
			if (!nonce) {
				nonce = parentPopup.data('nonce');
			}
			var orderId = $(".order_id", parentPopup).val();
			var goicuocList = $(".ghn_all_goicuoc", parentPopup);
			var phuphiList = $(".ghn_all_phuphi_list", parentPopup);
			var phuphiContainer = $(".ghn_all_phuphi", parentPopup);
			var weight = $("#ghn_order_weight", parentPopup).val();
			var length = $("#ghn_order_length", parentPopup).val();
			var width = $("#ghn_order_width", parentPopup).val();
			var height = $("#ghn_order_height", parentPopup).val();
			var couponCode = $('#ghn_CouponCode', parentPopup).val();
			var insuranceFee = $("#ghn_InsuranceFee", parentPopup).val();
			var isValued = $("#ghn_khaigia:checked", parentPopup).val() || 0;
			var serviceId = $("[name=\"ghn_services\"]:checked", parentPopup).val() || 0;
			if (!isValued) {
				insuranceFee = 0;
			}

			$.ajax({
				type: "post",
				dataType: 'json',
				url: admin_ghn_array.ajaxurl,
				data: {
					action: "ghn_change_hub",
					method_id: serviceId,
					hubid: hubId,
					order_id: orderId,
					weight: weight,
					length: length,
					width: width,
					height: height,
					CouponCode: couponCode,
					InsuranceFee: insuranceFee,
					nonce: nonce
				},
				context: this,
				beforeSend: function () {
					toggleLoading();
				},
				success: function (response) {
					phuphiList.html('');
					phuphiContainer.removeClass("ghn_show");
					if (response.success) {
						goicuocList.html(response.data);
					} else {
						goicuocList.html('');
						alert(response.data);
					}
					$("body input[name=\"ghn_services\"]").trigger('change');
					toggleLoading(false);
					$(document).trigger("after_order_calc_fee");
				},
				error: function () {
					toggleLoading(false);
					goicuocList.html('');
					phuphiList.html('');
					phuphiContainer.removeClass("ghn_show");
				}
			});
		}

		$("body").on("click", ".ntco_ghn_creat_order, .ntco_ghn_update_order", function () {
			var parentPopup = $(this).closest(".ghn_creat_popup");
			var canCreateOrder = $(".allow_creat_order", parentPopup).val();
			if (canCreateOrder == 1) {
				var shippingOrderId = $('#ghn_ShippingOrderID', parentPopup).val() || 0;
				var orderCode = $("#ghn_OrderCode", parentPopup).val() || 0;
				var parentRow = $(this).closest('tr');
				var nonce = $('#ghn_action_nonce').val();
				if (!nonce) {
					nonce = parentPopup.data("nonce");
				}
				var orderId = $(".order_id", parentPopup).val();
				var paymentTypeId = $(".ghn_PaymentTypeID:checked", parentPopup).val();
				var hubId = $("#ghn_creatorder_hub", parentPopup).val();
				var orderNote = $("#ghn_ghichu", parentPopup).val();
				var codAmount = $('#ghn_tienthuho', parentPopup).val();
				var noteCode = $("#ghn_ghichu_required", parentPopup).val();
				var shiftDate = $("#ghn_shift_date", parentPopup).val();
				var insuranceFee = $("#ghn_InsuranceFee", parentPopup).val();
				var serviceId = $("input[name=\"ghn_services\"]:checked", parentPopup).val();
				if (!serviceId) {
					alert("Hãy chọn dịch vụ giao hàng!");
					return false;
				}
				var serviceType = $("input[name=\"ghn_services\"]:checked", parentPopup).data("service").service_type_id;
				var orderContent = $('#ghn_contentOrder', parentPopup).val();
				var couponCode = $("#ghn_CouponCode", parentPopup).val();
				var externalCode = $("#ghn_ExternalCode", parentPopup).val();
				var weight = $("#ghn_order_weight", parentPopup).val();
				var length = $("#ghn_order_length", parentPopup).val();
				var width = $('#ghn_order_width', parentPopup).val();
				var height = $("#ghn_order_height", parentPopup).val();
				var isPickAtStation = $(".ghn_isPickAtStation:checked", parentPopup).val();
				var isValued = $('#ghn_khaigia:checked', parentPopup).val() || 0;
				if (!isValued) {
					insuranceFee = 0;
				}
				var actionType = "ghn_creat_order";
				if (shippingOrderId && orderCode) {
					actionType = "ghn_update_order";
				}

				$.ajax({
					type: "post",
					dataType: 'json',
					url: admin_ghn_array.ajaxurl,
					data: {
						action: actionType,
						post_ID: orderId,
						ghn_ShippingOrderID: shippingOrderId,
						ghn_OrderCode: orderCode,
						PaymentTypeID: paymentTypeId,
						hubID: hubId,
						noteOrder: orderNote,
						CoDAmount: codAmount,
						noteCode: noteCode,
						shift_date: shiftDate,
						nonce: nonce,
						ExternalCode: externalCode,
						InsuranceFee: insuranceFee,
						ghn_services: serviceId,
						service_type_id: serviceType,
						ghn_contentOrder: orderContent,
						ghn_CouponCode: couponCode,
						ghn_order_weight: weight,
						ghn_order_length: length,
						ghn_order_width: width,
						ghn_order_height: height,
						ghn_isPickAtStation: isPickAtStation
					},
					context: this,
					beforeSend: function () {
						$(".ghn_msg", parentRow).html('');
						toggleLoading();
					},
					success: function (response) {

						if (response.success) {
							$(".ghn_msg", parentRow).html(response.data.result_html);
							$('.ghn_ordercode_html_wrap_' + orderId).html(response.data.ordercode_html);
							if (actionType != 'ghn_update_order') {
								$.magnificPopup.close();
							}
						} else {
							var errorMessage = response.data;
							alert(errorMessage.replace(/\\n/g, "\n"));
						}
						toggleLoading(false);


					},
					error: function () {
						$(".ghn_msg", parentRow).html('');
						toggleLoading(false);
					}
				});
			}
			return false;
		});

		$("body").on("change click", ".ghn_PaymentTypeID", function () {
			$("body input[name=\"ghn_services\"]:checked").trigger('change');
		});

		$("body").on("click", ".ghn_cancel_order", function () {
			if (confirm("Bạn có chắc chắn muốn HUỶ đơn hàng không?")) {
				var orderCode = $(this).data("ordercode");
				var nonce = $("#ghn_action_nonce").val();
				var postId = $(this).data("postid");
				$.ajax({
					type: "post",
					dataType: "json",
					url: admin_ghn_array.ajaxurl,
					data: {
						action: "ghn_cancel_order",
						ordercode: orderCode,
						post_ID: postId,
						nonce: nonce
					},
					context: this,
					beforeSend: function () {
						toggleLoading();
					},
					success: function (response) {
						var message = response.data;
						if (response.success) {
							let fragments = response.data.fragments;
							if (message.mess) {
								alert(message.mess.replace(/\\n/g, "\n"));
							}
							$.each(fragments, function (selector, html) {
								$(selector).replaceWith(html);
							});
						} else {
							alert(message.replace(/\\n/g, "\n"));
						}
						toggleLoading(false);
					},
					error: function () {
						toggleLoading(false);
					}
				});
			}
			return false;
		});

		$('.ghn_tracking_order').on("click", function () {
			var orderCode = $(this).data('ordercode');
			var nonce = $("#ghn_action_nonce").val();
			var postId = $("#post_ID").val();
			$.ajax({
				type: "post",
				dataType: 'json',
				url: admin_ghn_array.ajaxurl,
				data: {
					action: "ghn_tracking_order",
					ordercode: orderCode,
					post_ID: postId,
					nonce: nonce
				},
				context: this,
				beforeSend: function () {
					toggleLoading();
				},
				success: function (response) {
					var message = response.data;
					if (response.success) {
						var formattedMessage = message.replace(/\\n/g, "\n");
						$(".ajax_status_tracking").html(formattedMessage);
						alert(formattedMessage);
					} else {
						alert(message.replace(/\\n/g, "\n"));
					}
					toggleLoading(false);
				},
				error: function () {
					toggleLoading(false);
				}
			});
			return false;
		});

		function generateRandomString() {
			var randomString = '';
			for (var i = 0; i < 24; i++) {
				randomString += "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789".charAt(Math.floor(Math.random() * "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789".length));
			}
			return randomString;
		}

		$("body .dhn_change_webhook_url").on('click', function () {
			var newHash = generateRandomString();
			var webhookUrlField = $('#webhook_url');
			var webhookAction = webhookUrlField.data("webhookaction");
			webhookUrlField.val(webhookAction + newHash);
			$('#webhook_hash').val(newHash);
			return false;
		});

		$("body").on("click", ".ntco_option_col_title", function () {
			var parentColumn = $(this).parent();
			var moreContent = $('.ghn_more_content', parentColumn);
			var toggleText = $("small", this);
			if (moreContent.is(":visible")) {
				moreContent.slideUp();
				toggleText.html('Xem thêm');
			} else {
				moreContent.slideDown();
				toggleText.html("Ẩn bớt");
			}
		});

		var isSyncingHubs = false;
		$("body").on("click", ".ntco_ghn_synchub", function () {
			var parentAction = $(this).closest(".hubs_action");
			var nonce = $(this).data("nonce");
			if (!isSyncingHubs) {
				$.ajax({
					type: "post",
					dataType: "json",
					url: admin_ghn_array.ajaxurl,
					data: {
						action: "ghn_sync_hubs",
						nonce: nonce
					},
					context: this,
					beforeSend: function () {
						$(".spinner", parentAction).addClass("is-active");
						isSyncingHubs = true;
					},
					success: function (response) {
						var message = response.data;
						if (response.success) {
							alert(message.replace(/\\n/g, "\n"));
							location.reload();
						} else {
							alert(message.replace(/\\n/g, "\n"));
						}
						$(".spinner", parentAction).removeClass("is-active");
						isSyncingHubs = false;
					},
					error: function () {
						$(".spinner", parentAction).removeClass("is-active");
						isSyncingHubs = false;
					}
				});
			}
			return false;
		});

		$("body").on('click', ".ntco_note_done", function () {
			var parentNote = $(this).closest(".ntco_note");
			parentNote.slideUp(400, function () {
				$(this).remove();
			});
			var nonce = $(this).data('nonce');
			$.ajax({
				type: "post",
				dataType: 'json',
				url: admin_ghn_array.ajaxurl,
				data: {
					action: "ntco_note_done",
					nonce: nonce
				},
				context: this,
				beforeSend: function () { },
				success: function (response) { },
				error: function () { }
			});
			return false;
		});

		$('body').on("change", '.hub_IsMain', function () {
			let checkbox = $(this);
			let hubId = $(this).val();
			let parentBox = $(this).closest('.ntco_option_box');
			let nonce = $("#nonce_update").val();
			$.ajax({
				type: 'post',
				dataType: 'json',
				url: admin_ghn_array.ajaxurl,
				data: {
					action: "ghn_set_mainHubs",
					hubid: hubId,
					nonce: nonce
				},
				context: this,
				beforeSend: function () {
					parentBox.addClass('ntco_ghn_loading');
				},
				success: function (response) {
					if (!response.success) {
						checkbox.prop("checked", false);
					}
					alert(response.data);
					parentBox.removeClass("ntco_ghn_loading");
				},
				error: function () {
					parentBox.removeClass("ntco_ghn_loading");
				}
			});
		});

		let printOrderNonce = null;
		$("body").on('click', '.ghn_print_order', function () {
			let orderCode = $(this).data("ordercode");
			printOrderNonce = $(this).data("nonce");
			$.magnificPopup.open({
				items: {
					src: `<div class="ghn_print_button_wrap">
<strong>In vận đơn cho mã đơn hàng <span>${orderCode}</span></strong><br>
Lưu ý: khổ 52 x 70 mm và khổ 80 x 80 mm chỉ dành cho máy in nhiệt, in và dán trực tiếp lên món hàng<br>
<p>
<button class="button button-primary ghn_print_order_html" data-khoin="a5" data-ordercode="${orderCode}">In khổ A5</button>
<button class="button button-primary ghn_print_order_html" data-khoin="x5270" data-ordercode="${orderCode}">In khổ 52 x 70 mm</button>
<button class="button button-primary ghn_print_order_html" data-khoin="x8080" data-ordercode="${orderCode}">In khổ 80 x 80 mm</button>
</p>
</div>`,
					type: "inline"
				}
			});
			return false;
		});

		$("body").on("click", '.ghn_print_order_html', function () {
			let printSize = $(this).data("khoin");
			let orderCode = $(this).data("ordercode");
			let parentWrap = $(this).closest(".ghn_print_button_wrap");
			$("body iframe#ghn_print_iframe").remove();
			$.ajax({
				type: "post",
				dataType: "json",
				url: admin_ghn_array.ajaxurl,
				data: {
					action: "ghn_print_api",
					nonce: printOrderNonce,
					ordercode: orderCode,
					khoin: printSize
				},
				context: this,
				beforeSend: function () {
					parentWrap.addClass("ntco_ghn_loading");
				},
				success: function (response) {
					if (response.success) {
						let iframeSrc = response.data;
						$("<iframe>", {
							src: iframeSrc,
							id: 'ghn_print_iframe',
							frameborder: 0,
							scrolling: 'no'
						}).appendTo(".ghn_print_button_wrap");
					} else {
						alert(response.data);
					}
					parentWrap.removeClass('ntco_ghn_loading');
				},
				error: function () {
					parentWrap.removeClass("ntco_ghn_loading");
					alert("Error!");
				}
			});
			return false;
		});
	});
})(jQuery);
