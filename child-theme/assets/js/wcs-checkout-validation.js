(function ($) {
  "use strict";

  if (typeof $ === "undefined") {
    return;
  }

  function toggleFloatingState(field) {
    if (!field) {
      return;
    }
    var hasValue = (field.value || "").trim().length > 0;
    field.classList.toggle("wcs-has-value", hasValue);
  }

  function validateField(field) {
    if (!field) {
      return true;
    }
    var wrapper = field.closest(".form-row");
    if (!wrapper) {
      return true;
    }

    var required = wrapper.classList.contains("validate-required");
    var valid = !required || (field.value || "").trim().length > 0;
    wrapper.classList.toggle("wcs-field-invalid", !valid);

    var error = wrapper.querySelector(".wcs-field-error");
    if (!valid) {
      if (!error) {
        error = document.createElement("span");
        error.className = "wcs-field-error";
        error.textContent = "Bu alan zorunludur.";
        wrapper.appendChild(error);
      }
    } else if (error) {
      error.remove();
    }
    return valid;
  }

  function init() {
    var fields = document.querySelectorAll(".wcs-co-wrap input.input-text, .wcs-co-wrap textarea, .wcs-co-wrap select");
    fields.forEach(function (field) {
      toggleFloatingState(field);
      field.addEventListener("input", function () {
        toggleFloatingState(field);
        validateField(field);
      });
      field.addEventListener("blur", function () {
        toggleFloatingState(field);
        validateField(field);
      });
    });

    var modeInputs = document.querySelectorAll('input[name="wcs_checkout_mode"]');
    if (modeInputs.length) {
      var createAccount = document.getElementById("createaccount");
      var loginNotice = document.querySelector(".wcs-co-login-notice");

      function syncModeUI() {
        var selected = document.querySelector('input[name="wcs_checkout_mode"]:checked');
        var mode = selected ? selected.value : "guest";

        modeInputs.forEach(function (input) {
          var option = input.closest(".wcs-co-mode__option");
          if (option) {
            option.classList.toggle("is-active", input.checked);
          }
        });

        if (createAccount) {
          createAccount.checked = mode === "register";
          $(createAccount).trigger("change");
        }

        if (loginNotice) {
          loginNotice.style.display = mode === "register" ? "none" : "";
        }
      }

      modeInputs.forEach(function (input) {
        input.addEventListener("change", syncModeUI);
      });
      syncModeUI();
    }
  }

  $(document.body).on("updated_checkout", init);
  document.addEventListener("DOMContentLoaded", init);
})(window.jQuery);
