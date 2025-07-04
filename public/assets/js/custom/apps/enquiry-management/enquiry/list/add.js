"use strict";
var KTUsersAddEnquiry = (function () {
  const t = document.getElementById("kt_modal_add_enquiry"),
    e = t.querySelector("#kt_modal_add_enquiry_form"),
    n = new bootstrap.Modal(t);
  return {
    init: function () {
      (() => {
        var o = FormValidation.formValidation(e, {
          fields: {
            type: {
              validators: { notEmpty: { message: "Enquiry type is required" } },
            },
            name: {
              validators: { notEmpty: { message: "Full name is required" } },
            },
            mobile: {
              validators: { notEmpty: { message: "Mobile is required" } },
            }
          },
          plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap: new FormValidation.plugins.Bootstrap5({
              rowSelector: ".fv-row",
              eleInvalidClass: "",
              eleValidClass: "",
            }),
          },
        });
        const i = t.querySelector('[data-kt-enquiry-modal-action="submit"]');
        i.addEventListener("click", (t) => {
          t.preventDefault(),
            o &&
              o.validate().then(function (t) {
                    if("Valid" == t){
                        $('.indicator-label').css({'display' : 'none'});
                        $('.indicator-progress').css({'display' : 'inline-block'});
                        $(i).prop('disabled', true);
                        var formData = new FormData($("#kt_modal_add_enquiry_form")[0]);
                        $.ajax({
                             headers: {'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')},
                    	  url: baseurl + '/esh2/enquiry/add',
                    	  method : 'post',
                    	  //dataType: 'json',
                            data: formData,
                            processData: false,
                            contentType: false, 
                    	  success: function(response){
            	            $(i).prop('disabled', false);
            	            $('.indicator-label').css({'display' : 'inline-block'});
                            $('.indicator-progress').css({'display' : 'none'});
                              response.status == 1
                                ? (i.setAttribute("data-kt-indicator", "on"),
                                  (i.disabled = !0),
                                  setTimeout(function () {
                                    
                                    i.removeAttribute("data-kt-indicator"),
                                      (i.disabled = !1),
                                      Swal.fire({
                                        text: "Form has been successfully submitted!",
                                        icon: "success",
                                        buttonsStyling: !1,
                                        confirmButtonText: "Ok, got it!",
                                        customClass: { confirmButton: "btn btn-primary" },
                                      }).then(function (t) {
                                          //window.location.reload();
                                        t.isConfirmed && n.hide();
                                      });
                                  }, 2e1))
                                : Swal.fire({
                                    text: "Sorry, looks like there are some errors detected, please try again. \n" + response.message,
                                    icon: "error",
                                    buttonsStyling: !1,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: { confirmButton: "btn btn-primary" },
                                  });
                	        }
                	    });
                    }
              });
        });
      })();
    },
  };
})();
KTUtil.onDOMContentLoaded(function () {
  KTUsersAddEnquiry.init();
});
