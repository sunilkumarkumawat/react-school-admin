
<!DOCTYPE html>

<html lang="en" >
    <!--begin::Head-->
@php
$setting = DB::table('settings')->whereNull('deleted_at')->first();
$slogan = explode('@', $setting->login_slogan);
@endphp
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
        
       
        <script>
            // Frame-busting to prevent site from being loaded within a frame without permission (click-jacking)
            if (window.top != window.self) {
                window.top.location.replace(window.self.location.href);
            }
        </script>
        
                        <script src="https://code.jquery.com/jquery-3.7.1.slim.min.js" integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8=" crossorigin="anonymous"></script>
       
        
    </head>
    <!--end::Head-->

    <!--begin::Body-->
    <body  id="kt_body"  class="app-blank" >
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
    
<!--begin::Authentication - Sign-in -->
<div class="d-flex flex-column flex-lg-row flex-column-fluid">    
    <!--begin::Body-->
    <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 order-2 order-lg-1">
        <!--begin::Form-->
        <div class="d-flex flex-center flex-column flex-lg-row-fluid">
        <a href="{{ url('/') }}" class="mb-0 mb-lg-12">
                <img alt="Logo" src="{{ env('IMAGE_SHOW_PATH') . $setting->logo }}" onerror="this.src='{{ env('IMAGE_SHOW_PATH') . 'default/logo.png' }}'" class="h-60px h-lg-100px"/>
            </a> 
            <!--begin::Wrapper-->
            <div class="w-lg-500px p-10">
                
<!--begin::Form-->
<form class="form w-100" method="post" novalidate="novalidate" id="kt_sign_in_form" data-kt-redirect-url="/" action="{{ url('login') }}">
    @csrf
    <!--begin::Heading-->
    <div class="text-center mb-11">
        
        <!--begin::Title-->
        <h1 class="text-gray-900 fw-bolder mb-3">
            Login   In 
        </h1>
        <!--end::Title-->

        <!--begin::Subtitle-->
        <div class="text-gray-500 fw-semibold fs-6">
            {{ $setting->name ?? '' }}
        </div>
        <!--end::Subtitle--->
    </div>
    <!--begin::Heading-->


    <!--begin::Input group--->
    <div class="fv-row mb-8">
        <!--begin::Email-->
        <input type="text" placeholder="Username" name="username" id="username" autocomplete="off" class="form-control bg-transparent @error('username') is-invalid @enderror"/> 
        @error('username')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
        <!--end::Email-->
    </div>

    <!--end::Input group--->
    <div class="fv-row mb-3">    
        <!--begin::Password-->
        <input type="password" placeholder="Password" name="password" id="password" autocomplete="off" class="form-control bg-transparent @error('password') is-invalid @enderror"/>
        @error('password')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
        <!--end::Password-->
    </div>
    <!--end::Input group--->

    <!--begin::Wrapper-->
    <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
        <div></div>

        <!--begin::Link-->
        <!--<a href="reset-password.html" class="link-primary">-->
        <!--    Forgot Password ?-->
        <!--</a>-->
        <!--end::Link-->
    </div>
    <!--end::Wrapper-->    

    <!--begin::Submit button-->
    <div class="text-center mb-10">
        <button type="submit" id="kt_sign_in_submit" class="btn btn-primary btn-sm">
            
<!--begin::Indicator label-->
<span class="indicator-label">
    Sign In</span>
<!--end::Indicator label-->

<!--begin::Indicator progress-->
<span class="indicator-progress">
    Please wait...    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
</span>
<!--end::Indicator progress-->        </button>
    </div>
    <!--end::Submit button-->

    <!--begin::Sign up-->
    <!--<div class="text-gray-500 text-center fw-semibold fs-6">-->
    <!--    Not a Member yet?-->

    <!--    <a href="sign-up.html" class="link-primary">-->
    <!--        Sign up-->
    <!--    </a>-->
    <!--</div>-->
    <!--end::Sign up-->
</form>
<!--end::Form--> 
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Form-->       
       
        

        <!--begin::Footer-->  
        <div class="w-lg-500px d-flex flex-stack px-10 mx-auto">
           
            <!--begin::Links-->
            
            <!--end::Links-->
        </div>
        <!--end::Footer-->
    </div>
    <!--end::Body-->
    
    <!--begin::Aside-->
    <div class="d-flex flex-lg-row-fluid w-lg-50 bgi-size-cover bgi-position-center order-1 order-lg-2" >
        <!--begin::Content-->
        <div class="d-flex flex-column flex-center py-7 py-lg-15 px-5 px-md-15 w-100">          
            <!--begin::Logo-->
            <!-- <a href="{{ url('/') }}" class="mb-0 mb-lg-12">
                <img alt="Logo" src="{{ env('IMAGE_SHOW_PATH') . $setting->logo }}" onerror="this.src='{{ env('IMAGE_SHOW_PATH') . 'default/logo.png' }}'" class="h-60px h-lg-100px"/>
            </a>     -->
            <!--end::Logo-->

            <!--begin::Image-->                
            <img class="d-none d-lg-block mx-auto w-275px w-md-50 w-xl-500px mb-10 mb-lg-20" src="{{ env('IMAGE_SHOW_PATH') . 'assets/media/misc/auth-screens.png' }}" alt=""/>                 
            <!--end::Image-->

            <!--begin::Title-->
            <!-- <h1 class="d-none d-lg-block text-white fs-2qx fw-bolder text-center mb-7"> 
                {{ $setting->name ?? '' }}
            </h1>   -->
            <!--end::Title-->
           
            <!--begin::Text-->
            <!--<div class="d-none d-lg-block text-white fs-base text-center">-->
            <!--    In this kind of post, <a href="#" class="opacity-75-hover text-warning fw-bold me-1">the blogger</a> -->

            <!--    introduces a person they’ve interviewed <br/> and provides some background information about -->
                
            <!--    <a href="#" class="opacity-75-hover text-warning fw-bold me-1">the interviewee</a> -->
            <!--    and their <br/> work following this is a transcript of the interview.  -->
            <!--</div>-->
            <!-- <div class="d-none d-lg-block text-white fs-base text-center">
                <marquee direction="up" scrollamount="2" scrolldelay="250" loop="-1" width="100%" height="20px" class="text-center">
                @if(!empty($slogan))
                @foreach($slogan as $slgn)
                    {{ $slgn ?? '' }}<br><br>
                @endforeach
                @endif
                </marquee>
            </div> -->
            <!--end::Text-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Aside-->
</div>
<!--end::Authentication - Sign-in-->
                         


                         </div>
<!--end::Root-->
        
        <!--begin::Javascript-->
        
        <script>
            var hostUrl = "{{ url('/') }}";      
        </script>

                    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
                            <script src="{{ asset('public/assets/plugins/global/plugins.bundle.js') }}"></script>
                            <script src="{{ asset('public/assets/js/scripts.bundle.js') }}"></script>
                        <!--end::Global Javascript Bundle-->
        
        
                    <!--begin::Custom Javascript(used for this page only)-->
                            <!-- <script src="{{ asset('public/assets/js/custom/authentication/sign-in/general.js') }}"></script> -->
                        <!--end::Custom Javascript-->
                <!--end::Javascript-->

                
                
                
            </body>
    <!--end::Body-->





</html>