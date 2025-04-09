/**
 * Happy Business Listing Forms JS
 */

(function($) {
    "use strict";
    
    // Form validation
    $("#hbl-business-registration-form, #hbl-contact-form").on("submit", function(e) {
        var valid = true;
        var firstError = null;
        
        // Remove existing error messages
        $(".form-error").remove();
        
        // Validate required fields
        $(this).find("[required]").each(function() {
            var $field = $(this);
            
            if ($field.val() === "") {
                valid = false;
                var errorMessage = hbl_forms.required;
                
                $field.after("<span class='form-error'>" + errorMessage + "</span>");
                
                if (!firstError) {
                    firstError = $field;
                }
            }
        });
        
        // Validate email fields
        $(this).find("input[type='email']").each(function() {
            var $field = $(this);
            var value = $field.val();
            
            if (value !== "" && !/^[^@]+@[^@]+\.[a-z]{2,}$/i.test(value)) {
                valid = false;
                var errorMessage = hbl_forms.email;
                
                $field.after("<span class='form-error'>" + errorMessage + "</span>");
                
                if (!firstError) {
                    firstError = $field;
                }
            }
        });
        
        // Validate URL fields
        $(this).find("input[type='url']").each(function() {
            var $field = $(this);
            var value = $field.val();
            
            if (value !== "" && !/^https?:\/\/[^\s/$.?#].[^\s]*$/i.test(value)) {
                valid = false;
                var errorMessage = hbl_forms.url;
                
                $field.after("<span class='form-error'>" + errorMessage + "</span>");
                
                if (!firstError) {
                    firstError = $field;
                }
            }
        });
        
        // Validate phone fields
        $(this).find("input[type='tel']").each(function() {
            var $field = $(this);
            var value = $field.val();
            
            if (value !== "" && !/^[0-9+\-() ]{7,}$/.test(value)) {
                valid = false;
                var errorMessage = hbl_forms.phone;
                
                $field.after("<span class='form-error'>" + errorMessage + "</span>");
                
                if (!firstError) {
                    firstError = $field;
                }
            }
        });
        
        // Validate terms checkbox
        if ($(this).find("input[name='terms_agreement']").length && !$(this).find("input[name='terms_agreement']:checked").length) {
            valid = false;
            var $field = $(this).find("input[name='terms_agreement']");
            var errorMessage = hbl_forms.terms;
            
            $field.parent().after("<span class='form-error'>" + errorMessage + "</span>");
            
            if (!firstError) {
                firstError = $field;
            }
        }
        
        // If not valid, prevent form submission and scroll to first error
        if (!valid) {
            e.preventDefault();
            
            if (firstError) {
                $("html, body").animate({
                    scrollTop: firstError.offset().top - 100
                }, 500);
                
                firstError.focus();
            }
        }
    });
    
    // Password strength meter
    $(".wp-pwd").each(function() {
        var $this = $(this);
        var $pass = $this.find("input[name='pass1']");
        var $strengthResult = $("<div class='password-strength-meter-result'></div>");
        
        $this.append($strengthResult);
        
        $pass.on("keyup", function() {
            var strength = wp.passwordStrength.meter(
                $pass.val(),
                [],
                $pass.val()
            );
            
            var strengthText = "";
            var strengthClass = "";
            
            switch (strength) {
                case 0:
                case 1:
                    strengthText = "Very Weak";
                    strengthClass = "very-weak";
                    break;
                case 2:
                    strengthText = "Weak";
                    strengthClass = "weak";
                    break;
                case 3:
                    strengthText = "Medium";
                    strengthClass = "medium";
                    break;
                case 4:
                    strengthText = "Strong";
                    strengthClass = "strong";
                    break;
            }
            
            $strengthResult.attr("class", "password-strength-meter-result " + strengthClass);
            $strengthResult.text(strengthText);
        });
    });
})(jQuery);