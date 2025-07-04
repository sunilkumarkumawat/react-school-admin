<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use DB;
use Session;
use File;
use App\Models\Setting;
use App\Models\Sidebar;
use App\Models\User;
use App\Models\Sessions;
use App\Models\NewsLetter;
use App\Models\Contact;
use App\Models\LeadManagment;
use App\Models\Role;
use App\Models\Property;
use App\Models\EnquiryDetail;
use App\Models\Project;
use App\Models\ProjectAmenities;
use App\Models\PropertyValuation;
use DateTime;
use Response;
use Arr;
use App\Jobs\ProcessApiRequest;
use App\Mail\SendNotificationMail;
use App\Models\MessageTemplate;
use App\Models\Package;
use Illuminate\Support\Facades\Mail;

class helper{


public static function allSidebarMenus()
{
$sidebarMenu = [
    [
        'title' => 'Dashboard',
        'className' => 'dashboard.view',
        'status' => true,
        'icon' => 'dashboard', // Google Material Symbol
        'route' => 'dashboard',
    ],
    [
        'title' => 'Branch',
        'className' => 'branch.view',
        'status' => true,
        'icon' => 'network-wired', // Google Material Symbol
        'route' => 'branch',
    ],
    [
        'title' => 'Role',
        'className' => 'role.view',
        'status' => true,
        'icon' => 'person', // Google Material Symbol
        'route' => 'role',
    ],
    [
        'title' => 'User Management',
        'className' => 'user_management',
        'status' => true,
        'icon' => 'users', // Material Symbol
        'subItems' => [
            [
                'title' => 'Add User',
                'className' => 'user_management.add',
                'status' => true,
                'route' => 'userAdd',
                'icon' => 'person_add', // Material Symbol
            ],
            [
                'title' => 'View User',
                'className' => 'user_management.view',
                'status' => true,
                'route' => 'userView',
                'icon' => 'visibility', // Material Symbol
            ],
            [
                'title' => 'Id & Password',
                'className' => 'user_management.credentials',
                'status' => true,
                'route' => 'userCredentials',
                'icon' => 'key', // Material Symbol
            ],
        ],
    ],
    [
        'title' => 'Student Management',
        'className' => 'student_management',
        'status' => true,
        'icon' => 'school', // Material Symbol
        'subItems' => [
            [
                'title' => 'Add Student',
                'className' => 'student_management.add',
                'status' => true,
                'route' => 'studentAdd',
                'icon' => 'person_add', // Material Symbol
            ],
            [
                'title' => 'View Student',
                'className' => 'student_management.view',
                'status' => true,
                'route' => 'studentView',
                'icon' => 'visibility', // Material Symbol
            ],
            [
                'title' => 'Id & Password',
                'className' => 'student_management.credentials',
                'status' => true,
                'route' => 'studentCredentials',
                'icon' => 'key', // Material Symbol
            ],
        ],
    ],
     [
        'title' => 'Expense',
        'className' => 'Expense.view',
        'status' => true,
        'icon' => 'money-bill-wave', // Google Material Symbol
        'route' => 'expense',
    ],
     [
        'title' => 'Fees Management',
        'className' => 'fees_management',
        'status' => true,
        'icon' => 'money-bill-wave', // Material Symbol
        'subItems' => [
            [
                'title' => 'Fees Group',
                'className' => 'fees_management.add',
                'status' => true,
                'route' => 'feesGroup',
                'icon' => 'person_add', // Material Symbol
            ],
            [
                'title' => 'Fees Type',
                'className' => 'fees_management.view',
                'status' => true,
                'route' => 'feesType',
                'icon' => 'visibility', // Material Symbol
            ],
            [
                'title' => 'Fees Master',
                'className' => 'fees_management.credentials',
                'status' => true,
                'route' => 'feesMaster',
                'icon' => 'key', // Material Symbol
            ],
        ],
    ],
     [
        'title' => 'Master',
        'className' => 'master',
        'status' => true,
        'icon' => 'network-wired', // Material Symbol
        'subItems' => [
            [
                'title' => 'Class',
                'className' => 'master.class',
                'status' => true,
                'route' => 'class',
                'icon' => 'network-wired', // Material Symbol
            ]
        ],
    ],
  
];


return $sidebarMenu;
}
public static function getPermissions($user)
{
    if (!$user) {
        return [];
    }

    $userId = $user->id;
    $roleId = $user->role_id;

    // Step 1: Try user_permissions
    $userPermissions = DB::table('user_permissions')
        ->where('user_id', $userId)
        ->pluck('permission')
        ->toArray();

    if (!empty($userPermissions)) {
        return $userPermissions;
    }

    // Step 2: Fallback to role_permissions
    $rolePermissions = DB::table('role_permissions')
        ->where('role_id', $roleId)
        ->pluck('permission')
        ->toArray();

    return !empty($rolePermissions) ? $rolePermissions : [];
}

public static function getSidebar($user)
{
    $allPermissions = self::getPermissions($user);
    $allowedPermissions = array_map('strtolower', $allPermissions);

  $sidebarMenu = self::allSidebarMenus();
    // Filter for non-admin roles
    if ($user->role_id != 1) {
        foreach ($sidebarMenu as $key => &$menu) {
            if (isset($menu['subItems'])) {
                $menu['subItems'] = array_filter($menu['subItems'], function ($sub) use ($allowedPermissions) {
                    return in_array(strtolower($sub['className']), $allowedPermissions);
                });

                if (empty($menu['subItems'])) {
                    unset($sidebarMenu[$key]);
                }
            } else {
                if (!in_array(strtolower($menu['className']), $allowedPermissions)) {
                    unset($sidebarMenu[$key]);
                }
            }
        }
    }

    return array_values($sidebarMenu);
}

