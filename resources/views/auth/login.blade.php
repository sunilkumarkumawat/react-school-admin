
<!DOCTYPE html>

<html lang="en" >
@php
$setting = DB::table('settings')->whereNull('deleted_at')->first();
@endphp
    <!--begin::Head-->
    <head>
    <title>DreamSakha | Login</title> 
        <meta charset="utf-8"/>
        <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content=""/>
        <meta name="keywords" content=""/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>      
        <meta property="og:locale" content="en_US" />
        <meta property="og:type" content="article" />
        <meta property="og:title" content="" />
        <meta property="og:url" content=""/>
        <meta property="og:site_name" content="" />
        <link rel="canonical" href=""/>
        <link rel="shortcut icon" href="{{ env('IMAGE_SHOW_PATH') . $setting->mini_logo }}" /><link rel="icon" href="{{ env('IMAGE_SHOW_PATH') . 'default/mini_logo.png' }}" type="image/x-icon" />

        <!--begin::Fonts(mandatory for all pages)-->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700"/>        <!--end::Fonts-->

        
        
                    <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
                    <link href="{{ asset('public/assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css"/>
                            <link href="{{ asset('public/assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css"/>
                        <!--end::Global Stylesheets Bundle-->
        
                    <!-- Google tag (gtag.js) -->
     
        <script>
            // Frame-busting to prevent site from being loaded within a frame without permission (click-jacking)
            if (window.top != window.self) {
                window.top.location.replace(window.self.location.href);
            }
        </script>
    </head>
    <!--end::Head-->

    <!--begin::Body-->
    <body  id="kt_body"  class="app-blank bgi-size-cover bgi-attachment-fixed bgi-position-center bgi-no-repeat" >
        <!--begin::Theme mode setup on page load-->
<script>
	var defaultThemeMode = "light";
	var themeMode;

	if ( document.documentElement ) {
		if ( document.documentElement.hasAttribute("data-bs-theme-mode")) {
			themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
		} else {
			if ( localStorage.getItem("data-bs-theme") !== null ) {
				themeMode = localStorage.getItem("data-bs-theme");
			} else {
				themeMode = defaultThemeMode;
			}			
		}

		if (themeMode === "system") {
			themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
		}

		document.documentElement.setAttribute("data-bs-theme", themeMode);
	}            
</script>
<!--end::Theme mode setup on page load-->
                            
        <!--begin::Root-->
        <div class="d-flex flex-column flex-root" id="kt_app_root">
    <!--begin::Page bg image-->
    <style>
        body {
            background-image: url('{{ env('IMAGE_SHOW_PATH') . 'assets/media/auth/bg4.jpg' }}');
        }

        [data-bs-theme="dark"] body {
            background-image: url('{{ env('IMAGE_SHOW_PATH') . 'assets/media/auth/bg4-dark.jpg' }}');
        }

        .center-wrapper {
            height: 100vh; /* Full viewport height */
            padding: 50px 0; /* Equal top and bottom padding */
        }
    </style>
    <!--end::Page bg image-->

    <!--begin::Authentication - Sign-in -->
    <div class="d-flex flex-column flex-root center-wrapper">
        <div class="d-flex justify-content-center align-items-center h-100">
            <!--begin::Card-->
            <div class=" d-flex flex-column align-items-stretch flex-center rounded-4 w-md-400px pt-10 pb-20 mobileView" style="background-color:#ffffff45">
                <!--begin::Wrapper-->
                <div class="d-flex flex-center flex-column flex-column-fluid px-lg-10">
                    
                    <!--begin::Form-->
                    <form class="form w-100" method="post" novalidate="novalidate" id="kt_sign_in_form" data-kt-redirect-url="/" action="{{ url('login') }}">
                        @csrf
                        <!--begin::Heading-->
                        <div class="text-center mb-10">
                            <div class="m-10 ">
                                <img alt="Logo" class="h-90px mb-7" src="{{ env('IMAGE_SHOW_PATH') .'setting/'. $setting->logo }}" onerror="this.src='{{ env('IMAGE_SHOW_PATH') . 'default/logo.png' }}'" />
                                <!--begin::Title-->
                            </div>
                            <h1 class="text-gray-900 fw-bolder mb-3">Sign In</h1>
                            <!-- <div class="text-gray-500 fw-semibold fs-6 mb-10">
                                {{ $setting->name ?? '' }}
                            </div> -->
                        </div>
                        <!--end::Heading-->

                        <!--begin::Input group--->
                        <div class="fv-row mb-8">
                            <input type="text" placeholder="Username" name="username" id="username" autocomplete="off" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" />
                            @error('username')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="fv-row mb-3">
                            <input type="password" placeholder="Password" name="password" id="password" autocomplete="off" class="form-control @error('password') is-invalid @enderror" value="{{ old('password') }}" />
                            @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" id="kt_sign_in_submit" class="mt-3 btn btn-primary">
                                <span class="indicator-label">Sign In</span>
                                <span class="indicator-progress">Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Card-->
        </div>
    </div>
    <!--end::Authentication - Sign-in -->
</div>


<style>
    .form-control {
 
  background-color: #ffffff94 !important;
 
}

@media (max-width: 980px) {
    .mobileView {
        padding: 35px;
    }
}
</style>

<!--end::Root-->
<script>
            var hostUrl = "{{ url('/') }}";      
        </script>
                    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
                    <script src="{{ asset('public/assets/plugins/global/plugins.bundle.js') }}"></script>
                            <script src="{{ asset('public/assets/js/scripts.bundle.js') }}"></script>
                        <!--end::Global Javascript Bundle-->
        
        
                <!--end::Javascript-->
    </body>
    <!--end::Body-->
</html> 