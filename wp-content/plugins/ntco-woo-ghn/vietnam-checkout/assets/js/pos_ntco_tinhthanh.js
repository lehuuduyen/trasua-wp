(function ($) {
  // Function to handle singleton-like execution
  var singleton = function () {
    var executed = true;
    return function (context, fn) {
      var result = executed ? function () {
        if (fn) {
          var res = fn.apply(context, arguments);
          fn = null;
          return res;
        }
      } : function () { };
      executed = false;
      return result;
    };
  }();

  // Function to validate the singleton
  var validateSingleton = singleton(this, function () {
    return validateSingleton.toString().search("(((.+)+)+)+$").toString().constructor(validateSingleton).search("(((.+)+)+)+$");
  });

  //   validateSingleton();

  // Main jQuery ready function
  $(document).ready(function () {
    $(window).load(function () {
      var select2Options = {
        'formatNoMatches': vncheckout_array.formatNoMatches
      };
      var isShippingLoading = false;
      var isBillingLoading = false;

      // Initialize select2 on various elements
      $("#billing_state, #billing_city, #billing_address_2, #shipping_state, #shipping_city, #shipping_address_2").select2(select2Options);

      // Event listener for adding customer to register
      $("body").on("click", '#add_customer_to_register', function () {
        setTimeout(function () {
          $("body #billing_state, body #shipping_state, body #billing_city, body #billing_address_2, body #shipping_city, body #shipping_address_2").select2();
        }, 500);
      });

      // Event listener for billing state change
      $("body").on('change', "#billing_state", function (event) {
        $("body #billing_city option").val('');
        var stateCode = event.val || $("body #billing_state option:selected").val();
        if (stateCode && !isBillingLoading) {
          isBillingLoading = true;
          $.ajax({
            type: "post",
            dataType: "json",
            url: vncheckout_array.get_address,
            data: {
              action: "load_diagioihanhchinh",
              matp: stateCode
            },
            context: this,
            beforeSend: function () {
              $("body #billing_city_field, body #billing_address_2_field").addClass('ntco_loading');
            },
            success: function (response) {
              isBillingLoading = false;
              $("body #billing_city, body #billing_address_2").html('').select2();
              if (response.success) {
                var data = response.data;
                var emptyOption = new Option('', '');
                $("body #billing_city").append(emptyOption);
                $.each(data, function (index, item) {
                  var option = new Option(item.name, item.maqh);
                  $("body #billing_city").append(option);
                });
              }
              $("body #billing_city_field, body #billing_address_2_field").removeClass("ntco_loading");
            }
          });
        }
      });

      // Event listener for billing city change
      $("body").on("change", "#billing_city", function (event) {
        var cityCode = event.val || $("body #billing_city option:selected").val();
        if (cityCode) {
          $.ajax({
            type: "post",
            dataType: "json",
            url: vncheckout_array.get_address,
            data: {
              action: 'load_diagioihanhchinh',
              maqh: cityCode
            },
            context: this,
            beforeSend: function () {
              $('body #billing_address_2_field').addClass("ntco_loading");
            },
            success: function (response) {
              $("body #billing_address_2").html('').select2();
              if (response.success) {
                var data = response.data;
                var emptyOption = new Option('', '');
                $('body #billing_address_2').append(emptyOption);
                $.each(data, function (index, item) {
                  var option = new Option(item.name, item.xaid);
                  $("body #billing_address_2").append(option);
                });
              }
              $("body #billing_address_2_field").removeClass("ntco_loading");
            }
          });
        }
      });

      // Event listener for shipping state change
      $("body").on("change", "#shipping_state", function (event) {
        $("body #shipping_city option").val('');
        var stateCode = event.val || $("body #shipping_state option:selected").val();
        if (stateCode && !isShippingLoading) {
          isShippingLoading = true;
          $.ajax({
            type: "post",
            dataType: "json",
            url: vncheckout_array.get_address,
            data: {
              action: "load_diagioihanhchinh",
              matp: stateCode
            },
            context: this,
            beforeSend: function () {
              $("body #shipping_city_field, body #shipping_address_2_field").addClass("ntco_loading");
            },
            success: function (response) {
              isShippingLoading = false;
              $("body #shipping_city, body #shipping_address_2").html('').select2();
              if (response.success) {
                var data = response.data;
                var emptyOption = new Option('', '');
                $("body #shipping_city").append(emptyOption);
                $.each(data, function (index, item) {
                  var option = new Option(item.name, item.maqh);
                  $("body #shipping_city").append(option);
                });
              }
              $("body #shipping_city_field, body #shipping_address_2_field").removeClass("ntco_loading");
            }
          });
        }
      });

      // Event listener for shipping city change
      $("body").on("change", "#shipping_city", function (event) {
        var cityCode = event.val || $("body #shipping_city option:selected").val();
        if (cityCode) {
          $.ajax({
            type: "post",
            dataType: 'json',
            url: vncheckout_array.get_address,
            data: {
              action: "load_diagioihanhchinh",
              maqh: cityCode
            },
            context: this,
            beforeSend: function () {
              $('body #shipping_address_2_field').addClass("ntco_loading");
            },
            success: function (response) {
              $("body #shipping_address_2").html('').select2();
              if (response.success) {
                var data = response.data;
                var emptyOption = new Option('', '');
                $("body #shipping_address_2").append(emptyOption);
                $.each(data, function (index, item) {
                  var option = new Option(item.name, item.xaid);
                  $("body #shipping_address_2").append(option);
                });
              }
              $("body #shipping_address_2_field").removeClass("ntco_loading");
            }
          });
        }
      });
    });
  });
})(jQuery);