   public function send($medium, $to, $message)
    {
        switch ($medium) {
            case 'sms':
                $this->sendSMS($to, $message);
                break;
            case 'email':
                $this->sendEmail($to, $message);
                break;
            case 'whatsapp':
                $this->sendWhatsApp($to, $message ,$filepath=null);
                break;
        }
    }



    public static function sendSMS($to,$message){



    }
    public static function sendEmail($to,$message){

      Mail::to($to)->queue(new SendNotificationMail($message));

    }
    public static function sendWhatsApp($to,$text,$filepath){

         if (!empty($to)) {


         
             $getData = 'number=91' . $to;
             
             if (!empty($text)) {
                 $getData .= '&message=' . urlencode($text);
             }
             
             if (!empty($filepath)) {
                 $getData .= '&fileurl=' . urlencode($filepath);
             }
             
             $serverUrl = "https://int.chatway.in/api/send-msg?username=rukmanisoft@gmail.com&" . $getData . $getData . "&token=SzlVM3ltYzFCaGU3RHZNQ1Y5Y2JNUT09";
             //$authKey = '353352756b6d616e69736f6674776172653130301726478340';
             //$url = $serverUrl . "?authentic-key=" . $authKey . "&route=1&" . $getData;
            //dd($url);
             $ch = curl_init();
             curl_setopt($ch, CURLOPT_URL, $serverUrl);
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
             
             $output = curl_exec($ch);
             
             if ($output === false) {
                 $error = curl_error($ch);
                 curl_close($ch);
                 return ['status' => 'error', 'message' => $error];
             }
             
             curl_close($ch);
       
             return ['status' => 'success', 'response' => $output];
         } else {
             return ['status' => 'error', 'message' => 'Mobile number is required.'];
   
         }


    }


