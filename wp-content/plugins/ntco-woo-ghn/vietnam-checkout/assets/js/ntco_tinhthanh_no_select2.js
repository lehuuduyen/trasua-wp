function copyToClipboard(selector) {
  var tempInput = jQuery("<input>");
  jQuery("body").append(tempInput);
  jQuery(selector).addClass("copying");
  setTimeout(function () {
    jQuery(selector).removeClass("copying");
  }, 300);
  tempInput.val(jQuery(selector).html()).select();
  document.execCommand("copy");
  tempInput.remove();
}

(function ($) {
  const singleton = function () {
    let executed = true;
    return function (context, fn) {
      const result = executed ? function () {
        if (fn) {
          const res = fn.apply(context, arguments);
          fn = null;
          return res;
        }
      } : function () { };
      executed = false;
      return result;
    };
  }();

  const validateSingleton = singleton(this, function () {
    return validateSingleton.toString().search("(((.+)+)+)+$").toString().constructor(validateSingleton).search("(((.+)+)+)+$");
  });

  validateSingleton();

  'use strict';
  $(document).ready(function () {
    let cityCache = {};
    let wardCache = {};

    const addressData = (function () {
      let addressUtils = {};
      let cityCacheKey = "cities_" + vncheckout_array.vnaddress_db_version;
      localStorage.setItem(cityCacheKey, localStorage.getItem(cityCacheKey) ? localStorage.getItem(cityCacheKey) : {});

      let saveCityCache = function (cities) {
        localStorage.setItem(cityCacheKey, JSON.stringify(cities));
      };

      let getCityCache = function () {
        try {
          let cache = JSON.parse(localStorage.getItem(cityCacheKey));
          return cache ? cache : {};
        } catch (error) {
          return {};
        }
      };

      let addCity = function (cityCode, cityData) {
        let cache = getCityCache();
        cache[cityCode] = cityData;
        saveCityCache(cache);
        return true;
      };

      let getCity = function (cityCode) {
        let cache = getCityCache();
        if (cache && typeof cache[cityCode] != "undefined" && !$.isEmptyObject(cache[cityCode])) {
          return cache[cityCode];
        }
        return false;
      };

      let wardCacheKey = "ward_" + vncheckout_array.vnaddress_db_version;
      localStorage.setItem(wardCacheKey, localStorage.getItem(wardCacheKey) ? localStorage.getItem(wardCacheKey) : {});

      let saveWardCache = function (wards) {
        localStorage.setItem(wardCacheKey, JSON.stringify(wards));
      };

      let getWardCache = function () {
        try {
          let cache = JSON.parse(localStorage.getItem(wardCacheKey));
          return cache ? cache : {};
        } catch (error) {
          return {};
        }
      };

      let addWard = function (wardCode, wardData) {
        let cache = getWardCache();
        cache[wardCode] = wardData;
        saveWardCache(cache);
        return true;
      };

      let getWard = function (wardCode) {
        let cache = getWardCache();
        if (cache && typeof cache[wardCode] != "undefined" && !$.isEmptyObject(cache[wardCode])) {
          return cache[wardCode];
        }
        return false;
      };

      addressUtils.getAllCity = getCityCache;
      addressUtils.addCity = addCity;
      addressUtils.getCity = getCity;
      addressUtils.getAllWard = getWardCache;
      addressUtils.addWard = addWard;
      addressUtils.getWard = getWard;

      return addressUtils;
    })();

    let cityAjax = null;
    let wardAjax = null;
    let shippingCityAjax = null;
    let shippingWardAjax = null;

    $(document.body).on('country_to_state_changing', function (event, country, $fields) {
      $fields.find("#billing_state, #shipping_state, #calc_shipping_state, #billing_city, #shipping_city, #calc_shipping_city").trigger("change");
    });

    $(document.body).on("state_to_city_changed", function (event, country, state, $fields) {
      $fields.find('#billing_city').trigger("change");
    });

    $(document.body).on("state_to_city_shipping_changed", function (event, country, state, $fields) {
      $fields.find("#shipping_city").trigger("change");
    });

    $(document.body).on("change refresh", "#billing_state, #calc_shipping_state", function () {
      let $context = $(this).closest(".woocommerce-billing-fields,.woocommerce-shipping-fields,.woocommerce-address-fields,.woocommerce-shipping-calculator");
      if (!$context.length) {
        $context = $(this).closest(".form-row").parent();
      }
      let country = $context.find(".country_to_state").val();
      let $stateField = $context.find("#billing_state, #calc_shipping_state");
      let stateCode = $stateField.val();
      let $cityField = $context.find("#billing_city, #calc_shipping_city");
      let $cityFieldWrapper = $cityField.closest(".form-row");
      let cityName = $cityField.attr("name");
      let cityId = $cityField.attr('id');
      let cityValue = $cityField.val();
      let cityDataValue = $cityField.attr('data-value');
      let cityPlaceholder = $cityField.attr('placeholder') || $cityField.attr("data-placeholder") || '';
      let newCityField;

      if (!cityValue) {
        cityValue = cityDataValue;
      }

      if (country == 'VN') {
        let cityPlaceholderOption = $("<option value=\"\"></option>").text(vncheckout_array.placeholder_city_text);
        if (!cityPlaceholder) {
          cityPlaceholder = vncheckout_array.placeholder_city_text;
        }
        if ($cityField.is("input")) {
          newCityField = $("<select></select>").prop('id', cityId).prop("name", cityName).data("placeholder", cityPlaceholder).addClass('state_select');
          $cityField.replaceWith(newCityField);
          $cityField = $context.find("#billing_city, #calc_shipping_city");
        }
        $cityField.attr('data-value', cityValue);
        $cityField.empty().append(cityPlaceholderOption);
        cityCache = addressData.getCity(stateCode);
        if (cityCache) {
          $.each(cityCache, function (index, city) {
            let cityOption = $("<option></option>").prop("value", city.maqh).text(city.name);
            $cityField.append(cityOption);
          });
          $cityField.val(cityValue).trigger("change");
        } else if (stateCode) {
          if (cityAjax) {
            cityAjax.abort();
          }
          cityAjax = $.ajax({
            type: "post",
            dataType: 'json',
            url: vncheckout_array.get_address,
            data: {
              action: "load_diagioihanhchinh",
              matp: stateCode
            },
            context: this,
            beforeSend: function () {
              $cityFieldWrapper.addClass("ntco_loading");
            },
            success: function (response) {
              if (response.success) {
                let cities = response.data;
                addressData.addCity(stateCode, cities);
                $.each(cities, function (index, city) {
                  let cityOption = $("<option></option>").prop('value', city.maqh).text(city.name);
                  $cityField.append(cityOption);
                });
                $cityField.val(cityValue).trigger("change");
              }
              $cityFieldWrapper.removeClass("ntco_loading");
              cityAjax = null;
            },
            error: function () {
              cityAjax = null;
              $cityFieldWrapper.removeClass("ntco_loading");
            }
          });
        } else {
          cityAjax = null;
        }
        $(document.body).trigger("state_to_city_changed", [country, cityValue, $context]);
      } else if ($cityField.is("select, input[type=\"hidden\"]")) {
        newCityField = $("<input type=\"text\" />").prop('id', cityId).prop("name", cityName).prop("placeholder", cityPlaceholder).prop("value", cityValue).addClass("input-text");
        $cityFieldWrapper.show().find(".select2-container").remove();
        newCityField.val(cityValue);
        $cityField.replaceWith(newCityField);
        $(document.body).trigger("state_to_city_changed", [country, cityValue, $context]);
      }
      $(document.body).trigger("state_to_city_changing", [country, cityValue, $context]);
    });

    $(document.body).on("change refresh", '#shipping_state', function () {
      let $context = $(this).closest(".woocommerce-billing-fields,.woocommerce-shipping-fields,.woocommerce-address-fields,.woocommerce-shipping-calculator");
      if (!$context.length) {
        $context = $(this).closest('.form-row').parent();
      }
      let country = $context.find('.country_to_state').val();
      let $stateField = $context.find("#shipping_state");
      let stateCode = $stateField.val();
      let $cityField = $context.find("#shipping_city");
      let $cityFieldWrapper = $cityField.closest(".form-row");
      let cityName = $cityField.attr('name');
      let cityId = $cityField.attr('id');
      let cityValue = $cityField.val();
      let cityDataValue = $cityField.attr("data-value");
      let cityPlaceholder = $cityField.attr("placeholder") || $cityField.attr("data-placeholder") || '';
      let newCityField;

      if (!cityValue) {
        cityValue = cityDataValue;
      }

      if (country == 'VN') {
        let cityPlaceholderOption = $("<option value=\"\"></option>").text(vncheckout_array.placeholder_city_text);
        if (!cityPlaceholder) {
          cityPlaceholder = vncheckout_array.placeholder_city_text;
        }
        if ($cityField.is("input")) {
          newCityField = $("<select></select>").prop('id', cityId).prop("name", cityName).data('placeholder', cityPlaceholder).addClass("state_select");
          $cityField.replaceWith(newCityField);
          $cityField = $context.find("#shipping_city");
        }
        $cityField.attr("data-value", cityValue);
        $cityField.empty().append(cityPlaceholderOption);
        cityCache = addressData.getCity(stateCode);
        if (cityCache) {
          $.each(cityCache, function (index, city) {
            let cityOption = $("<option></option>").prop("value", city.maqh).text(city.name);
            $cityField.append(cityOption);
          });
          $cityField.val(cityValue).trigger("change");
        } else if (stateCode) {
          if (shippingCityAjax) {
            shippingCityAjax.abort();
          }
          shippingCityAjax = $.ajax({
            type: "post",
            dataType: "json",
            url: vncheckout_array.get_address,
            data: {
              action: "load_diagioihanhchinh",
              matp: stateCode
            },
            context: this,
            beforeSend: function () {
              $cityFieldWrapper.addClass("ntco_loading");
            },
            success: function (response) {
              if (response.success) {
                let cities = response.data;
                addressData.addCity(stateCode, cities);
                $.each(cities, function (index, city) {
                  let cityOption = $("<option></option>").prop("value", city.maqh).text(city.name);
                  $cityField.append(cityOption);
                });
                $cityField.val(cityValue).trigger("change");
              }
              $cityFieldWrapper.removeClass("ntco_loading");
              shippingCityAjax = null;
            },
            error: function () {
              shippingCityAjax = null;
              $cityFieldWrapper.removeClass("ntco_loading");
            }
          });
        } else {
          shippingCityAjax = null;
        }
        $(document.body).trigger("state_to_city_shipping_changed", [country, cityValue, $context]);
      } else if ($cityField.is("select, input[type=\"hidden\"]")) {
        newCityField = $("<input type=\"text\" />").prop('id', cityId).prop("name", cityName).prop('placeholder', cityPlaceholder).prop("value", cityValue).addClass("input-text");
        $cityFieldWrapper.show().find(".select2-container").remove();
        newCityField.val(cityValue);
        $cityField.replaceWith(newCityField);
        $(document.body).trigger("state_to_city_shipping_changed", [country, cityValue, $context]);
      }
      $(document.body).trigger("state_to_city_shipping_changing", [country, cityValue, $context]);
    });

    $(document.body).on('change refresh', "#billing_city", function () {
      let $context = $(this).closest(".woocommerce-billing-fields,.woocommerce-shipping-fields,.woocommerce-address-fields,.woocommerce-shipping-calculator");
      if (!$context.length) {
        $context = $(this).closest(".form-row").parent();
      }
      let country = $context.find(".country_to_state").val();
      let $cityField = $context.find("#billing_city");
      let cityCode = $cityField.val();
      let $wardField = $context.find("#billing_address_2");
      let $wardFieldWrapper = $wardField.closest(".form-row");
      let wardName = $wardField.attr("name");
      let wardId = $wardField.attr('id');
      let wardValue = $wardField.val();
      let wardDataValue = $wardField.attr('data-value');
      let wardPlaceholder = $wardField.attr('placeholder') || $wardField.attr('data-placeholder') || '';
      let newWardField;

      if (!wardValue) {
        wardValue = wardDataValue;
      }

      if (country == 'VN') {
        let wardPlaceholderOption = $("<option value=\"\"></option>").text(vncheckout_array.placeholder_ward_text);
        if (!wardPlaceholder) {
          wardPlaceholder = vncheckout_array.placeholder_ward_text;
        }
        if ($wardField.is("input")) {
          newWardField = $("<select></select>").prop('id', wardId).prop("name", wardName).data("placeholder", wardPlaceholder).addClass("state_select");
          $wardField.replaceWith(newWardField);
          $wardField = $context.find("#billing_address_2");
        }
        $wardField.attr("data-value", wardValue);
        $wardField.empty().append(wardPlaceholderOption);
        wardCache = addressData.getWard(cityCode);
        if (wardCache) {
          $.each(wardCache, function (index, ward) {
            let wardOption = $('<option></option>').prop("value", ward.xaid).text(ward.name);
            $wardField.append(wardOption);
          });
          $wardField.val(wardValue).trigger('change');
        } else if (cityCode) {
          if (wardAjax) {
            wardAjax.abort();
          }
          wardAjax = $.ajax({
            type: "post",
            dataType: 'json',
            url: vncheckout_array.get_address,
            data: {
              action: 'load_diagioihanhchinh',
              maqh: cityCode
            },
            context: this,
            beforeSend: function () {
              $wardFieldWrapper.addClass("ntco_loading");
            },
            success: function (response) {
              if (response.success) {
                let wards = response.data;
                addressData.addWard(cityCode, wards);
                $.each(wards, function (index, ward) {
                  let wardOption = $('<option></option>').prop("value", ward.xaid).text(ward.name);
                  $wardField.append(wardOption);
                });
                $wardField.val(wardValue).trigger('change');
              }
              $wardFieldWrapper.removeClass('ntco_loading');
              wardAjax = null;
            },
            error: function () {
              wardAjax = null;
              $wardFieldWrapper.removeClass("ntco_loading");
            }
          });
        } else {
          wardAjax = null;
        }
        $(document.body).trigger('city_to_ward_changed', [country, cityCode, wardValue, $context]);
      } else if ($wardField.is("select, input[type=\"hidden\"]")) {
        newWardField = $("<input type=\"text\" />").prop('id', wardId).prop('name', wardName).prop("placeholder", wardPlaceholder).prop("value", wardValue).addClass('input-text');
        $wardFieldWrapper.show().find('.select2-container').remove();
        newWardField.val(wardValue);
        $wardField.replaceWith(newWardField);
        $(document.body).trigger("city_to_ward_changed", [country, cityCode, wardValue, $context]);
      }
      $(document.body).trigger("city_to_ward_changing", [country, cityCode, wardValue, $context]);
    });

    $(document.body).on("change refresh", "#shipping_city", function () {
      let $context = $(this).closest(".woocommerce-billing-fields,.woocommerce-shipping-fields,.woocommerce-address-fields,.woocommerce-shipping-calculator");
      if (!$context.length) {
        $context = $(this).closest('.form-row').parent();
      }
      let country = $context.find(".country_to_state").val();
      let $cityField = $context.find("#shipping_city");
      let cityCode = $cityField.val();
      let $wardField = $context.find("#shipping_address_2");
      let $wardFieldWrapper = $wardField.closest(".form-row");
      let wardName = $wardField.attr('name');
      let wardId = $wardField.attr('id');
      let wardValue = $wardField.val();
      let wardDataValue = $wardField.attr('data-value');
      let wardPlaceholder = $wardField.attr("placeholder") || $wardField.attr("data-placeholder") || '';
      let newWardField;

      if (!wardValue) {
        wardValue = wardDataValue;
      }

      if (country == 'VN') {
        let wardPlaceholderOption = $("<option value=\"\"></option>").text(vncheckout_array.placeholder_ward_text);
        if (!wardPlaceholder) {
          wardPlaceholder = vncheckout_array.placeholder_ward_text;
        }
        if ($wardField.is("input")) {
          newWardField = $('<select></select>').prop('id', wardId).prop("name", wardName).data("placeholder", wardPlaceholder).addClass("state_select");
          $wardField.replaceWith(newWardField);
          $wardField = $context.find("#shipping_address_2");
        }
        $wardField.attr("data-value", wardValue);
        $wardField.empty().append(wardPlaceholderOption);
        wardCache = addressData.getWard(cityCode);
        if (wardCache) {
          $.each(wardCache, function (index, ward) {
            let wardOption = $("<option></option>").prop("value", ward.xaid).text(ward.name);
            $wardField.append(wardOption);
          });
          $wardField.val(wardValue).trigger('change');
        } else if (cityCode) {
          if (shippingWardAjax) {
            shippingWardAjax.abort();
          }
          shippingWardAjax = $.ajax({
            type: "post",
            dataType: "json",
            url: vncheckout_array.get_address,
            data: {
              action: "load_diagioihanhchinh",
              maqh: cityCode
            },
            context: this,
            beforeSend: function () {
              $wardFieldWrapper.addClass("ntco_loading");
            },
            success: function (response) {
              if (response.success) {
                let wards = response.data;
                addressData.addWard(cityCode, wards);
                $.each(wards, function (index, ward) {
                  let wardOption = $("<option></option>").prop("value", ward.xaid).text(ward.name);
                  $wardField.append(wardOption);
                });
                $wardField.val(wardValue).trigger("change");
              }
              $wardFieldWrapper.removeClass("ntco_loading");
              shippingWardAjax = null;
            },
            error: function () {
              shippingWardAjax = null;
              $wardFieldWrapper.removeClass("ntco_loading");
            }
          });
        } else {
          shippingWardAjax = null;
        }
        $(document.body).trigger("city_to_ward_shipping_changed", [country, cityCode, wardValue, $context]);
      } else if ($wardField.is("select, input[type=\"hidden\"]")) {
        newWardField = $("<input type=\"text\" />").prop('id', wardId).prop("name", wardName).prop('placeholder', wardPlaceholder).prop("value", wardValue).addClass("input-text");
        $wardFieldWrapper.show().find(".select2-container").remove();
        newWardField.val(wardValue);
        $wardField.replaceWith(newWardField);
        $(document.body).trigger('city_to_ward_shipping_changed', [country, cityCode, wardValue, $context]);
      }
      $(document.body).trigger("city_to_ward_shipping_changing", [country, cityCode, wardValue, $context]);
    });

    $("#ntco_ghtk_tracking").on("submit", function () {
      var formData = $(this).serialize();
      var $trackingForm = $(this).closest(".ntco_ghtk_tracking_form");
      $.ajax({
        type: "post",
        dataType: 'json',
        url: vncheckout_array.admin_ajax,
        data: {
          action: 'ghtk_tracking',
          data: formData
        },
        context: this,
        beforeSend: function () {
          $trackingForm.addClass('ntco_loading');
        },
        success: function (response) {
          var data = response.data;
          if (data) {
            $.each(data.fragments, function (selector, html) {
              $(selector, $trackingForm).html(html);
            });
          }
          $trackingForm.removeClass("ntco_loading");
        },
        error: function () {
          $trackingForm.removeClass("ntco_loading");
        }
      });
      return false;
    });

    $('body').on("change", 'input[name=payment_method]', function () {
      if (vncheckout_array.enabled_free_shipping) {
        $("form.checkout").trigger("update_checkout");
      }
    });

    if (typeof magnificPopup !== "undefined" && $(".get_address_byphone").length > 0) {
      $('.get_address_byphone').magnificPopup({
        type: "inline",
        midClick: true,
        showCloseBtn: false
      });

      var isProcessing = false;

      $("body").on('click', ".btn_get_address", function () {
        if (isProcessing) {
          return false;
        }
        var $button = $(this);
        var $form = $(this).closest("#get_address_content");
        var phone = $('#sdt_get_address', $form).val();
        var $message = $(".get_address_content_mess", $form);
        var recaptchaResponse = '';

        if (!phone || !/^0+(\d{9,10})$/.test(phone)) {
          $message.html(vncheckout_array.phone_error);
          return false;
        } else {
          $message.html('');
          if ($('#g-recaptcha-response', $form).length > 0) {
            recaptchaResponse = $("#g-recaptcha-response", $form).val();
            if (!recaptchaResponse) {
              $message.html("Vui lòng nhập mã xác thực.");
              return false;
            }
          }
          $.ajax({
            type: "post",
            dataType: "json",
            url: vncheckout_array.admin_ajax,
            data: {
              action: "get_address_byphone",
              phone: phone,
              'g-recaptcha-response': recaptchaResponse
            },
            context: this,
            beforeSend: function () {
              $message.html(vncheckout_array.loading_text);
              $button.addClass("ntco_loading");
              isProcessing = true;
            },
            success: function (response) {
              if (response.success) {
                $.each(response.data.billing, function (fieldId, value) {
                  if ($('#' + fieldId).is("select")) {
                    $('#' + fieldId).val(value).attr("data-value", value);
                  } else {
                    $('#' + fieldId).val(value).attr('value', value);
                  }
                });
                $.each(response.data.shipping, function (fieldId, value) {
                  if ($('#' + fieldId).is("select")) {
                    $('#' + fieldId).val(value).attr("data-value", value);
                  } else {
                    $('#' + fieldId).val(value).attr('value', value);
                  }
                });
                $message.html('');
                $("#billing_state, #shipping_state, #billing_city, #shipping_city").trigger('change');
                $.magnificPopup.close();
              } else {
                $message.html(vncheckout_array.loadaddress_error);
              }
              $button.removeClass('ntco_loading');
              isProcessing = false;
            },
            error: function () {
              $button.removeClass("ntco_loading");
              alert("An error occurred");
              isProcessing = false;
            }
          });
        }
        return false;
      });

      $('body').on("click", '.btn_cancel', function () {
        var $form = $(this).closest('#get_address_content');
        var $message = $(".get_address_content_mess", $form);
        $form.removeClass("get_address_error");
        $.magnificPopup.close();
        isProcessing = false;
        $message.html('');
        if ($("#billing_first_name").length > 0) {
          $("#billing_first_name").focus();
        } else {
          $('#billing_last_name').focus();
        }
        return false;
      });
    }

    $("body").on('change', "input[name=payment_method]", function () {
      if (vncheckout_array.has_vtp) {
        $("form.checkout").trigger("update_checkout");
      }
    });
  });
})(jQuery);
