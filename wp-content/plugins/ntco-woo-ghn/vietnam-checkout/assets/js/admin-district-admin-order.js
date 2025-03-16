jQuery(function ($) {
  let addressDataLoaded = false;
  let cityDataCache = {};
  let wardDataCache = {};

  const addressStorageHandler = function () {
    let addressStorage = {};
    const cityKey = "cities_" + admin_vncheckout_array.vnaddress_db_version;
    localStorage.setItem(cityKey, localStorage.getItem(cityKey) ? localStorage.getItem(cityKey) : {});

    const saveCityData = function (data) {
      localStorage.setItem(cityKey, JSON.stringify(data));
    };

    const loadCityData = function () {
      try {
        const data = JSON.parse(localStorage.getItem(cityKey));
        return data ? data : {};
      } catch (error) {
        return {};
      }
    };

    const addCityData = function (key, value) {
      const data = loadCityData();
      data[key] = value;
      saveCityData(data);
      return true;
    };

    const getCityData = function (key) {
      const data = loadCityData();
      if (data && !$.isEmptyObject(data[key]) && data[key]) {
        return data[key];
      }
      return false;
    };

    const wardKey = "ward_" + admin_vncheckout_array.vnaddress_db_version;
    localStorage.setItem(wardKey, localStorage.getItem(wardKey) ? localStorage.getItem(wardKey) : {});

    const saveWardData = function (data) {
      localStorage.setItem(wardKey, JSON.stringify(data));
    };

    const loadWardData = function () {
      try {
        const data = JSON.parse(localStorage.getItem(wardKey));
        return data ? data : {};
      } catch (error) {
        return {};
      }
    };

    const addWardData = function (key, value) {
      const data = loadWardData();
      data[key] = value;
      saveWardData(data);
      return true;
    };

    const getWardData = function (key) {
      const data = loadWardData();
      if (data && !$.isEmptyObject(data[key]) && data[key]) {
        return data[key];
      }
      return false;
    };

    addressStorage.getAllCity = loadCityData;
    addressStorage.addCity = addCityData;
    addressStorage.getCity = getCityData;
    addressStorage.getAllWard = loadWardData;
    addressStorage.addWard = addWardData;
    addressStorage.getWard = getWardData;

    return addressStorage;
  }();

  let billingCityRequest = null;
  let shippingCityRequest = null;
  let billingWardRequest = null;
  let shippingWardRequest = null;

  const addressFormHandler = {
    init: function () {
      $(".js_field-country").on("change", function () {
        const parentFieldset = $(this).closest(".fieldset-billing,.fieldset-shipping,.edit_address");
        const container = parentFieldset.length ? parentFieldset : $(this).closest(".form-table");
        container.find(".js_field-state, .js_field-city, [name=\"woocommerce_default_country\"]").trigger("change");
      });

      $("a.edit_address").on("click", function () {
        const parentContainer = $(this).closest(".order_data_column");
        const container = parentContainer.length ? parentContainer : $(this).closest(".form-table");
        container.find(".js_field-state, .js_field-city, [name=\"woocommerce_default_country\"]").trigger("change");
      });

      $(document.body).on('change refresh', "#billing_state, #_billing_state, [name=\"woocommerce_default_country\"]", this.changeBillingState);
      $(document.body).on("change refresh", "#shipping_state, #_shipping_state", this.changeShippingState);
      $(document.body).on("change refresh", "#billing_city, #_billing_city, #woocommerce_store_city", this.changeBillingCity);
      $(document.body).on("change refresh", "#shipping_city, #_shipping_city", this.changeShippingCity);
      $(document.body).on("state_to_city_changed", function (event, country, city, container) {
        container.find(".js_field-city").trigger("change");
      });

      if ($('#woocommerce_store_city').length > 0) {
        $("[name=\"woocommerce_default_country\"]").trigger('change');
      }
    },
    changeBillingState: function () {
      const container = $(this).closest(".fieldset-billing,.fieldset-shipping,.edit_address").length ?
        $(this).closest(".fieldset-billing,.fieldset-shipping,.edit_address") : $(this).closest(".form-table");

      const stateField = $(this);
      let stateValue = stateField.val();
      let cityField = container.find('#billing_city, #_billing_city, #woocommerce_store_city');
      const cityFieldContainer = cityField.closest("tr, .form-field");
      const countryField = stateField.parents(".form-table, .edit_address").find(":input.js_field-country");
      let countryValue = countryField.val();
      const cityName = cityField.attr("name");
      const cityId = cityField.attr('id');
      let cityValue = cityField.val();
      let cityPlaceholder = cityField.attr('placeholder') || cityField.attr("data-placeholder") || '';
      let cityInputField;

      if (!cityValue) {
        cityValue = cityField.attr("data-value");
      }

      if (typeof countryValue == "undefined" && $("#woocommerce_store_city").length > 0) {
        if (stateValue) {
          const stateParts = stateValue.split(':');
          countryValue = stateParts[0];
          stateValue = stateParts[1];
        }
      }

      if (countryValue == 'VN') {
        const defaultOption = $("<option value=\"\"></option>").text(admin_vncheckout_array.placeholder_city_text);
        if (!cityPlaceholder) {
          cityPlaceholder = admin_vncheckout_array.placeholder_city_text;
        }

        if (cityField.is("input")) {
          cityInputField = $("<select style=\"width: 25em;\"></select>").prop('id', cityId).prop("name", cityName).data("placeholder", cityPlaceholder).addClass("js_field-city");
          cityField.replaceWith(cityInputField);
          cityField = container.find("#billing_city, #_billing_city, #woocommerce_store_city");
        }

        cityField.attr("data-value", cityValue);
        cityField.empty().append(defaultOption);

        if (addressDataLoaded) {
          cityDataCache = addressDataLoaded[stateValue] ? addressDataLoaded[stateValue] : '';
          if (!cityDataCache) {
            cityDataCache = addressStorageHandler.getCity(stateValue);
          }
        } else {
          cityDataCache = addressStorageHandler.getCity(stateValue);
        }

        if (cityDataCache) {
          $.each(cityDataCache, function (index, city) {
            const cityOption = $("<option></option>").prop("value", city.maqh).text(city.name);
            cityField.append(cityOption);
          });
          cityField.val(cityValue);
          cityField.show().selectWoo().hide().trigger('change');
          $(document.body).trigger("state_to_city_changed", [countryValue, cityValue, container]);
        } else if (stateValue) {
          if (billingCityRequest) {
            billingCityRequest.abort();
          }
          billingCityRequest = $.ajax({
            type: "post",
            dataType: 'json',
            url: admin_vncheckout_array.get_address,
            data: { action: 'load_diagioihanhchinh', matp: stateValue },
            context: this,
            beforeSend: function () {
              cityFieldContainer.addClass("ntco_loading");
            },
            success: function (response) {
              if (response.success) {
                const cities = response.data;
                addressStorageHandler.addCity(stateValue, cities);
                $.each(cities, function (index, city) {
                  const cityOption = $("<option></option>").prop("value", city.maqh).text(city.name);
                  cityField.append(cityOption);
                });
                cityField.val(cityValue);
              }
              cityFieldContainer.removeClass('ntco_loading');
              billingCityRequest = null;
              cityField.show().selectWoo().hide().trigger("change");
              $(document.body).trigger("state_to_city_changed", [countryValue, cityValue, container]);
            },
            error: function () {
              billingCityRequest = null;
              cityFieldContainer.removeClass("ntco_loading");
            }
          });
        } else {
          billingCityRequest = null;
        }
      } else if (cityField.is("select, input[type=\"hidden\"]")) {
        cityInputField = $("<input type=\"text\" />").prop('id', cityId).prop("name", cityName).prop("placeholder", cityPlaceholder).addClass("regular-text js_field-city");
        cityInputField.val(cityValue).attr("value", cityValue);
        cityFieldContainer.show().find(".select2-container").remove();
        cityField.replaceWith(cityInputField);
        cityField.show().trigger("change");
        $(document.body).trigger("state_to_city_changed", [countryValue, cityValue, container]);
      }
    },
    changeShippingState: function () {
      const container = $(this).closest(".fieldset-billing,.fieldset-shipping,.edit_address").length ?
        $(this).closest(".fieldset-billing,.fieldset-shipping,.edit_address") : $(this).closest(".form-table");

      const stateField = $(this);
      const stateValue = stateField.val();
      let cityField = container.find("#shipping_city, #_shipping_city");
      const cityFieldContainer = cityField.closest("tr, .form-field");
      const countryField = stateField.parents(".form-table, .edit_address").find(":input.js_field-country");
      let countryValue = countryField.val();
      const cityName = cityField.attr("name");
      const cityId = cityField.attr('id');
      let cityValue = cityField.val();
      let cityPlaceholder = cityField.attr("placeholder") || cityField.attr('data-placeholder') || '';
      let cityInputField;

      if (!cityValue) {
        cityValue = cityField.attr("data-value");
      }

      if (typeof countryValue == "undefined" && $('#woocommerce_store_city').length > 0) {
        if (stateValue) {
          const stateParts = stateValue.split(':');
          countryValue = stateParts[0];
          stateValue = stateParts[1];
        }
      }

      if (countryValue == 'VN') {
        const defaultOption = $("<option value=\"\"></option>").text(admin_vncheckout_array.placeholder_city_text);
        if (!cityPlaceholder) {
          cityPlaceholder = admin_vncheckout_array.placeholder_city_text;
        }

        if (cityField.is("input")) {
          cityInputField = $("<select style=\"width: 25em;\"></select>").prop('id', cityId).prop("name", cityName).data("placeholder", cityPlaceholder).addClass('js_field-city');
          cityField.replaceWith(cityInputField);
          cityField = container.find('#shipping_city, #_shipping_city');
        }

        cityField.attr("data-value", cityValue);
        cityField.empty().append(defaultOption);

        if (addressDataLoaded) {
          cityDataCache = addressDataLoaded[stateValue] ? addressDataLoaded[stateValue] : '';
          if (!cityDataCache) {
            cityDataCache = addressStorageHandler.getCity(stateValue);
          }
        } else {
          cityDataCache = addressStorageHandler.getCity(stateValue);
        }

        if (cityDataCache) {
          $.each(cityDataCache, function (index, city) {
            const cityOption = $("<option></option>").prop("value", city.maqh).text(city.name);
            cityField.append(cityOption);
          });
          cityField.val(cityValue);
          cityField.show().selectWoo().hide().trigger('change');
          $(document.body).trigger("state_to_city_shipping_changed", [countryValue, cityValue, container]);
        } else if (stateValue) {
          if (shippingCityRequest) {
            shippingCityRequest.abort();
          }
          shippingCityRequest = $.ajax({
            type: 'post',
            dataType: "json",
            url: admin_vncheckout_array.get_address,
            data: { action: "load_diagioihanhchinh", matp: stateValue },
            context: this,
            beforeSend: function () {
              cityFieldContainer.addClass("ntco_loading");
            },
            success: function (response) {
              if (response.success) {
                const cities = response.data;
                addressStorageHandler.addCity(stateValue, cities);
                $.each(cities, function (index, city) {
                  const cityOption = $("<option></option>").prop("value", city.maqh).text(city.name);
                  cityField.append(cityOption);
                });
                cityField.val(cityValue);
              }
              cityFieldContainer.removeClass("ntco_loading");
              shippingCityRequest = null;
              cityField.show().selectWoo().hide().trigger("change");
              $(document.body).trigger("state_to_city_shipping_changed", [countryValue, cityValue, container]);
            },
            error: function () {
              shippingCityRequest = null;
              cityFieldContainer.removeClass("ntco_loading");
            }
          });
        } else {
          shippingCityRequest = null;
        }
      } else if (cityField.is("select, input[type=\"hidden\"]")) {
        cityInputField = $("<input type=\"text\" />").prop('id', cityId).prop("name", cityName).prop('placeholder', cityPlaceholder).addClass("regular-text js_field-city");
        cityInputField.val(cityValue).attr('value', cityValue);
        cityFieldContainer.show().find('.select2-container').remove();
        cityField.replaceWith(cityInputField);
        cityField.show().trigger("change");
        $(document.body).trigger('state_to_city_shipping_changed', [countryValue, cityValue, container]);
      }
    },
    changeBillingCity: function () {
      const container = $(this).closest(".fieldset-billing,.fieldset-shipping,.edit_address").length ?
        $(this).closest(".fieldset-billing,.fieldset-shipping,.edit_address") : $(this).closest(".form-table");

      const cityField = $(this);
      const cityValue = cityField.val();
      let wardField = container.find("#billing_address_2, #_billing_address_2, #woocommerce_store_address_2");
      const wardFieldContainer = wardField.closest('tr, .form-field');
      let countryField = cityField.parents('.form-table, .edit_address').find(':input.js_field-country');
      let countryValue = countryField.val();
      const wardName = wardField.attr('name');
      const wardId = wardField.attr('id');
      let wardValue = wardField.val();
      let wardPlaceholder = wardField.attr("placeholder") || wardField.attr("data-placeholder") || '';
      const stateField = container.find("#_billing_state");
      const stateValue = stateField.val();
      let wardInputField;

      if (!wardValue) {
        wardValue = wardField.attr("data-value");
      }

      if (typeof countryValue == 'undefined') {
        countryField = cityField.parents(".form-table").find("[name=\"woocommerce_default_country\"]");
        countryValue = countryField.val();
        if (countryValue) {
          countryValue = countryValue.split(':')[0];
        }
      }

      if (countryValue == 'VN') {
        const defaultOption = $("<option value=\"\"></option>").text(admin_vncheckout_array.placeholder_ward_text);
        if (!wardPlaceholder) {
          wardPlaceholder = admin_vncheckout_array.placeholder_ward_text;
        }

        if (wardField.is("input")) {
          wardInputField = $("<select style=\"width: 25em;\"></select>").prop('id', wardId).prop("name", wardName).data("placeholder", wardPlaceholder);
          wardField.replaceWith(wardInputField);
          wardField = container.find("#billing_address_2, #_billing_address_2, #woocommerce_store_address_2");
        }

        wardField.attr("data-value", wardValue);
        wardField.empty().append(defaultOption);

        if (addressDataLoaded && addressDataLoaded[stateValue] && addressDataLoaded[stateValue][cityValue]) {
          wardDataCache = addressDataLoaded[stateValue][cityValue].wards || '';
          if (!wardDataCache) {
            wardDataCache = addressStorageHandler.getWard(cityValue);
          }
        } else {
          wardDataCache = addressStorageHandler.getWard(cityValue);
        }

        if (wardDataCache) {
          $.each(wardDataCache, function (index, ward) {
            const wardOption = $('<option></option>').prop("value", ward.xaid).text(ward.name);
            wardField.append(wardOption);
          });
          wardField.val(wardValue).trigger("change");
          wardField.show().selectWoo().hide().trigger('change');
          $(document.body).trigger("city_to_ward_changed", [countryValue, wardValue, container]);
        } else if (cityValue) {
          if (billingWardRequest) {
            billingWardRequest.abort();
          }
          billingWardRequest = $.ajax({
            type: 'post',
            dataType: 'json',
            url: admin_vncheckout_array.get_address,
            data: { action: "load_diagioihanhchinh", maqh: cityValue },
            context: this,
            beforeSend: function () {
              wardFieldContainer.addClass("ntco_loading");
            },
            success: function (response) {
              if (response.success) {
                const wards = response.data;
                addressStorageHandler.addWard(cityValue, wards);
                $.each(wards, function (index, ward) {
                  const wardOption = $("<option></option>").prop("value", ward.xaid).text(ward.name);
                  wardField.append(wardOption);
                });
                wardField.val(wardValue).trigger('change');
              }
              wardFieldContainer.removeClass("ntco_loading");
              billingWardRequest = null;
              wardField.show().selectWoo().hide().trigger("change");
              $(document.body).trigger("city_to_ward_changed", [countryValue, wardValue, container]);
            },
            error: function () {
              billingWardRequest = null;
              wardFieldContainer.removeClass("ntco_loading");
            }
          });
        } else {
          billingWardRequest = null;
        }
      } else if (wardField.is("select, input[type=\"hidden\"]")) {
        wardInputField = $("<input type=\"text\" />").prop('id', wardId).prop("name", wardName).prop("placeholder", wardPlaceholder).addClass('regular-text');
        wardInputField.val(wardValue).attr('value', wardValue);
        wardFieldContainer.show().find(".select2-container").remove();
        wardField.replaceWith(wardInputField);
        wardField.show().trigger("change");
        $(document.body).trigger('city_to_ward_changed', [countryValue, wardValue, container]);
      }
    },
    changeShippingCity: function () {
      const container = $(this).closest(".fieldset-billing,.fieldset-shipping,.edit_address").length ?
        $(this).closest(".fieldset-billing,.fieldset-shipping,.edit_address") : $(this).closest(".form-table");

      const cityField = $(this);
      const cityValue = cityField.val();
      let wardField = container.find("#shipping_address_2, #_shipping_address_2");
      const wardFieldContainer = wardField.closest("tr, .form-field");
      const countryField = cityField.parents(".form-table, .edit_address").find(":input.js_field-country");
      let countryValue = countryField.val();
      const wardName = wardField.attr("name");
      const wardId = wardField.attr("id");
      let wardValue = wardField.val();
      let wardPlaceholder = wardField.attr("placeholder") || wardField.attr("data-placeholder") || '';
      const stateField = container.find("#_shipping_state");
      const stateValue = stateField.val();
      let wardInputField;

      if (!wardValue) {
        wardValue = wardField.attr("data-value");
      }

      if (typeof countryValue == "undefined") {
        countryField = cityField.parents(".form-table").find("[name=\"woocommerce_default_country\"]");
        countryValue = countryField.val();
        if (countryValue) {
          countryValue = countryValue.split(':')[0];
        }
      }

      if (countryValue == 'VN') {
        const defaultOption = $("<option value=\"\"></option>").text(admin_vncheckout_array.placeholder_ward_text);
        if (!wardPlaceholder) {
          wardPlaceholder = admin_vncheckout_array.placeholder_ward_text;
        }

        if (wardField.is('input')) {
          wardInputField = $("<select style=\"width: 25em;\"></select>").prop('id', wardId).prop("name", wardName).data("placeholder", wardPlaceholder);
          wardField.replaceWith(wardInputField);
          wardField = container.find("#shipping_address_2, #_shipping_address_2, #woocommerce_store_address_2");
        }

        wardField.attr("data-value", wardValue);
        wardField.empty().append(defaultOption);

        if (addressDataLoaded && addressDataLoaded[stateValue] && addressDataLoaded[stateValue][cityValue]) {
          wardDataCache = addressDataLoaded[stateValue][cityValue].wards || '';
          if (!wardDataCache) {
            wardDataCache = addressStorageHandler.getWard(cityValue);
          }
        } else {
          wardDataCache = addressStorageHandler.getWard(cityValue);
        }

        if (wardDataCache) {
          $.each(wardDataCache, function (index, ward) {
            const wardOption = $('<option></option>').prop("value", ward.xaid).text(ward.name);
            wardField.append(wardOption);
          });
          wardField.val(wardValue).trigger("change");
          wardField.show().selectWoo().hide().trigger("change");
          $(document.body).trigger('city_to_ward_changed', [countryValue, wardValue, container]);
        } else if (cityValue) {
          if (shippingWardRequest) {
            shippingWardRequest.abort();
          }
          shippingWardRequest = $.ajax({
            type: "post",
            dataType: "json",
            url: admin_vncheckout_array.get_address,
            data: { action: 'load_diagioihanhchinh', maqh: cityValue },
            context: this,
            beforeSend: function () {
              wardFieldContainer.addClass("ntco_loading");
            },
            success: function (response) {
              if (response.success) {
                const wards = response.data;
                addressStorageHandler.addWard(cityValue, wards);
                $.each(wards, function (index, ward) {
                  const wardOption = $("<option></option>").prop("value", ward.xaid).text(ward.name);
                  wardField.append(wardOption);
                });
                wardField.val(wardValue).trigger("change");
              }
              wardFieldContainer.removeClass("ntco_loading");
              shippingWardRequest = null;
              wardField.show().selectWoo().hide().trigger("change");
              $(document.body).trigger("city_to_ward_changed", [countryValue, wardValue, container]);
            },
            error: function () {
              shippingWardRequest = null;
              wardFieldContainer.removeClass("ntco_loading");
            }
          });
        } else {
          shippingWardRequest = null;
        }
      } else if (wardField.is("select, input[type=\"hidden\"]")) {
        wardInputField = $("<input type=\"text\" />").prop('id', wardId).prop('name', wardName).prop("placeholder", wardPlaceholder).addClass('regular-text');
        wardInputField.val(wardValue).attr('value', wardValue);
        wardFieldContainer.show().find(".select2-container").remove();
        wardField.replaceWith(wardInputField);
        wardField.show().trigger("change");
        $(document.body).trigger("city_to_ward_changed", [countryValue, wardValue, container]);
      }
    }
  };

  if (admin_vncheckout_array.json_address_enable && admin_vncheckout_array.json_address) {
    $.getJSON(admin_vncheckout_array.json_address, function (data) {
      for (let key in localStorage) {
        if (key.startsWith('ward_') || key.startsWith('cities_')) {
          localStorage.removeItem(key);
        }
      }
      addressDataLoaded = data;
      addressFormHandler.init();
    }).fail(function () {
      addressFormHandler.init();
    });
  } else {
    addressFormHandler.init();
  }

  $(".button_create_tables").on("click", function () {
    const button = $(this);
    const notice = button.closest(".notice");
    const nonce = button.data("nonce");
    $.ajax({
      type: 'post',
      dataType: "json",
      url: admin_vncheckout_array.ajaxurl,
      data: { action: "vnaddress_create_table", security: nonce },
      context: this,
      beforeSend: function () {
        button.html("Đang cấu hình...");
      },
      success: function (response) {
        button.html("Đã xong");
        if (response.success) {
          notice.remove();
          alert(response.data);
        } else {
          alert(response.data);
        }
      },
      error: function (xhr, status, error) {
        alert(status);
      }
    });
    return false;
  });

  let updatingCountry = false;
  $("body").on("click", ".update_country", function () {
    if (updatingCountry) {
      return false;
    }
    const cell = $(this).closest('td');
    const messageElement = $(".ajax_mess", cell);
    const nonce = $(this).data('nonce');
    $.ajax({
      type: 'post',
      dataType: "json",
      url: admin_vncheckout_array.ajaxurl,
      data: { action: 'ghtk_update_country', nonce: nonce },
      context: this,
      beforeSend: function () {
        messageElement.html("Đang chạy...");
        updatingCountry = true;
      },
      success: function (response) {
        if (response.success) {
          messageElement.css("color", 'green').html(response.data);
        } else {
          messageElement.css('color', 'red').html(response.data);
        }
        updatingCountry = false;
      },
      error: function (xhr, status, error) {
        messageElement.css("color", "red").html("CÃ³ lá»—i xáº£y ra!");
        updatingCountry = false;
      }
    });
    return false;
  });

  if ($('#ntcobillingState').length > 0) {
    $("#ntcobillingState").selectWoo();
    $("#ntcobillingCity").selectWoo();
    $(document.body).on("change refresh", "#ntcobillingState", function (event) {
      const cityField = $("#ntcobillingCity");
      const defaultOption = $("<option value=\"\"></option>").text(admin_vncheckout_array.placeholder_city_text);
      cityField.empty().append(defaultOption);
      let stateValue = event.val;
      if (!stateValue) {
        stateValue = $('#ntcobillingState option:selected').val();
      }
      if (stateValue) {
        cityDataCache = addressStorageHandler.getCity(stateValue);
        if (cityDataCache) {
          $.each(cityDataCache, function (index, city) {
            const cityOption = $("<option></option>").prop("value", city.maqh).text(city.name);
            cityField.append(cityOption);
          });
        } else if (stateValue) {
          if (billingCityRequest) {
            billingCityRequest.abort();
          }
          billingCityRequest = $.ajax({
            type: "post",
            dataType: "json",
            url: admin_vncheckout_array.get_address,
            data: { action: "load_diagioihanhchinh", matp: stateValue },
            context: this,
            success: function (response) {
              if (response.success) {
                const cities = response.data;
                addressStorageHandler.addCity(stateValue, cities);
                $.each(cities, function (index, city) {
                  const cityOption = $("<option></option>").prop("value", city.maqh).text(city.name);
                  cityField.append(cityOption);
                });
              }
              billingCityRequest = null;
            },
            error: function () {
              billingCityRequest = null;
            }
          });
        } else {
          billingCityRequest = null;
        }
      }
    });
  }
});