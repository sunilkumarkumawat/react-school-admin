<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AboutDevelopersController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProjectAmenitiesController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\PossessionStatusController;
use App\Http\Controllers\OwnershipController;
use App\Http\Controllers\PropertyTypeController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\LocalityController;
use App\Http\Controllers\SubLocalityController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\NewsLetterController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\VisiterLogsController;
use App\Http\Controllers\PropertyValuationController;
use App\Http\Controllers\BankInformationController;
use App\Http\Controllers\EnquiryDetailController;
use App\Http\Controllers\LeadManagmentController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ProjectController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::middleware(['guest'])->group(function () {
    //Start Of AuthController
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    // End of AuthController







});
Route::middleware(['auth'])->group(function () {

    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    //Start Of DashboardController
    Route::get('/', [DashboardController::class, 'index']);

    Route::get('/dashboard', [DashboardController::class, 'dashboard']);
    // End of DashboardController

    //Start Profile Controller
    Route::match(['get', 'post'], '/profileOverview', [ProfileController::class, 'profileOverview']);
    //End Profile Controller

    //Srat Of Property Controller
    Route::match(['get', 'post'], 'propertyAdd', [PropertyController::class, 'propertyAdd']);
    Route::match(['get', 'post'], 'propertyView', [PropertyController::class, 'propertyView']);
    Route::match(['get', 'post'], 'propertyApproval', [PropertyController::class, 'propertyApproval']);
    Route::match(['get', 'post'], 'getPropertyApproval', [PropertyController::class, 'getPropertyApproval']);
    Route::match(['get', 'post'], 'PropertyApprovalStatus', [PropertyController::class, 'PropertyApprovalStatus']);
    Route::match(['get', 'post'], 'getPropertyData', [PropertyController::class, 'getPropertyData']);
    Route::match(['get', 'post'], 'getDetailForm', [PropertyController::class, 'getDetailForm']);
    Route::match(['get', 'post'], 'getProjects', [PropertyController::class, 'getProjects']);
    Route::match(['get', 'post'], 'addProject', [PropertyController::class, 'addProject']);
    Route::match(['get', 'post'], 'residentialSellProperty', [PropertyController::class, 'residentialSellProperty']);
    Route::match(['get', 'post'], 'residentialLivingProperty', [PropertyController::class, 'residentialLivingProperty']);
    Route::match(['get', 'post'], 'commercialRentProperty', [PropertyController::class, 'commercialRentProperty']);
    Route::match(['get', 'post'], 'topProperty', [PropertyController::class, 'topProperty']);
    Route::match(['get', 'post'], 'getTopProperty', [PropertyController::class, 'getTopProperty']);
    Route::any('/commercialView', [PropertyController::class, 'commercialView']);
    Route::any('propertyDetailView/{id}', [PropertyController::class, 'propertyDetailView']);
    Route::any('propertyDelete/{id}', [PropertyController::class, 'propertyDelete']);

    Route::match(['get', 'post'], 'getLocality', [PropertyController::class, 'getLocality']);
    Route::match(['get', 'post'], 'getSubLocality', [PropertyController::class, 'getSubLocality']);

    Route::match(['get', 'post'], 'propertyReviews', [PropertyController::class, 'propertyReviews']);
    Route::match(['get', 'post'], 'getPropertyReviews', [PropertyController::class, 'getPropertyReviews']);
    Route::match(['get', 'post'], 'propertyReviewStatus', [PropertyController::class, 'propertyReviewStatus']);
    Route::any('commercialPropertyEdit/{id}', [PropertyController::class, 'commercialPropertyEdit']);
    Route::any('residentialRentPropertyEdit/{id}', [PropertyController::class, 'residentialRentPropertyEdit']);
    Route::any('residentialSellPropertyEdit/{id}', [PropertyController::class, 'residentialSellPropertyEdit']);
    Route::any('residentialLivingPropertyEdit/{id}', [PropertyController::class, 'residentialLivingPropertyEdit']);
    Route::any('propertySegment', [PropertyController::class, 'propertySegment']);
    //End Of Property Controller 

    //Start Of BannerController
    Route::any('/bannerAdd', [BannerController::class, 'bannerAdd']);
    Route::get('/bannerView', [BannerController::class, 'bannerView']);
    Route::any('/bannerStatus', [BannerController::class, 'bannerStatus']);
    Route::any('/bannerDelete/{id}', [BannerController::class, 'bannerDelete']);
    Route::any('/bannerEdit/{id}', [BannerController::class, 'bannerEdit']);
    Route::any('/getBannertData', [BannerController::class, 'getBannertData']);
    //End Of BannerController

    //Start Of Notification Section
    Route::match(['get', 'post'], '/messageTemplateAdd', [NotificationController::class, 'messageTemplateAdd']);
    Route::match(['get', 'post'], '/messageTemplateView', [NotificationController::class, 'messageTemplateView']);
    Route::match(['get', 'post'], '/getMessageTemplate', [NotificationController::class, 'getMessageTemplate']);
    Route::match(['get', 'post'], '/messageTemplateStatus', [NotificationController::class, 'messageTemplateStatus']);
    Route::match(['get', 'post'], '/messageTemplateEdit/{id}', [NotificationController::class, 'messageTemplateEdit']);
    Route::match(['get', 'post'], '/messageTemplateDelete', [NotificationController::class, 'messageTemplateDelete']);
    //End Of Notification Section

    //Start Of Package Section
    Route::match(['get', 'post'], '/packageAdd', [PackageController::class, 'packageAdd']);
    Route::match(['get', 'post'], '/packageView', [PackageController::class, 'packageView']);
    Route::match(['get', 'post'], '/packageEdit/{id}', [PackageController::class, 'packageEdit']);
    Route::match(['get', 'post'], '/packageDelete', [PackageController::class, 'packageDelete']);
    Route::match(['get', 'post'], '/getPackage', [PackageController::class, 'getPackage']);
    //End Of Package Section

    //Start Of About Developers
    Route::any('/developerAdd', [AboutDevelopersController::class, 'developerAdd']);
    Route::any('/developerView', [AboutDevelopersController::class, 'developerView']);
    Route::any('/developerEdit/{id}', [AboutDevelopersController::class, 'developerEdit']);
    Route::any('/developerDelete/{id}', [AboutDevelopersController::class, 'developerDelete']);
    Route::any('/getDeveloperData', [AboutDevelopersController::class, 'getDeveloperData']);
    //End Of About Developers

    //Start Of SettingController
    Route::match(['get', 'post'], '/updateSetting', [SettingController::class, 'updateSetting']);
    Route::match(['get', 'post'], '/contactUsAdd', action: [SettingController::class, 'contactUsAdd']);
    Route::any('/termCondition', [SettingController::class, 'termConditionView']);
    Route::any('/plan', [SettingController::class, 'planView']);
    Route::get('/aboutUs', [SettingController::class, 'aboutUsView']);
    // End of SettingController

    //Start Of News Section
    Route::any('/newsCategoryAdd', [NewsController::class, 'newsCategoryAdd']);
    Route::any('/getNewsCategoryData', [NewsController::class, 'getNewsCategoryData']);
    Route::any('/newsCategoryEdit/{id}', [NewsController::class, 'newsCategoryEdit']);
    Route::any('/newsCategoryDelete/{id}', [NewsController::class, 'newsCategoryDelete']);
    Route::get('/newsCategoryView', [NewsController::class, 'newsCategoryView'])->name('news.newsCategoryView');

    Route::any('/newsSubCategoryAdd', [NewsController::class, 'newsSubCategoryAdd'])->name('news.newsSubCategoryAdd');
    Route::any('/newsSubCategoryEdit/{id}', [NewsController::class, 'newsSubCategoryEdit']);
    Route::get('/newsSubCategoryView', [NewsController::class, 'newsSubCategoryView'])->name('news.newsSubCategoryView');
    Route::any('/newsSubCategoryDelete/{id}', [NewsController::class, 'newsSubCategoryDelete']);
    Route::any('/getNewsSubCategoryData', [NewsController::class, 'getNewsSubCategoryData']);

    Route::any('/newsAdd', [NewsController::class, 'newsAdd']);
    Route::any('/newsView', [NewsController::class, 'newsView']);
    Route::any('/newsDelete/{id}', [NewsController::class, 'newsDelete']);
    Route::any('/newsEdit/{id}', [NewsController::class, 'newsEdit']);
    Route::any('/newsSubCategoryData/{id}', [NewsController::class, 'newsSubCategoryData']);
    Route::any('/getNewsData', [NewsController::class, 'getNewsData']);
    //End Of News Section


    //ProjectAmenities
    Route::any('/projectAmenitiesAdd', [ProjectAmenitiesController::class, 'projectAmenitiesAdd']);
    Route::any('/projectAmenitiesView', [ProjectAmenitiesController::class, 'projectAmenitiesView']);
    Route::any('/projectAmenitiesEdit/{id}', [ProjectAmenitiesController::class, 'projectAmenitiesEdit']);
    Route::any('/projectAmenitiesDelete/{id}', [ProjectAmenitiesController::class, 'projectAmenitiesDelete']);
    Route::any('/getAmenitiestData', [ProjectAmenitiesController::class, 'getAmenitiestData']);
    //End of ProjectAmenities

    //Start Of Agent Controller
    Route::any('/agentAdd', [AgentController::class, 'agentAdd']);
    Route::any('/agentView', [AgentController::class, 'agentView']);
    Route::any('/agentDelete/{id}', [AgentController::class, 'agentDelete']);
    Route::any('/agentEdit/{id}', [AgentController::class, 'agentEdit']);
    Route::any('/getAgenttData', [AgentController::class, 'getAgenttData']);
    //End Of Agent Controller

    //Start of PossessionController 
    Route::any('/possessionStatusAdd', [PossessionStatusController::class, 'possessionStatusAdd']);
    Route::any('/possessionStatusView', [PossessionStatusController::class, 'possessionStatusView']);
    Route::any('/possessionStatusDelete/{id}', [PossessionStatusController::class, 'possessionStatusDelete']);
    Route::any('/possessionStatusEdit/{id}', [PossessionStatusController::class, 'possessionStatusEdit']);
    Route::any('/getPossessionStatusData', [PossessionStatusController::class, 'getPossessionStatusData']);
    //End of PossessionController

    //Ownership
    Route::any('/ownershipAdd', [OwnershipController::class, 'ownershipAdd']);
    Route::any('/ownershipView', [OwnershipController::class, 'ownershipView']);
    Route::any('/ownershipDelete/{id}', [OwnershipController::class, 'ownershipDelete']);
    Route::any('/ownershipEdit/{id}', [OwnershipController::class, 'ownershipEdit']);
    Route::any('/getOwnershipData', [OwnershipController::class, 'getOwnershipData']);

    //Start of PropertyTypeController 
    Route::any('/propertyTypeAdd', [PropertyTypeController::class, 'propertyTypeAdd']);
    Route::any('/propertyTypeView', [PropertyTypeController::class, 'propertyTypeView']);
    Route::any('/propertyTypeEdit/{id}', [PropertyTypeController::class, 'propertyTypeEdit']);
    Route::any('/propertyTypeDelete/{id}', [PropertyTypeController::class, 'propertyTypeDelete']);
    Route::any('/getPropertyTypeData', [PropertyTypeController::class, 'getPropertyTypeData']);
    //End of PropertyTypeController

    //Start of DistrictController 
    Route::any('/districtAdd', [DistrictController::class, 'districtAdd']);
    Route::any('/districtView', [DistrictController::class, 'districtView']);
    Route::any('/districtEdit/{id}', [DistrictController::class, 'districtEdit']);
    Route::any('/districtDelete/{id}', [DistrictController::class, 'districtDelete']);
    Route::any('/getDistrictData', [DistrictController::class, 'getDistrictData']);
    //End of DistrictController

    //Start of LocalityController 
    Route::any('/localityAdd', [LocalityController::class, 'localityAdd']);
    Route::any('/localityView', [LocalityController::class, 'localityView']);
    Route::any('/localityEdit/{id}', [LocalityController::class, 'localityEdit']);
    Route::any('/localityDelete/{id}', [LocalityController::class, 'localityDelete']);
    Route::any('/getlocalityData', [LocalityController::class, 'getlocalityData']);
    //End of LocalityController

    //Start of SubLocalityController 
    Route::any('/subLocalityAdd', [SubLocalityController::class, 'subLocalityAdd']);
    Route::any('/subLocalityView', [SubLocalityController::class, 'subLocalityView']);
    Route::any('/subLocalityEdit/{id}', [SubLocalityController::class, 'subLocalityEdit']);
    Route::any('/subLocalityDelete/{id}', [SubLocalityController::class, 'subLocalityDelete']);
    Route::any('/subLocalityData', [SubLocalityController::class, 'subLocalityData']);
    //End of SubLocalityController

    //Start of FaqController 
    Route::any('/faqAdd', [FaqController::class, 'faqAdd']);
    Route::any('/faqView', [FaqController::class, 'faqView']);
    Route::any('/faqStatus', [FaqController::class, 'faqStatus']);
    Route::any('/faqEdit/{id}', [FaqController::class, 'faqEdit']);
    Route::any('/faqDelete/{id}', [FaqController::class, 'faqDelete']);
    Route::any('/getFaqtData', [FaqController::class, 'getFaqtData']);
    //End of FaqController

    //Start of ContactController 
    Route::any('/contactView', [ContactController::class, 'contactView']);
    Route::any('/getContactData', [ContactController::class, 'getContactData']);
    Route::any('/contactStatusUpdate/{id}', [ContactController::class, 'contactStatusUpdate']);
    Route::any('/assignContact', [ContactController::class, 'assignContact']);
    //End of FaqController

    //Start of NewsLetterController 
    Route::any('/newsLetterView', [NewsLetterController::class, 'newsLetterView']);
    Route::any('/newsLetterStatus', [NewsLetterController::class, 'newsLetterStatus']);
    Route::any('/getNewsLetterData', [NewsLetterController::class, 'getNewsLetterData']);
    //End of NewsLetterController

    //Start of TestimonialController 
    Route::any('/testimonialView', [TestimonialController::class, 'testimonialView']);
    Route::any('/testimonialDelete/{id}', [TestimonialController::class, 'testimonialDelete']);
    Route::any('/testimonialStatus', [TestimonialController::class, 'testimonialStatus']);
    Route::any('/testimonialUserBlock', [TestimonialController::class, 'testimonialUserBlock']);
    Route::any('/getTestimonialData', [TestimonialController::class, 'getTestimonialData']);
    //End of TestimonialController

    //Top Projects 
    Route::any('/topProjectAdd', [PropertyController::class, 'topProjectAdd']);
    Route::any('/propertyStatus', [PropertyController::class, 'propertyStatus']);

    //End of Top projects

    //Start of ProjectController 
    Route::any('/projectAdd', [ProjectController::class, 'projectAdd']);
    Route::any('/projectView', [ProjectController::class, 'projectView']);
    Route::any('/getProjectData', [ProjectController::class, 'getProjectData']);
    Route::any('/projectStatus', [ProjectController::class, 'projectStatus']);
    Route::any('/projectDelete/{id}', [ProjectController::class, 'projectDelete']);
    Route::any('/projectEdit/{id}', [ProjectController::class, 'projectEdit']);
    //End of ProjectController 

    //Start of User Controller 
    Route::any('/userAdd', [UserController::class, 'userAdd']);
    Route::any('/userEdit/{id}', [UserController::class, 'userEdit']);
    Route::any('/userDelete/{id}', [UserController::class, 'userDelete']);
    Route::any('/userActive/{id}', [UserController::class, 'userActive']);
    Route::any('/userView', [UserController::class, 'userView']);
    Route::any('/endUserView', [UserController::class, 'endUserView']);
    Route::any('/getEndUserData', [UserController::class, 'getEndUserData']);
    Route::any('/endUserEdit/{id}', [UserController::class, 'endUserEdit']);
    Route::any('/getUserData', [UserController::class, 'getUserData']);
    Route::any('/userStatus', [UserController::class, 'userStatus']);
    //End of TestimonialController


    // //Start of ContactUs Controller 
    // Route::any('/contactUsAdd', [ContactUsController::class, 'contactUsAdd']);
    // Route::any('/contactUsEdit/{id}', [ContactUsController::class, 'contactUsEdit']);
    // Route::any('/contactUsDelete/{id}', [ContactUsController::class, 'contactUsDelete']);
    // Route::any('/contactUsView', [ContactUsController::class, 'contactUsView']);
    // Route::any('/getContactUstData', [ContactUsController::class, 'getContactUstData']);
    // //End of ContactUs Controller 


    //Start of VisiterLogs Controller 
    Route::any('/visiterLogs', [VisiterLogsController::class, 'visiterLogsView']);
    Route::any('/getVisiterLog', [VisiterLogsController::class, 'getVisiterLog']);
    Route::any('/updateStatus', [VisiterLogsController::class, 'updateStatus']);
    //End of ContactUs Controller 


    //Start of PropertyValuation Controller
    Route::any('/propertyValuation', [PropertyValuationController::class, 'propertyValuation']);
    Route::any('/getPropertyValuation', [PropertyValuationController::class, 'getPropertyValuation']);
    Route::any('/getValuation', [PropertyValuationController::class, 'getValuation']);
    Route::any('/updateValuationStatus/{id}', [PropertyValuationController::class, 'updateValuationStatus']);
    Route::any('/assignProperty', [PropertyValuationController::class, 'assignProperty']);
    Route::any('/transferProperty', [PropertyValuationController::class, 'transferProperty']);
    Route::any('/changeStatus', [PropertyValuationController::class, 'changeStatus']);
    Route::any('/sendValuationMessages', [PropertyValuationController::class, 'sendValuationMessages']);
    //End of PropertyValuation Controller 

    //Start of BankInformation Controller
    Route::any('/bankDetailAdd', [BankInformationController::class, 'bankDetailAdd']);
    Route::any('/bankDetailView', [BankInformationController::class, 'bankDetailView']);
    Route::any('/getBankDetail', [BankInformationController::class, 'getBankDetail']);
    Route::any('/bankDetailStatus', [BankInformationController::class, 'bankDetailStatus']);
    Route::any('/bankDetailEdit/{id}', [BankInformationController::class, 'bankDetailEdit']);
    Route::any('/bankDetailDelete/{id}', [BankInformationController::class, 'bankDetailDelete']);
    //End of BankInformation Controller 

    //Start of Enquiry Controller
    Route::any('/enquiryDeatilsView', [EnquiryDetailController::class, 'enquiryDeatilsView']);
    Route::any('/getenquiryDeatilsView', [EnquiryDetailController::class, 'getenquiryDeatilsView']);
    Route::post('/update-status', [EnquiryDetailController::class, 'updateStatus']);

    //End of Enquiry Controller 

    //Start of LeadManagmentController 
    Route::any('/leadView', [LeadManagmentController::class, 'leadView']);
    Route::any('/getLeadData', [LeadManagmentController::class, 'getLeadData']);
    Route::any('/leadStatusUpdate/{id}', [LeadManagmentController::class, 'leadStatusUpdate']);
    Route::any('/getLocation', [LeadManagmentController::class, 'getLocation']);
    Route::any('/getPropertyByLocation', [LeadManagmentController::class, 'getPropertyByLocation']);
    Route::any('/leadAssign', [LeadManagmentController::class, 'leadAssign']);
    //End of LeadManagmentController

    //Start of SubscriptionController
    Route::any('/subscriptionView', [SubscriptionController::class, 'subscriptionView']);
    Route::any('/getSubscriptionData', [SubscriptionController::class, 'getSubscriptionData']);
    //End of SubscriptionController

});