     public static function getSetting(){
       
       $setting = Setting::where('branch_id',Session::get('branch_id'))->with('Account')->with('City')->with('Country')->with('State')->with('Account')->get()->first();
       
         if(empty($setting)){
            $setting = Setting::where('branch_id',1)->with('Account')->with('City')->with('Country')->with('State')->with('Account')->get()->first();
         }
      
       return $setting;
   
    } 
     public static function getTasksStatus(){
       
      $data['enq'] = EnquiryDetail::selectRaw("
      COUNT(*) as total_tasks,
      COUNT(CASE WHEN status = 22 THEN 1 END) as total_completed
  ")->first();
      $data['leads'] = LeadManagment::selectRaw("
      COUNT(*) as total_tasks,
      COUNT(CASE WHEN status = 30 THEN 1 END) as total_completed
  ")->first();
      $data['valuation'] = PropertyValuation::selectRaw("
      COUNT(*) as total_tasks,
      COUNT(CASE WHEN status = 39 THEN 1 END) as total_completed
  ")->first();

  
        
      
       return $data;
   
    } 
  
  public static function defaultData(){

    $emptyImage = env('IMAGE_SHOW_PATH') . 'assets/media/svg/files/blank-image.svg';
    $defaultData = [];
    $defaultData = Arr::add($defaultData, 'emptyImage', $emptyImage);
    $defaultData = Arr::add($defaultData, 'emptyData', 'No Data Found');
    return $defaultData;

  }

   public static function getSiderbar(){
      $getSidebar = Sidebar::where('status', 1)->orderBy('id', 'ASC')->get();
      return $getSidebar;
   }

  public static function sidebar1(){
     $sidebar1 = Sidebar::where('sidebar2', 0)->where('sidebar3', 0)->where('sidebar4', 0)->where('sidebar5', 0)->where('status', 1)->whereIn('id', explode(',', Auth::user()->sidebars))->whereRaw('sidebar1 NOT REGEXP "^[0-9]+$"')->orderBy('id', 'ASC')->get();
     return $sidebar1;
  }
  
  public static function sidebar2($id1){
     $sidebar2 = Sidebar::where('sidebar1', $id1)->where('sidebar3', 0)->where('sidebar4', 0)->where('sidebar5', 0)->where('status', 1)->whereIn('id', explode(',', Auth::user()->sidebars))->whereRaw('sidebar2 NOT REGEXP "^[0-9]+$"')->orderBy('id', 'ASC')->get();
     return $sidebar2;
  }
  
  public static function sidebar3($id1, $id2){
     $sidebar3 = Sidebar::where('sidebar1', $id1)->where('sidebar2', $id2)->where('sidebar4', 0)->where('sidebar5', 0)->where('status', 1)->whereIn('id', explode(',', Auth::user()->sidebars))->whereRaw('sidebar3 NOT REGEXP "^[0-9]+$"')->orderBy('id', 'ASC')->get();
     return $sidebar3;
  }
  
  public static function sidebar4($id1, $id2, $id3){
     $sidebar4 = Sidebar::where('sidebar1', $id1)->where('sidebar2', $id2)->where('sidebar3', $id3)->where('sidebar5', 0)->where('status', 1)->whereIn('id', explode(',', Auth::user()->sidebars))->whereRaw('sidebar4 NOT REGEXP "^[0-9]+$"')->orderBy('id', 'ASC')->get();
     return $sidebar4;
  }
  
  public static function sidebar5($id1, $id2, $id3, $id4){
     $sidebar5 = Sidebar::where('sidebar1', $id1)->where('sidebar2', $id2)->where('sidebar3', $id3)->where('sidebar4', $id4)->where('status', 1)->whereIn('id', explode(',', Auth::user()->sidebars))->whereRaw('sidebar5 NOT REGEXP "^[0-9]+$"')->orderBy('id', 'ASC')->get();
     return $sidebar5;
  }


  public static function getSidebar1(){
   $sidebar1 = Sidebar::where('sidebar2', 0)->where('sidebar3', 0)->where('sidebar4', 0)->where('sidebar5', 0)->where('status', 1)->whereRaw('sidebar1 NOT REGEXP "^[0-9]+$"')->orderBy('id', 'ASC')->get();
   return $sidebar1;
}

public static function getSidebar2($id1){
   $sidebar2 = Sidebar::where('sidebar1', $id1)->where('sidebar3', 0)->where('sidebar4', 0)->where('sidebar5', 0)->where('status', 1)->whereRaw('sidebar2 NOT REGEXP "^[0-9]+$"')->orderBy('id', 'ASC')->get();
   return $sidebar2;
}

public static function getSidebar3($id1, $id2){
   $sidebar3 = Sidebar::where('sidebar1', $id1)->where('sidebar2', $id2)->where('sidebar4', 0)->where('sidebar5', 0)->where('status', 1)->whereRaw('sidebar3 NOT REGEXP "^[0-9]+$"')->orderBy('id', 'ASC')->get();
   return $sidebar3;
}

public static function getSidebar4($id1, $id2, $id3){
   $sidebar4 = Sidebar::where('sidebar1', $id1)->where('sidebar2', $id2)->where('sidebar3', $id3)->where('sidebar5', 0)->where('status', 1)->whereRaw('sidebar4 NOT REGEXP "^[0-9]+$"')->orderBy('id', 'ASC')->get();
   return $sidebar4;
}

public static function getSidebar5($id1, $id2, $id3, $id4){
   $sidebar5 = Sidebar::where('sidebar1', $id1)->where('sidebar2', $id2)->where('sidebar3', $id3)->where('sidebar4', $id4)->where('status', 1)->whereRaw('sidebar5 NOT REGEXP "^[0-9]+$"')->orderBy('id', 'ASC')->get();
   return $sidebar5;
}


  public static function getRole($roleId){
      $getRole = Role::find($roleId);
      return $getRole;
  }

   public static function getNewsLetter(){
      $getNewsLetter = NewsLetter::all()->count();
      return $getNewsLetter;
   }
   public static function getAmenities(){
      $getAmenities = ProjectAmenities::all();
      return $getAmenities;
   }
   public static function getProject(){
      $getProject = Project::all()->count();
      return $getProject;
   }

   public static function getContactEnquiry(){
      $getContactEnquiry = Contact::all()->count();
      return $getContactEnquiry;
   }
   public static function getNewEnquiry(){
      $getNewEnquiry = Contact::where('status',19)->count();
      return $getNewEnquiry;
   }
   public static function getProgressEnquiry(){
      $getProgressEnquiry = Contact::where('status',20)->count();
      return $getProgressEnquiry;
   }

   public static function getMessageTemplate(){
      $getMessageTemplate = MessageTemplate::all()->count();
      return $getMessageTemplate;
   }

   public static function getMessageTemplateApproval(){
      $getMessageTemplateApproval = MessageTemplate::where('status',0)->count();
      return $getMessageTemplateApproval;
   }
   public static function getLead(){
      $LeadManagment = LeadManagment::all()->count();
      return $LeadManagment;
   }
   public static function getNewLead(){
      $getNewLead = LeadManagment::where('status',23)->count();
      return $getNewLead;
   }
   public static function getConvertLead(){
      $getConvertLead = LeadManagment::where('status',30)->count();
      return $getConvertLead;
   }
   public static function getAdminUser(){
      $getAdminUser = User::whereIn('users.role_id', [1, 7])->count();
      return $getAdminUser;
   }
   public static function getWebsiteUser(){
      $getWebsiteUser = User::whereNotIn('users.role_id', [1, 7])->count();
      return $getWebsiteUser;
   }

   public static function getpropertyValuation(){
      $getpropertyValuation = PropertyValuation::all()->count();
      return $getpropertyValuation;
   }
   public static function getpropertyValuationCompleted(){
      $getpropertyValuationCompleted = PropertyValuation::where('status',39)->count();
      return $getpropertyValuationCompleted;
   }
   public static function getpropertyApproval(){
      $getpropertyApproval = Property::where('status',0)->count();
      return $getpropertyApproval;
   }
   public static function getpropertyApproved(){
      $getpropertyApproved = Property::where('status',1)->count();
      return $getpropertyApproved;
   }
  

   public static function getLeadReminder(){
      $today = Carbon::now()->format('m-d'); 
      $getLeadReminder = DB::table('lead_managment')
      ->where(DB::raw("DATE_FORMAT(lead_managment.reminder_date, '%m-%d')"), $today)
      ->select( 'lead_managment.name') // Select desired columns
      ->get();
     
     return $getLeadReminder;
 }  
   public static function getEnquiryReminder(){
      $today = Carbon::now()->format('m-d'); 
      $getLeadReminder = DB::table('contacts')
      ->where(DB::raw("DATE_FORMAT(contacts.reminder_date, '%m-%d')"), $today)
      ->select( 'contacts.name') // Select desired columns
      ->get();
     
     return $getLeadReminder;
 }  

   public static function getPackage(){
      $getPackage = Package::all()->count();
      return $getPackage;
   }

   public static function sendMail($tmplale,$data) {

      Mail::send($tmplale, $data, function($message) use ($data) {
         $message->from(getenv('MAIL_FROM_ADDRESS'));
         $message->to($data['email']);
         $message->subject($data['subject']);

      });
        
   }

   public static function sendFirebaseMessage()
{
    $params = [
        'text' => 'Hello from Postman prashant',
        'senderId' => 'admin123',
        'schoolId' => 'school_001',
        'receiverType' => 'all',
        'fcmTokens' => [
            "c9gmfwvdrJ1DlbTUn2d17R:APA91bH-a4kVjIjD42vs0Jp9wU8W0L0tZ7j1TyDTKoFzZOSNekGSWeHEwn1QA5UFYCUuijN6VOb8pLkJ9zw8TEqwXXoQ5HVXPJ-oCWxrVOhH8GwBj2HkL2A"
        ]
    ];

    try {
        $response = Http::post('https://socket.rusofterp.in/api/chat/send', $params);

        if ($response->successful()) {
            return [
                'status' => 'success',
                'response' => $response->body()
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'API Error',
                'response' => $response->body()
            ];
        }

    } catch (\Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Request Failed: ' . $e->getMessage()
        ];
    }
}


}




