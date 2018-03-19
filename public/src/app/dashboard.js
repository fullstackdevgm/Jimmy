var JimmyDashboard  =  angular.module('Jimmy.dashboard',['ngResource','ngRoute',
                                                         'angular-loading-bar',
                                                         'angularUtils.directives.dirPagination',
                                                         'ngFileUpload','nvd3']);

var widgetHtml = null;
var widgetCanceler,campaignCanceler,profileCanceler;

var generalInfo = function($resource){
    return $resource('/info', {}, {'query': {method: 'GET', isArray: false}});
}
generalInfo.$inject = ["$resource"];


var blog = function($resource){
    return $resource('/blog', {}, {'query': {method: 'GET', isArray: false}});
}
blog.$inject = ["$resource"];

var user = function($resource){
    return $resource('/user/:user_id', {user_id:'@user_id'} ,
        {'query': {method: 'GET', isArray: false},'update': {method: 'PUT', isArray: false}});
}
user.$inject = ["$resource"];

var clientList = function($resource){
   return $resource("/clients",{}, {'query':{method:'GET',isArray: true}});
}
clientList.$inject = ["$resource"];

var packageList = function($resource){
   return $resource("/packages/:package_id",{package_id:'@package_id'}, {'query':{method:'GET',isArray: true},'get':{method:'GET',isArray: false,params:{package_id:'@package_id'},}});
}
packageList.$inject = ["$resource"];

var client  = function($resource){
   return $resource("/clients/:client_id",{client_id:'@client_id'},
                {
                  query:  {method:'GET'},
                  delete: { method: 'DELETE'},
                  update: {method:'PUT'},
             });
}
client.$inject = ["$resource"];

var clientSourceList = function($resource){
    return $resource("/clients/:client_id/sources",{},
    {'query':{method:'GET',params:{client_id:'@client_id'},isArray: true}});
}
clientSourceList.$inject = ["$resource"];

var clientSource = function($resource){
    return $resource("/clients/sources/:source_id",{}, {
        delete: { method: 'DELETE', params: {source_id: '@source_id'}  }
    })
}
clientSource.$inject = ["$resource"];

var campaignList = function($resource){
    return $resource("/clients/campaigns/:client_account_id",{}, {'query':{method:'GET',params:{client_account_id:'@client_account_id'},isArray: true}});
}
campaignList.$inject = ["$resource"];

var profileList = function($resource){
    return $resource("/clients/profiles/:client_account_id",{}, {'query':{method:'GET',params:{client_account_id:'@client_account_id'},isArray: true}});
}
profileList.$inject = ["$resource"];

var segmentList = function($resource){
    return $resource("/clients/segments/:client_account_id",{}, {'query':{method:'GET',params:{client_account_id:'@client_account_id'},isArray: true}});
}
segmentList.$inject = ["$resource"];

var goalsList = function($resource){
    return $resource("/clients/goals/:client_account_id/:profile_id",{}, {'query':{method:'GET',params:{profile_id:'@profile_id',client_account_id:'@client_account_id'},isArray: true}});
}
 goalsList.$inject = ["$resource"];
 
var activityLog = function($resource) {
    return $resource("/activity-log",{},{'query':{method:'POST'}, isArray:true});
}

var clientAccounts = function($resource){
    return $resource("/clients/client-accounts",{}, {
        'query':{method:'GET',params:{client_account_id:'@client_account_id'},isArray: true}
    }

            );
}
 clientAccounts.$inject = ["$resource"];

var accountSource = function($resource) {
    return $resource("/source/:action",{},{
                create : {method : 'POST', params:{}},
                list:    {method : 'GET', params:{}}
            });
}
 accountSource.$inject = ["$resource"];

var sourceClients = function($resource) {
    return $resource("/client-source/get-clients",{},
    {
        query: {method:'POST',params:{}}
    });
}
 sourceClients.$inject = ["$resource"];

var unmappedClients = function($resource) {
    return $resource("/client/get-unmapped-clients",{},
    {
        query: {method:'POST',params:{}}
    });
}
 unmappedClients.$inject = ["$resource"];

var migration = function($resource) {
    return $resource("/client/:action",{},
    {
        query: {method:'POST',params:{action:'check-migration-status'}},
        done: {method : 'POST', params:{action:'migration-done'}},
    });
}
 unmappedClients.$inject = ["$resource"];

var metricsOptions = function($resource){
    return $resource("/metrics-options",{}, {'query':{method:'GET',params:{channel:'@channel'},isArray: false}});
}

metricsOptions.$inject = ["$resource"];

var reportList = function($resource){
   return $resource("/clients/:client_id/reports/:report_id",{}, {
       // query : {method:'GET',isArray: true},
        update: { method: 'PUT',    params: {report_id: '@report_id'}  },
        delete: { method: 'DELETE', params: {report_id: '@report_id'}  }
    })
}
reportList.$inject = ["$resource"];

var template = function($resource) {
    return $resource ("reports/template/:action", {},{
        create : { method : 'POST', params:{ action : 'save-template'}},
        list : { method : 'POST', params: { action : 'list-templates'}, isArray : true },
        use : { method : 'POST', params: { action : "use-template"}},
        delete : {method: 'POST', params: {action: "delete-template"}}
    });
}

template.$inject = ["$resource"];

var tour = function($resource) {
    return $resource('reports/tour-visited',{tourName : '@tourName', userId : '@userId'}, {
        visitTour : { method : 'POST', action: 'tour-visited'}
    });
}
tour.$inject = ["$resource"];

var braintreePayment = function($resource) {
    return $resource('braintree-payment/:action', {}, {
       getToken : {method : 'POST', params : {action : 'get-token'}},
       subscribe : {method : 'POST', params : {action: 'create-subscription'}},
       updateCustomer : {method : 'POST', params : {action: 'update-customer'}},
       cancelSubscription : {method : 'POST', params : {action: 'cancel-subscription'}},
       viewInvoice : {method: 'GET', params : {action: 'load-invoice'}}
    });
}

var recentReportList = function($resource){
   return $resource("/reports/recent/:agency_id",{},{});
}
recentReportList.$inject = ["$resource"];

var report = function($resource){
   return $resource("/reports/:report_id/:action/:id",{report_id:'@report_id',agency_id:'@agency_id'}, {
        query:{method:'GET',params:{agency_id:'@agency_id'}},
        update: {method:'PUT',params:{report_id:'@report_id'},isArray:false},
        clone:  {method:'POST',params:{report_id:'@report_id',action:'clone'}},
        share:  {method:'POST',params:{report_id:'@report_id',action:'share'}},
        schedule:  {method:'POST',params:{report_id:'@report_id',action:'schedule'}},
        updateSchedule:  {method:'PUT',params:{report_id:'@report_id',action:'schedule'}},
        getShared:     {method:'GET',params:{report_id:'@report_id', action:'share'},isArray: true},
        getScheduled:  {method:'GET',params:{report_id:'@report_id', action:'schedule'},isArray: true},
        removeSharing: {method:'DELETE',params:{report_id:'@report_id', action:'share',id:'@id'}},
        removeSchedule: {method:'DELETE',params:{report_id:'@report_id', action:'schedule',id:'@id'}}
    })
}
report.$inject = ["$resource"];

var insight = function($resource) {
    return $resource("/insights/:action", {}, {
       list : { method : 'POST', params: {action : "insight-list"}},
       widgetInsight : { method : 'POST', params: {action : "widget-insight"}},
       insightOptions : { method : 'POST', params: {action : "insight-options"}},
       saveInsight : { method: 'POST', params: {action : "save-insight"}}
    });
}

var widgetList = function($resource){
   return $resource("/reports/:report_id/widgets",{}, {
        update: { method: 'PUT',    params: {report_id: '@report_id'} },
        delete: { method: 'DELETE', params: {report_id: '@report_id'} }
 })
}
widgetList.$inject = ["$resource"];

var widget = function($resource,$q){
    canceler = $q.defer();

    var res  = $resource("/widget/:widget_id",{widget_id:'@widget_id'}, {
              'get'    :{method:'GET',params:{widget_id:'@widget_id'},timeout:canceler.promise,isArray: false},
              'update' :{method:'PUT',isArray: false},
              
              });

    return res;
}
widget.$inject = ["$resource", "$q"];

var message = function($resource,$q){
    canceler = $q.defer();

    var res  = $resource("/message/:message_id",{message_id:'@message_id'}, {
              'get'    :{method:'GET',params:{message_id:'@message_id'},timeout:canceler.promise,isArray: false},
              'update' :{method:'PUT',isArray: false}
              });

    return res;
}
message.$inject = ["$resource", "$q"];

var messageList = function($resource){
   return $resource("/widget/:widget_id/messages",{}, {
        update: { method: 'PUT',    params: {report_id: '@widget_id'}  },
        delete: { method: 'DELETE', params: {report_id: '@widget_id'}  }
    })
}
messageList.$inject = ["$resource"];



var coworker = function($resource){
    return $resource("/coworker/:coworker_id",{}, {'query':{method:'GET',params:{coworker_id:'@coworker_id'},isArray: true}});
}
coworker.$inject = ["$resource"];

var currentReport = function($rootScope){

  return {
    report:null,
    sources:null,
    setSources:function(sources){
      this.sources = sources;
    },
    getSources:function(){
      return this.sources;
    },
    setReport:function(report){
      this.report = report;
    },
    getReport:function(){
      return this.report;
    }
  }
}
currentReport.$inject = ["$rootScope"];


var flashMessage = function($rootScope) {
  var queue = [];
  var currentMessage = "";


  return {
    message:'',
    icon_font:'',
    bg:'',
    header:'',
    setMessage: function(message) {
      if(message.success){
        this.icon_font = 'font-blue';
        this.bg = 'bg-blue';
        this.header='Success!';
      } else {
        this.icon_font = 'font-red';
        this.bg        = 'bg-red';
        this.header    = 'Oops!';
      }
      this.message = message.message;
    },
    getMessage: function() {
      var msg = this.message;
      this.message = '';
      return msg;
    },
    getBg:function(){
      return this.bg;
    },
    getIconFont:function(){
      return this.icon_font;
    },
    getHeaderMessage:function(){
      return this.header;
    }

  };
};
flashMessage.$inject = ["$rootScope"];
var appAuthorization = function($q,$rootScope,$timeout,ClientAccounts,ClientSourceList,$routeParams){
  var appAuth;

  appAuth =  {
      authorized:false,
      re_authorized:false,
      channel:null,
      timeout:null,
      authWin:null,
      reauthWin:null,
      defer:null,
      check_authorization : function() {

        if(!appAuth.defer){
            appAuth.defer  = $q.defer();
        }

        appAuth.timeout = $timeout(appAuth.check_authorization, 1000);

        if(angular.isDefined(appAuth.authWin.authorized)){

            appAuth.authorized= appAuth.authWin.authorized;
            if(appAuth.authorized){
              appAuth.authWin.close();

              if(appAuth.defer){
                 $rootScope.$broadcast('authorized');
                 appAuth.defer.resolve();
              }

              $timeout.cancel(appAuth.timeout);

            } else
              appAuth.defer.reject();
        }

        return appAuth.defer.promise;
     },
     check_reauthorization : function() {
       appAuth.timeout = $timeout(appAuth.check_reauthorization, 1000);

        if(angular.isDefined($rootScope.reauthorization_window)){
            $rootScope.re_authorized  = $rootScope.reauthorization_window.re_authorized;
            appAuth.re_authorized     = $rootScope.re_authorized;

          if($rootScope.re_authorized) {
            $timeout.cancel(appAuth.timeout);
            $rootScope.reauthorization_window.close();

            appAuth.defer.resolve();
          }
          return appAuth.defer.promise;
        }
     }
  }

  return appAuth;
}
appAuthorization.$inject = ["$q", "$rootScope", "$timeout", "ClientAccounts",
                            "ClientSourceList", "$routeParams"];

JimmyDashboard.factory('GeneralInfo', generalInfo);
JimmyDashboard.factory('Blog', blog);
JimmyDashboard.factory('User', user);
JimmyDashboard.factory('ClientList', clientList);
JimmyDashboard.factory('Client', client);
JimmyDashboard.factory('ReportList', reportList);
JimmyDashboard.factory('Report', report);
JimmyDashboard.factory('WidgetList', widgetList);
JimmyDashboard.factory('RecentReports', recentReportList);
JimmyDashboard.factory('CampaignList', campaignList);
JimmyDashboard.factory('MetricsOptions', metricsOptions);
JimmyDashboard.factory('Widget', widget);
JimmyDashboard.factory('ClientAccounts', clientAccounts);
JimmyDashboard.factory('ClientSourceList', clientSourceList);
JimmyDashboard.factory('ClientSource', clientSource);
JimmyDashboard.factory('ProfileList', profileList);
JimmyDashboard.factory('SegmentList', segmentList);
JimmyDashboard.factory('GoalsList', goalsList);
JimmyDashboard.factory('PackageList', packageList);
JimmyDashboard.factory('Coworker', coworker);
JimmyDashboard.factory('FlashMessage', flashMessage);
JimmyDashboard.factory('AppAuth', appAuthorization);
JimmyDashboard.factory('CurrentReport', currentReport);
JimmyDashboard.factory('MessageList', messageList);
JimmyDashboard.factory('Message', message);
JimmyDashboard.factory('Tour', tour);
JimmyDashboard.factory('Template', template);
JimmyDashboard.factory('AccountSource', accountSource);
JimmyDashboard.factory('SourceClients', sourceClients);
JimmyDashboard.factory('UnmappedClients', unmappedClients);
JimmyDashboard.factory('Migration', migration);
JimmyDashboard.factory('BraintreePayment', braintreePayment);
JimmyDashboard.factory('ActivityLog', activityLog);
JimmyDashboard.factory('Insight', insight);
// The core natural service
JimmyDashboard.factory("naturalService", ["$locale", function($locale) {
    "use strict";
        // the cache prevents re-creating the values every time, at the expense of
        // storing the results forever. Not recommended for highly changing data
        // on long-term applications.
    var natCache = {},
        // amount of extra zeros to padd for sorting
        padding = function(value) {
            return "00000000000000000000".slice(value.length);
        },

        // Converts a value to a string.  Null and undefined are converted to ''
        toString = function(value) {
            if(value === null || value === undefined) return '';
            return ''+value;
        },

        // Calculate the default out-of-order date format (dd/MM/yyyy vs MM/dd/yyyy)
        natDateMonthFirst = $locale.DATETIME_FORMATS.shortDate.charAt(0) === "M",
        // Replaces all suspected dates with a standardized yyyy-m-d, which is fixed below
        fixDates = function(value) {

            // first look for dd?-dd?-dddd, where "-" can be one of "-", "/", or "."
            return toString(value).replace(/(\d\d?)[-\/\.](\d\d?)[-\/\.](\d{4})/, function($0, $m, $d, $y) {
                // temporary holder for swapping below
                var t = $d;
                // if the month is not first, we'll swap month and day...
                if(!natDateMonthFirst) {
                    // ...but only if the day value is under 13.
                    if(Number($d) < 13) {
                        $d = $m;
                        $m = t;
                    }
                } else if(Number($m) > 12) {
                    // Otherwise, we might still swap the values if the month value is currently over 12.
                    $d = $m;
                    $m = t;
                }
                // return a standardized format.
                return $y+"-"+$m+"-"+$d;
            });
        },

        // Fix numbers to be correctly padded
        fixNumbers = function(value) {

        //value.replace(/(\d+)((\,)*)?((\.\d+)+)?/g

            // First, look for anything in the form of d.d or d.d.d...
            return value.replace(/((((\d+),)+)*(\d+)(\.(\d+))?)/g, function ($0, integer, decimal, $3) {
                // If there's more than 2 sets of numbers...
                //if(decimal==',')

                if(decimal==','){
                    return $0.replace(/(\,+)/g, function ($d) {
                        return $d + $d;
                    });
                }

                 if (decimal !== $3) {
                    // treat as a series of integers, like versioning,
                    // rather than a decimal
                    return $0.replace(/(\d+)/g, function ($d) {
                        return padding($d) + $d;
                    });
                } else {
                    // add a decimal if necessary to ensure decimal sorting
                    decimal = decimal || ".0";
                    return padding(integer) + integer + decimal + padding(decimal);
                }
            });
        },

        // Finally, this function puts it all together.
        natValue = function (value) {
            if(natCache[value]) {
                return natCache[value];
            }
            natCache[value] = fixNumbers(fixDates(value));
            return natCache[value];
        };

    // The actual object used by this service
    return {
        naturalValue: natValue,
        naturalSort: function(a, b) {
            a = natVale(a);
            b = natValue(b);
            return (a < b) ? -1 : ((a > b) ? 1 : 0);
        }
    };
}])


JimmyDashboard.factory('AuthService', ['$q','$location','$rootScope','GeneralInfo','PackageList',function($q,$location,$rootScope,GeneralInfo,PackageList){
    var dateDifferenceInDays = function(dateSmall, dateLarge) {
      var one_day=1000*60*60*24;

      // Convert both dates to milliseconds
      var date1_ms = dateSmall.getTime();
      var date2_ms = dateLarge.getTime();

      // Calculate the difference in milliseconds
      var difference_ms = date2_ms - date1_ms;

      // Convert back to days and return
      return Math.round(difference_ms/one_day);
    }

    return {
           isAuthenticated:function(roles) {
               var defer = $q.defer();


               if(!angular.isDefined($rootScope.generalInfo)){
                    $rootScope.generalInfo = GeneralInfo.query(function(generalInfo) {
                        if($rootScope.generalInfo.account_state == "cancelled") {
                             $location.path('/account-cancelled');
                             return;
                        }

                      // migration of users to braintree.
                      // check if user is on a standard package
                      if($rootScope.generalInfo.current_user.type !="user" &&
                              $rootScope.generalInfo.package.type == 'standard') {

                      // console.log('standard');
                        // if on standard
                        // Check if user is not on trial
                        if($rootScope.generalInfo.package.is_free_trial==0) {
                          console.log('not trial');
                          // check if current user is an agency or coworker
                          if($rootScope.generalInfo.current_user.type == 'agency') {
                            console.log('agency');
                            // check if user has an no braintree subscription
                            if($rootScope.generalInfo.current_user.credit_card) {
                           //   console.log('has card');
                              // check if user is subscribed to braintree
                              if(!$rootScope.generalInfo.current_user.credit_card.subscription) {
                              //  console.log('non-braintree');
                                $rootScope.migrationRequired = true;
                                $rootScope.migrate = true;
                                $location.path('/payment-update');
                              } else {
                              //  console.log('braintree');
                                // check if current status of user subscription is not active or pending
                                if(["Active", "Pending"].indexOf($rootScope.generalInfo.current_user.credit_card.subscription.status)==-1) {
                               //   console.log('card failed');
                                  $rootScope.migrationRequired = true;
                                  $rootScope.inactiveSubscription = true;
                                  $location.path('/payment-update');
                                }
                              }
                            }
                          } else if($rootScope.generalInfo.current_user.type == 'coworker') {
                            console.log($rootScope.generalInfo.parent);
                            // check if user has an no braintree subscription
                            if($rootScope.generalInfo.parent.credit_card) {
                              if(!$rootScope.generalInfo.parent.credit_card.subscription) {
                             //   console.log('update');
                                $rootScope.migrationRequired = true;
                                $location.path('/payment-update');
                              }
                            }
                          }
                        } else {
                          // if on trial
                          // check if user is on 14 day trial and trial has not expired
                          console.log('trial');
                          if($rootScope.generalInfo.package.id==16) {
                            var created = $rootScope.generalInfo.current_user.created;
                            if($rootScope.generalInfo.current_user.type == 'coworker') {
                                created = $rootScope.generalInfo.parent.created;

                            }
                            var diff = dateDifferenceInDays(new Date(created), new Date())
                            console.log('diff '+diff);
                            // check if trial expired
                            if(diff>14) {
                              $rootScope.migrationRequired = true;
                              $rootScope.trialExpired = true;
                              $location.path('/payment-update');
                            }
                          }
                        }
                      } else {
                        // if not on standard
                        // do nothing
                        console.log('not standard');
                      }


                        if(angular.isArray(roles) && $.inArray(generalInfo.current_user.type,roles)!=-1)
                          defer.resolve(generalInfo);
                        else
                          defer.reject(generalInfo);

                        // PackageList.get({package_id:1},function(unlimited_package){ // package_id can be any random number
                        //   $rootScope.unlimited_package = unlimited_package;
                        // });


                    },function(data){
                      defer.reject(generalInfo);
                    });



                } else {

                    if(angular.isArray(roles) && $.inArray($rootScope.generalInfo.current_user.type,roles)!=-1){
                      defer.resolve($rootScope.generalInfo);
                    }
                    else
                      defer.reject($rootScope.generalInfo);
                      //check if account cancelled.

                        if($rootScope.generalInfo.account_state == "cancelled") {
                             $location.path('/account-cancelled');
                            return;
                        }
                      // migration of users to braintree.
                      // check if user is on a standard package
                      if($rootScope.generalInfo.current_user.type != 'user' && $rootScope.generalInfo.package.type == 'standard') {
                        console.log('standard');
                        // if on standard
                        // Check if user is not on trial
                        if($rootScope.generalInfo.package.is_free_trial==0) {
                          console.log('not trial');
                          // check if current user is an agency or coworker
                          if($rootScope.generalInfo.current_user.type == 'agency') {
                            console.log('agency');
                            // check if user has an no braintree subscription
                            if($rootScope.generalInfo.current_user.credit_card) {
                              console.log('has card');
                              // check if user is subscribed to braintree
                              if(!$rootScope.generalInfo.current_user.credit_card.subscription) {
                                console.log('non-braintree');
                                $rootScope.migrationRequired = true;
                                $rootScope.migrate = true;
                                $location.path('/payment-update');
                              } else {

                                // check if current status of user subscription is not active or pending
                                if(["Active", "Pending"].indexOf($rootScope.generalInfo.current_user.credit_card.subscription.status)==-1) {
                                  console.log('card failed');
                                  $rootScope.migrationRequired = true;
                                  $rootScope.inactiveSubscription = true;
                                  $location.path('/payment-update');
                                }
                              }
                            }
                          } else if($rootScope.generalInfo.current_user.type == 'coworker') {
                            console.log('coworker');
                            // check if user has an no braintree subscription
                            if($rootScope.generalInfo.parent.credit_card) {
                              if(!$rootScope.generalInfo.parent.credit_card.subscription) {
                                console.log('update');
                                 $rootScope.migrate = true;
                                $rootScope.migrationRequired = true;
                                $location.path('/payment-update');
                              }
                            }
                          }
                        } else {
                          // if on trial
                          // check if user is on 14 day trial and trial has not expired
                          console.log('trial');
                          if($rootScope.generalInfo.package.id==16) {
                            var created = $rootScope.generalInfo.current_user.created;
                            if($rootScope.generalInfo.current_user.type == 'coworker') {
                                created = $rootScope.generalInfo.parent.created;
                            }
                            var diff = dateDifferenceInDays(new Date(created), new Date());

                            console.log('diff '+diff);
                            // check if trial expired
                            if(diff>14) {
                              $rootScope.migrationRequired = true;
                              $rootScope.trialExpired = true;
                              $location.path('/payment-update');
                            }
                          }
                        }
                      } else {
                        // if not on standard
                        // do nothing
                        console.log('not standard');
                      }

                }

                return defer.promise;
            }
    }
}])

JimmyDashboard.factory('ReportUpgradeService', ['$rootScope', 'PackageList', 'FlashMessage', 'Report', function($rootScope, PackageList, FlashMessage, Report){
  $rootScope.$on('billing-info-save', function(success) {
    if(success) {
      $('#billing-info-dialog').dialog('destroy');
      // display a message
      FlashMessage.setMessage({message:'Report upgraded.', success:true});
    } else {
      // display message
      FlashMessage.setMessage({message:'Billing information could not be saved. Report not upgraded.', success:true});
    }
  });
  return {
    handleUpgrade: function(report){
      var upgrade_dialog =  $('#upgradeAlertDialog').dialog('open');
      $ ('.ui-widget-overlay').addClass('bg-black opacity-60');
    }
  };
}])

JimmyDashboard.config(["$routeProvider", function($routeProvider) {

    var bootstrap =  ['$q','AuthService',function($q, $rootScope, AuthService,GeneralInfo,$route) {
          $rootScope.sideBarOptions = false;
          $rootScope.showSearch = false;
       if( $route.current.$$route.originalPath == "/clients" ||
           $route.current.$$route.originalPath == "/reports" ||
           $route.current.$$route.originalPath =="/clients/:client_id/reports"){
          $rootScope.showSearch = true;
       }
       if ($route.current.$$route.originalPath=="/report/:report_id") {

                $rootScope.sideBarOptions = true;

       }
       return AuthService.isAuthenticated($route.current.roles);
    }];



  $routeProvider.
        when('/',                               {controller:"DashboardCtrl",  templateUrl:'src/app/dashboard/index.html',resolve:bootstrap,roles:['user','agency','coworker']}).
        when('/user',                           {controller:"UserCtrl",       templateUrl:'src/app/dashboard/account.html',resolve:bootstrap,roles:['user','agency','coworker']}).
        when('/payment-update',                 {controller:"UserCtrl",       templateUrl:'src/app/dashboard/payment-updation.html',resolve:bootstrap,roles:['user','agency','coworker']}).
         when('/account-cancelled',             {controller:"UserCtrl",       templateUrl:'src/app/dashboard/account-cancelled.html',resolve:bootstrap,roles:['user','agency','coworker']}).
        when('/upgrade-successful',             {controller:"UpgradeCtrl",    templateUrl:'src/app/dashboard/upgrade-successful.html',resolve:bootstrap,roles:['agency']}).
        when('/clients',                        {controller:"ClientListCtrl", templateUrl:'src/app/client/list.html',resolve:bootstrap,roles:['agency','coworker']}).
        when('/clients/:client_id',             {controller:"ClientCtrl",     templateUrl:'src/app/client/index.html',resolve:bootstrap,roles:['agency','coworker']}).
        when('/clients/:client_id/reports',     {controller:"ReportListCtrl", templateUrl:'src/app/report/list.html',resolve:bootstrap,roles:['agency','coworker']}).
        when('/reports/:list',                  {controller:"ReportListCtrl", templateUrl:'src/app/report/shared-list.html',resolve:bootstrap,roles:['agency','coworker']}).
        when('/report/new/',                    {controller:"NewReportCtrl",  templateUrl:'src/app/report/form.html',resolve:bootstrap,roles:['agency','coworker']}).
        when('/report/new-report',              {controller:"CreateReportCtrl",templateUrl:'src/app/report/new-report.html',resolve:bootstrap,roles:['agency','coworker']}).
        when('/report/:report_id',              {controller:"ReportCtrl",     templateUrl:'src/app/report/index.html',resolve:bootstrap,roles:['user','agency','coworker']}).
        when('/report/:report_id/widget/new',   {controller:"NewReportCtrl",  templateUrl:'src/app/report/form.html',resolve:bootstrap,roles:['agency','coworker'],action:'new-widget'}).
        when('/report/:report_id/widget/new-widget',{controller:"NewWidgetCtrl",  templateUrl:'src/app/report/new-widget.html',resolve:bootstrap,roles:['agency','coworker'],action:'new-widget'}).
        when('/reports',                        {controller:"ReportListCtrl", templateUrl:'src/app/report/list.html',resolve:bootstrap,roles:['agency','coworker']}).
        when('/reports/:report_id/widgets/:action/:type',   {controller:"WidgetCtrl",     templateUrl:'src/app/client/view.html',resolve:bootstrap,roles:['agency','coworker']}).
        when('/coworker',                       {controller:"CoworkerCtrl",   templateUrl:'src/app/coworker/list.html',resolve:bootstrap,roles:['agency']}).
        otherwise({redirectTo:'/'});
}]);


JimmyDashboard.run(["$rootScope", "$location", "GeneralInfo", "naturalService", function ($rootScope, $location,GeneralInfo,naturalService) {

    $rootScope.$on("$routeChangeError",function(event, next, current){
        console.log("Unauthorised Access");
        event.preventDefault();
        $location.path("#/");
        return false;
    });

    $rootScope.$on("$routeChangeStart",function(event, next, current){




          if(widgetCanceler)  {
            widgetCanceler.resolve();
            widgetCanceler  = null;
          }

          if(campaignCanceler) {
            campaignCanceler.resolve();
            campaignCanceler = null;
          }

          if(profileCanceler){
            profileCanceler.resolve();
            profileCanceler  = null;
          }

        event.preventDefault();

        if(widgetCanceler)
          return widgetCanceler.promise;
        else
          return true;
    });

    $rootScope.natural = function (field) {
        return function (item) {
           return naturalService.naturalValue(item[field]);
        }
    };

}]);

JimmyDashboard.controller("MainCtrl",["$scope", "$rootScope", "$timeout","$location", "FlashMessage", "GeneralInfo", function($scope,$rootScope,$timeout, $location, FlashMessage,GeneralInfo){
      $scope.flashMessage = FlashMessage;

      $scope.$on('report_deleted',function(){
        $rootScope.generalInfo.templates_used--;
        $rootScope.templates_used_perc = ($rootScope.generalInfo.templates_used/$rootScope.generalInfo.package.templates_allowed)*100;
      });

      $scope.$on('report_cloned',function(){
        $rootScope.generalInfo.templates_used++;
        $rootScope.templates_used_perc = ($rootScope.generalInfo.templates_used/$rootScope.generalInfo.package.templates_allowed)*100;
      });

      $scope.$on("settings_updated",function(e,settings){
        var    logo = "/images/"+$rootScope.generalInfo.logo_config.logo_url;

          if(settings && settings.replace_app_logo){
             logo = "/resources/logos/agencies/" + $rootScope.generalInfo.current_user.logo;
              $("#header-logo").css("background","url(" +logo+ ")  no-repeat -30px");
              $("#header-logo").css("background-size","100%");
              $("#header-logo img").remove();
          }
      });

      $scope.$on('$locationChangeSuccess', function () {

        $scope.locationParams = $location.path().split('/');

      })

      $scope.$on('breadcrumbs_ready', function (event, data) {
        angular.forEach(data.crumbs ,function(value,key) {
        if (value.title.length>20) {
            data.crumbs[key].title = value.title.substring(0, 17)+"...";
        }
      });


        $scope.crumbs = data.crumbs;
      });

      $scope.createReport = function() {
           $location.path("/report/new-report");
      }

}]);

JimmyDashboard.controller("UserCtrl",[
  "$q", "$scope", "$rootScope", "$resource", "$location", "$http", "GeneralInfo", "ClientAccounts", "FlashMessage",
   "Upload", "$timeout", "BraintreePayment",
  function($q,$scope,$rootScope,$resource,$location,$http,GeneralInfo,ClientAccounts,FlashMessage,
   Upload, $timeout, BraintreePayment){

    var downloads = 0;

    $scope.changepass = {user_id:$scope};
    $scope.settings   = {};
    $scope.ccinfo     = {};
    $scope.isInvoiceDisplayed = false;

    GeneralInfo.query(function(generalInfo){
      $scope.user = generalInfo.current_user;
      $scope.changepass.user_id = $scope.user.id;
      $scope.settings = generalInfo.settings;
      $scope.invoices = generalInfo.invoices;
      $scope.creditCard = generalInfo.current_user.credit_card;
      $scope.billingReportsCount = generalInfo.templates_used;
      if($scope.user==undefined || $scope.user.logo==null) {
        $scope.isLogoSet = false;
      } else {
        $scope.isLogoSet = true;
      }
      if($scope.user==undefined || $scope.user.logo==null) {
        $scope.isCompanyLogoSet = false;
      } else {
        $scope.isCompanyLogoSet = true;
      }
    });

    $scope.isAgency = function() {
      return $rootScope.generalInfo.current_user.type == 'agency';
    }

    $scope.isCoworker = function() {
      return $rootScope.generalInfo.current_user.type == 'coworker';
    }

    $scope.$on('$locationChangeSuccess', function() {
      var locationParams = $location.path().split('/');
      if(locationParams[1].indexOf('user')!=-1) {
        $scope.$emit('breadcrumbs_ready', {
          crumbs: [
            {
              title: 'Dashboard',
              url: '/',
              class: ''
            },
            {
              title: 'User',
              url: '/user',
              class: 'active'
            }
          ]
        });
      }
    })

    $scope.changePassword = function(){

        if($("#password_form").parsley('validate')){

            $http({
                method: 'POST',
                url: "/changepwd",
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                transformRequest: function (data) {

                    var formData = $.param(data);
                    return formData;
                },
                data: $scope.changepass
            }).
            success(function (data, status, headers, config) {
                FlashMessage.setMessage(data);
                $scope.changepass.password   = '';
                $scope.changepass.c_password = '';
            }).
            error(function (data, status, headers, config) {
                FlashMessage.setMessage(data);
            });

        }

    }


    $scope.updateTitle = function(name){

          return $resource('/user/save-title/:user_id', {},{update:{method:'post',headers: {'Content-Type': 'application/x-www-form-urlencoded'}}})
                          .update({user_id:$scope.user.id},$.param({name:name}));
    }


    $scope.uploadFinish = function(data){
        $rootScope.generalInfo = GeneralInfo.query(function(data){
            $scope.$emit("settings_updated",data.settings);
        });
    }

    $scope.saveSettings = function(){
        $http({
                  method: 'POST',
                  url: "/settings/save",
                  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                  transformRequest: function (data) {
                      var formData = $.param(data);
                      return formData;
                  },
                  data: $scope.settings
              }).
              success(function (data, status, headers, config) {
                  FlashMessage.setMessage(data);
                  $scope.$emit("settings_updated",data.settings);

              }).
              error(function (data, status, headers, config) {
                  FlashMessage.setMessage(data);
              });
    }


    $scope.cancelAccount=function(){
             $http({
                  method: 'POST',
                  url: "/cancel",
                  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              }).
              success(function (data, status, headers, config) {
                // Braintree and Eway cancellation had already occurred
                if(data.success==true){
                  FlashMessage.setMessage(data);
                  location.href = '/user/logout';
                }
              }).
              error(function (data, status, headers, config) {
                  FlashMessage.setMessage(data);
              });
    }

    $scope.showUpgrade = function() {
      $("#settings-agency").accordion("option", "active", 3);
      $('body').animate({scrollTop:$('.paymentInfoSettings').offset().top}, 'slow');
    }

    $scope.getTrialExpiryDate = function(createdDateString) {
      var createdDate = new Date(createdDateString);
      var expriryDate = new Date(createdDateString);
      expriryDate.setDate(createdDate.getDate() + 14);
      return expriryDate;
    }

    $scope.toggleInvoiceDisplay = function() {
      $scope.isInvoiceDisplayed = !$scope.isInvoiceDisplayed;
      if($scope.isInvoiceDisplayed) {
        BraintreePayment.viewInvoice(function(data) {
          console.log(data.data);
          if(data.success) {
            $scope.invoices = data.data;
          }
        });
      }
    }

    q  = $q.defer();

    $scope.downloadInvoice = function(invoiceId) {
      var iframe = $("#download-invoice");
      var ProgressBarScope = angular.element("#progress-bar").scope();

      downloads = downloads+1;

      if(downloads>1){
         // ProgressBarScope.stop({message:'Download Cancelled'});
         q.resolve(); // resolve previous promise
         q = $q.defer(); // create a new one
         // ProgressBarScope.start({message:'Downloading Report ( ' + invoiceId + " )"});
       } else
         // ProgressBarScope.start({message:'Downloading Report ( ' +  invoiceId + " )"});
      $http({
          url: '/braintree-payment/download/' + invoiceId,
          method: "GET",
          timeout:q.promise
      }).
      success(function(data, status, headers, config) {
          FlashMessage.setMessage(data);

          if(data.success){
            iframe.attr('src',data.file);
            downloads = 0;
            // ProgressBarScope.complete({message:'Download Complete'});
          } else {
            // ProgressBarScope.stop({message:'Download Cancelled'});
          }
      }).
      error(function(data, status, headers, config) {
        downloads = 0;
        FlashMessage.setMessage({message:'Download Cancelled',success:false});
      });
    }

    $scope.removeLogo = function() {
              $http({
                method: 'POST',
                url: '/user/remove-logo',
                data: {
                  user_id: $scope.user.id
                }
              }).success(function (data, status, headers, config) {
                $timeout(function() {
                  // sets isLogoSet so as to hide the remove btn
                  $scope.isCompanyLogoSet = false;
                  // sets the orange background to JimmyData logo.
                  $("#header-logo").css("background","#faa71a");
                  // update the settings.
                  $scope.uploadFinish(data);
                })
              })
    }

    $scope.removeThumb = function() {
              $http({
                method: 'POST',
                url: '/user/remove-thumb',
                data: {
                  user_id: $scope.user.id
                }
              }).success(function (data, status, headers, config) {
                $timeout(function() {
                  // sets isLogoSet so as to hide the remove btn
                  $scope.isLogoSet = false;
                  // sets the orange background to JimmyData logo.
                  $("#header-logo").css("background","#faa71a");
                  // update the settings.
                  $scope.uploadFinish(data);
                })
              })
    }

    $scope.savePaymentInfo = function(){

      if($("#cc-recurring-form").parsley( 'validate' )){
        $scope.saveDisabled = true;

        BraintreePayment.getToken(function(data) {
          var client = new braintree.api.Client({
          clientToken: data.token
          });
          client.tokenizeCard({
          number: $scope.ccinfo.cc_number,
          expirationDate: $scope.ccinfo.cc_exp_month+'/'+$scope.ccinfo.cc_exp_year
          }, function(err, nonce) {
            var userInfo = {
              firstname: $scope.ccinfo.firstname,
              lastname: $scope.ccinfo.lastname,
              streetaddress: $scope.ccinfo.billing.address,
              state: $scope.ccinfo.billing.state,
              country: $scope.ccinfo.billing.country,
              postalCode: $scope.ccinfo.billing.zipcode,
            }

            // Check whether user has credit card and is subscribed to Braintree
            if(!!$rootScope.generalInfo.current_user.credit_card &&
                !!$rootScope.generalInfo.current_user.credit_card.subscription) {
              BraintreePayment.updateCustomer({
                userInfo: userInfo,
                nonce: nonce
              }, function(data) {

                if(!data.success) {
                  FlashMessage.setMessage(data);
                } else {

                  FlashMessage.setMessage(data);

                  $scope.$emit('billing-info-save', true);

                  $scope.billing = {};
                  $scope.cc = {};
                  if($('#upgradePackageDialog').dialog('isOpen')==true) {
                    $('#upgradePackageDialog').dialog('close');
                  }
                  $scope.isOnBillingAddress = true;

                  GeneralInfo.query(function(generalInfo){
                    $rootScope.generalInfo =  generalInfo;
                    $scope.creditCard = $rootScope.generalInfo.current_user.credit_card;
                  });

                  $rootScope.$broadcast('upgradation_successful');

                  if($rootScope.migrationRequired) {
                    console.log('migration done');
                    $rootScope.generalInfo = GeneralInfo.query(function(data){
                        $scope.$emit("settings_updated",data.settings);
                    });
                    $rootScope.migrationRequired = false;
                    $location.path('#/');
                  }
                }
                if($rootScope.migrationRequired) {
                  $rootScope.migrationRequired=false;
                  $location.path('#/user');
                }
                $scope.saveDisabled = false;
              });

            } else {

              BraintreePayment.subscribe({
                userInfo: userInfo,
                nonce: nonce
              }, function(data) {

                if(!data.success) {
                  FlashMessage.setMessage({message:'Failed to add card.', success:false});
                } else {
                      _kmq.push(['record', 'Submit Payment Button']);
                  FlashMessage.setMessage({message:'Card and billing information saved.', success:true});

                  $scope.$emit('billing-info-save', true);

                  $scope.billing = {};
                  $scope.cc = {};
                  if($('#upgradePackageDialog').dialog('isOpen')==true) {
                    $('#upgradePackageDialog').dialog('close');
                  }
                  $scope.isOnBillingAddress = true;

                  GeneralInfo.query(function(generalInfo){
                    $rootScope.generalInfo =  generalInfo;
                    $scope.creditCard = $rootScope.generalInfo.current_user.credit_card;
                  });

                  $rootScope.$broadcast('upgradation_successful');

                  if($rootScope.migrationRequired) {
                    $rootScope.generalInfo = GeneralInfo.query(function(data){
                        $scope.$emit("settings_updated",data.settings);
                    });
                    console.log('migration done');
                    $rootScope.migrationRequired = false;
                    $location.path('#/');
                  }
                }
                $scope.saveDisabled = false;
              });
            }
          });
        });
      }
        }

    // listens files to see whether new files are added
    // if added uploads them
    $scope.$watch('files', function () {
        $scope.upload($scope.files);
    })

    // checks whether new file is added to be uploaded
    // if added moves it to the files array
    $scope.$watch('file', function () {
        if ($scope.file != null) {
            $scope.upload($scope.file,'upload-thumb');
        }
    })

    // checks whether new file is added to be uploaded
    // if added moves it to the files array
    $scope.$watch('companyLogo', function () {
        if ($scope.companyLogo != null) {
            $scope.upload($scope.companyLogo, 'upload-logo');

        }
    })

    // function that uploads the file to the server
    $scope.upload = function (file,url) {
      // check if file actually exists and has no error
      if (file && !file.$error) {
        Upload.upload({
            url: '/user/'+url,
            data: {
              file: file,
              user_id: $scope.user.id
            }
        }).success(function (data, status, headers, config) {
            $timeout(function() {
                // set isLogoSet to display show hide btn
                if(url=='upload-logo') {
                  $scope.isCompanyLogoSet = true;

                } else if(url=='upload-thumb') {
                  $scope.isLogoSet = true;
                }
                // update settings
                $scope.uploadFinish(data);
                // TODO: Change this to something more accurate.
                // client_source_added event closes all opened dialogs.
                $scope.$emit('client_source_added');
            });
        });
      }
    }
}]);


JimmyDashboard.controller("DashboardCtrl",["$q", "$scope","Tour", "$rootScope", "$location", "$timeout",
    "$http", "$resource", "Client", "ClientList", "GeneralInfo", "RecentReports", "ReportList", "Report",
    "Blog", "FlashMessage", "PackageList", "Migration", "ActivityLog", function($q,$scope,Tour,$rootScope,$location,$timeout,$http,
    $resource,Client,ClientList,GeneralInfo,RecentReports,ReportList,Report,Blog,FlashMessage,
    PackageList, Migration, ActivityLog){
  
    $scope.$emit('breadcrumbs_ready', {
      crumbs:[
      ]
    });
    $scope.activityLog = [];
    $scope.limit = 10;
    ActivityLog.query({"limit":$scope.limit},function(data) {
        if (data.success) {
            $scope.activityLog = data.logData;
        }

    });
    
    if($rootScope.generalInfo.current_user.type=='agency' || $rootScope.generalInfo.current_user.type=='coworker'){


        RecentReports.query({list:'recent' },function(recentReports){
           $scope.recentReports  = recentReports;


        });
        Migration.query(function(data){
            if (data.required) {
                  $("#migrateBtn").trigger("click");
            }
        });

        ClientList.query({list:'recent'},function(clients){
           $scope.clients        = clients;
               var userId = $rootScope.generalInfo.current_user.id;
                //    if ($rootScope.generalInfo.current_user.type != 'user') {
                //     Tour.visitTour({tourName : "create report" ,userId: userId }, function(data) {
                //         if (!data.visited) {
                //             createTourSteps =  [
                //                 {'next .create-report-btn' : "To get going, just Create New Report"}

                //             ]
                //          var createTour = new EnjoyHint({});
                //              createTour.set(createTourSteps);
                //              createTour.run();
                //         }
                //     });
                // }
        });

        $scope.template       = 'src/app/dashboard/agency.html';

        Blog.query(function(blog){
            $scope.posts  = blog.posts;
        });

        if($rootScope.generalInfo.package!=null){
            //If templates_allowed has value its a normal package else it is unlimited
            if($rootScope.generalInfo.package.id == $rootScope.generalInfo.unlimited_package.id || $rootScope.generalInfo.package.id == 13)// very fragile way of doing this. Fix when you get a chance
            {
               $scope.banner_text         = "Want Jimmy Whitelabelled on your server?";
               $scope.templates_msg       =  $rootScope.generalInfo.templates_used + " out of unlimited templates used";
               $scope.circle_text         =  null;
            } else {
               $scope.banner_text         = "You are currently on a trial";
               $scope.templates_msg       = $rootScope.generalInfo.templates_used + " out of " +  $rootScope.generalInfo.package.templates_allowed +  " templates used";
               $scope.circle_text         = $rootScope.generalInfo.days_left+' days left in your trial';
               $scope.templates_used_perc = ($rootScope.generalInfo.templates_used/$rootScope.generalInfo.package.templates_allowed)*100;
            }

          }

    } else {
        $rootScope.showSearch  = true;
        $scope.reports         = ReportList.query();
        $scope.template        = 'src/app/dashboard/user.html';
    }

    $scope.$on('client_saved',function(e,client_id){
        $scope.clients          = ClientList.query({list:'recent'});
        if(!!$rootScope.moveToReportCreation) {
          $location.path('/report/new-report')
        } else {
          $location.path("/clients/" + client_id+"/reports")
        }

    });

    $scope.$on("report_cloned",function(){
        $scope.recentReports  = RecentReports.query({list:'recent' });
    })


    // When the Sharing is removed completely from a particular report update the model
    $scope.$on("sharing_removed",function(e,data){
        if(data.length==1){
           _.filter($scope.recentReports,function(report){
                 if(data[0].report_id==report.id)
                    report.shared = false;
          });
        }
    })

    //When the Sharing is added to a particular report update the model
    $scope.$on("report_shared",function(e,data){
        if(data.length==1){
           _.filter($scope.recentReports,function(report){
                 if(data[0].report_id==report.id)
                    report.shared = true;
          });
        }
    })


    // When the Schedule is removed completely from a particular report update the model
    $scope.$on("schedule_removed",function(e,data){
        if(data.length==1){
           _.filter($scope.recentReports,function(report){
                 if(data[0].report_id==report.id)
                    report.scheduled = false;
          });
        }
    })

    //When the Schedule is added to a particular report update the model
    $scope.$on("scheduled",function(e,data){
        if(data.length==1){
           _.filter($scope.recentReports,function(report){
                 if(data[0].report_id==report.id)
                    report.scheduled = true;
          });
        }
    })



    $scope.deleteClient = function(client_id){

        Client.delete({client_id:client_id},function(data){
            FlashMessage.setMessage(data);

            if(data.success==true){
              $scope.clients        = ClientList.query({list:'recent'});
            }
       });
    }

    $scope.delete = function(report_id, title) {
        var   deleteScope = angular.element($( "#delete-report")).scope();
        deleteScope.report_id   = report_id;
        deleteScope.$apply(function() {
            deleteScope.delReport = {};
            deleteScope.delReport.title  = title;
        });
        deleteScope.showDialog();

            $scope.$on('report_deleted', function(e, args) {
                 $scope.recentReports  = RecentReports.query({list:'recent' });
            });

    }

}]);
function OtherController($scope) {
  $scope.pageChangeHandler = function(num) {
    console.log('going to page ' + num);
  };
}
JimmyDashboard.controller("DeleteReportCtrl", ["$scope","FlashMessage",
    "Report", function($scope, FlashMessage, Report)
    {   var delete_dialog;
        $scope.showDialog = function() {
                delete_dialog =  $("#delete-report").dialog({
                      modal: true,
                      minWidth: 285,
                      minHeight: 120,
                      resizable:false,
                      dialogClass: "modal-dialog",
                      show: "fadeIn" ,
                      close: function(event, ui)
                      {
                          $(this).dialog('destroy');
                      }
                   });

            $('.ui-widget-overlay').addClass('bg-black opacity-60');
        }
        $scope.delete = function() {
                Report.delete({report_id:$scope.report_id},function(data) {
                   FlashMessage.setMessage(data);
                   $scope.$emit("report_deleted");
                   delete_dialog.dialog('destroy');
                });

        }

    }]);

JimmyDashboard.controller("CoworkerCtrl",["$scope", "$rootScope", "GeneralInfo", "Coworker", "FlashMessage", function($scope,$rootScope,GeneralInfo,Coworker,FlashMessage){

     $scope.coworkers = Coworker.query();
     $scope.coworker = {};
     $scope.saveDisabled = false;

     $scope.$emit('breadcrumbs_ready', {
      crumbs: [
        {
          title: 'Dashboard',
          url: '/',
          class: ''
        },
        {
          title: 'Coworker',
          url: '',
          class: 'active'
        }
      ]
    });

     $scope.save = function(){

        if($("#coworker_form").parsley('validate')){

                Coworker.save($scope.coworker,function(data){
                    FlashMessage.setMessage(data);
                    if(data.success==true){
                        $scope.$emit("coworker_saved",data.client_id);
                        $scope.coworkers = Coworker.query();
                    }
                });
        }
     }

     $scope.delete = function(coworker_id){
        Coworker.delete({coworker_id:coworker_id},function(data){
            FlashMessage.setMessage(data);
            if(data.success==true){
              $scope.coworkers = _.filter($scope.coworkers,function(coworker){
                  return !(coworker.user_id==coworker_id);
              })

            }
        });
     }

}]);



JimmyDashboard.controller("SupportCtrl",["$scope", "$rootScope", "$http", "GeneralInfo", "Coworker", "FlashMessage", function($scope,$rootScope,$http,GeneralInfo,Coworker,FlashMessage){

     $scope.support = {}
     $scope.support_types = [{id:'general',name:'General'},{id:'sales',name:'Sales'},{id:'technical',name:'Technical'}];



     $scope.submit = function(){


        if($("#support_form").parsley('validate')){

             $http({
                      method: 'POST',
                      url: "/support",
                      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                      transformRequest: function (data) {
                          var formData = $.param(data);
                          return formData;
                      },
                      data: $scope.support
                  }).
                  success(function (data, status, headers, config) {
                      FlashMessage.setMessage(data);
                  }).
                  error(function (data, status, headers, config) {
                      FlashMessage.setMessage(data);
                  });
        }
    }

}]);



JimmyDashboard.controller("ClientCtrl",["$scope", "$rootScope", "$http", "$location", "$routeParams", "$timeout", "Client", "ClientSourceList", "ClientSource", "ClientAccounts", "AppAuth", "FlashMessage", function($scope,$rootScope, $http,$location, $routeParams,$timeout, Client,ClientSourceList,ClientSource,ClientAccounts,AppAuth,FlashMessage){

    $scope.client        = Client.query({client_id:$routeParams.client_id },function(client){

        if(angular.isDefined(client.client_id)) {
           $scope.clientSources = ClientSourceList.query({client_id:$routeParams.client_id});
        } else {
            $scope.error_msg   = client.message;
        }
        $scope.$emit('breadcrumbs_ready', {
          crumbs: [
            {
              title: 'Dashboard',
              url: '/',
              class: ''
            },
            {
              title: client.name,
              url: '',
              class: 'active'
            }
          ]
        });
    });

    $scope.$on('client_source_added',function(e,args){
        $scope.clientSources          = ClientSourceList.query({client_id:args.client_id});
    });

    $scope.uploadFinish = function(data){
        $scope.client        = Client.query({client_id:$routeParams.client_id });
    }

    $scope.deleteSource = function(source_id){
      ClientSource.delete({source_id:source_id},function(data){

          FlashMessage.setMessage(data);

          if(data.success==true){
              $scope.clientSources = _.filter($scope.clientSources,function(source){
                  return !(source.id==source_id);
              })
          }
      });
    }


    $scope.updateTitle = function(name){
         return  Client.update({client_id:$scope.client.client_id},{name:name,action:'update-name'});
    }


    $scope.reauthorizeAccess = function(source_id){
      var left  = ($(window).width()/2)-(900/2);
      var top   = ($(window).height()/2)-(600/2);

      $rootScope.reauthorization_window = window.open('/re-authapp/'+source_id, 'Reauthorization Window', 'width=600,height=600,top=' + top + ', left=' + left);
      $rootScope.reauthorization_window.focus();
      // Init the check reauthorization method and invoke the promise callback function
      $timeout(AppAuth.check_reauthorization, 1000).then(function(){
          $scope.clientSources = ClientSourceList.query({client_id:$routeParams.client_id});
      });
    }
}]);

JimmyDashboard.controller('ClientAddCtrl', ["$scope", "$rootScope", "$location",
    "Client", "FlashMessage", "AppAuth","SourceClients", "ClientSourceList", function($scope, $rootScope,
    $location, Client, FlashMessage, AppAuth, SourceClients, ClientSourceList) {


        $scope.authorized    = false;
        $scope.show_upload   = false;
        $scope.client        = {};
        $scope.saveDisabled  = false;
        $scope.sources = ClientSourceList.query();
        $scope.sourcesLoaded = false;
        $scope.sourcesLoading = false;

        $scope.clientAccountUpdate = function() {
          $scope.$digest();
        }

        $scope.initDialog = function() {
          // hide the source addition div
          $scope.showSourceAddition = false;
          // clear all channel selections
          $scope.channels.forEach(function(c) {
            c.isSelected = false;
          });
          // clear all source selections
          $scope.sources.forEach(function(s) {
              s.isSelected = false;
          });
          // init client
          $scope.client = {};
          // init source name
          $scope.newSourceName = '';
        }

        $scope.channels = [
          {name: "Google Adwords", img: "/images/googleadwords-icon.png", isSelected: false, data:'googleadwords'},
          {name: "Google Analytics", img: "/images/googleanalytics-icon.png", isSelected: false, data:'googleanalytics'},
          {name: "Bing", img: "/images/bingads-icon.png", isSelected: false, data:'bingads'}
        ];


        $scope.uploadFinish = function(data) {

            $scope.client  = Client.query({client_id:$scope.client.client_id});
            $scope.success = true;
            $scope.msg     = "Logo updated";
            $scope.bg      = "success-bg";
            $scope.header  = 'Success!';
        }


        $scope.channelSelect = function(channel) {
          $scope.channels.forEach(function(c) {
            c.isSelected = false;
          });
          channel.isSelected = true;
          $scope.selectedChannel = channel;
        }

        $scope.showClientAddition = function() {
          $scope.selectedChannel = null;
          $scope.newSourceName = null;
          $scope.channels.forEach(function(c) {
            c.isSelected = false;
          })
          $scope.showSourceAddition = false;
        }

        $scope.hasSelectedSource = function() {
          var selected = $scope.sources.filter(function(s) {
            if(s.isSelected == undefined || s.isSelected == false ) {
              return false;
            }
            if(s.isSelected == true) {
              return true;
            }
          })
          return selected.length == 1;
        }


        $scope.isSourceFormValid = function() {
          if(!$scope.newSourceName) {
            return false;
          }
          return $scope.newSourceName.length >0 && !!$scope.selectedChannel;
        }

        $scope.hasSelectedAccount = function() {
          if($scope.client.account == undefined){
            return false;
          }
          return true;
        }

        $scope.hasClientName = function() {
          if($scope.client.name==null) {
            return false;
          }
          return $scope.client.name.length > 0;
        }

        $scope.selectSource = function(source, channel) {
            $scope.sources.forEach(function(s) {
              s.isSelected = false;
            })
            source.isSelected = true;

               // $("button.sourceItem").css("background-color","#2E292D");
               // $("button#sourceItem-"+source.id).css("background-color","#827c81");
                // $("#client_form").show();
               // $(".loading-gif").show();
                // $("#addClientError").hide();
                $scope.sourcesLoading = true;
                $scope.sourcesLoaded = false;
               SourceClients.query({sourceId : source.id}, function(data){
                    if(data.success) {
                      $scope.channel = channel;
                      $scope.token_id = source.id;
                      $scope.client_accounts = data.accounts;
                      $scope.client_accounts.loaded = true;
                      // $(".loading-gif").hide();
                      FlashMessage.setMessage(data);
                      $scope.sourcesLoading = false;
                      $scope.sourcesLoaded = true;

                    } else {
                      $scope.errorMessage = data.message;
                      $("#addClientError").show();
                      $scope.sourcesLoading = false;
                      $scope.sourcesLoaded = true;
                      // $(".loading-gif").hide();

                    }

               });
        }
      $scope.addSourceBtn = function() {
             $('#add-source-data').show();
                   $('#sourceForm').hide();
      }

        $scope.save = function() {

          $scope.saveDisabled = true;
          angular.forEach($scope.client_accounts, function(v, k) {
              if($scope.client.account == v.id) {
                    $scope.client.email = v.email;
                    $scope.client.account_name  = v.name;
                    $scope.client.channel = $scope.channel;
                    $scope.client.token_id = $scope.token_id;
              }
          });

          if($scope.hasSelectedAccount()&&$scope.hasClientName()&&$scope.hasSelectedSource()) {
            Client.save($scope.client,function(data) {
                $scope.saveDisabled = false;
                $scope.show_upload = true;
                FlashMessage.setMessage(data);
                $scope.client.client_id = data.client_id;
                $scope.$emit("client_saved",data.client_id);
                $("#add-client").dialog('destroy');

            });
          }

        }
}])

JimmyDashboard.controller('ClientSourceAddCtrl', ["$scope", "$rootScope", "$routeParams",
    "Client", "FlashMessage", "AppAuth","SourceClients", "ClientSourceList", function($scope,$rootScope,
    $routeParams, Client, FlashMessage, AppAuth, SourceClients, ClientSourceList){

    $scope.authorized    = false;
    $scope.client        = {};
    $scope.saveDisabled  = false;
    clientId = null;
    $scope.sourcesLoaded = false;
    $scope.sourcesLoading = false;

    $scope.showSourceAddition = false;

    $scope.channels = [
      {name: "Google Adwords", img: "/images/googleadwords-icon.png", isSelected: false, data:'googleadwords'},
      {name: "Google Analytics", img: "/images/googleanalytics-icon.png", isSelected: false, data:'googleanalytics'},
      {name: "Bing", img: "/images/bingads-icon.png", isSelected: false, data:'bingads'}
    ];

    $scope.sources = ClientSourceList.query();

    $scope.clientAccountUpdate = function() {
      $scope.$digest();
    }

    $scope.initDialog = function() {
      // clear all channel selections
      $scope.channels.forEach(function(c) {
        c.isSelected = false;
      });
      // clear source name
      $scope.newSourceName = '';
      // init client
      $scope.client = {};
    }

    $scope.closeSourceAddition = function() {
      $scope.$emit('source_addition_closed');
    }

    $scope.channelSelect = function(channel) {
      $scope.channels.forEach(function(c) {
        c.isSelected = false;
      });
      channel.isSelected = true;
      $scope.selectedChannel = channel;
    }

    $scope.showClientAddition = function() {
      $scope.selectedChannel = null;
      $scope.newSourceName = null;
      $scope.channels.forEach(function(c) {
        c.isSelected = false;
      })
      $scope.showSourceAddition = false;
    }

    $scope.isSourceFormValid = function() {
      if(!$scope.newSourceName) {
        return false;
      }
      return $scope.newSourceName.length >0 && !!$scope.selectedChannel;
    }

    if($routeParams.client_id)
       $scope.client.client_id  = $routeParams.client_id;
    else {
       $scope.client.client_id  = $scope.$parent.report.user_id;
    }

    $scope.hasSelectedSource = function() {
      var selected = $scope.sources.filter(function(s) {
        if(s.isSelected == undefined || s.isSelected == false ) {

          return false;
        }
        if(s.isSelected == true) {

          return true;
        }
      })
      return selected.length == 1;
    }


    $scope.isSourceFormValid = function() {
      if(!$scope.newSourceName) {
        return false;
      }
      return $scope.newSourceName.length >0 && !!$scope.selectedChannel;
    }

    $scope.hasSelectedAccount = function() {
      if($scope.client.account == undefined){
        return false;
      }
      return true;
    }

    $scope.$on("sourceAdded", function(val,arg){
            $scope.selectSource(arg[0], arg[0].channel);
        });

    $scope.selectSource = function(source, channel) {
           $scope.sources.forEach(function(s) {
              s.isSelected = false;
            })
            source.isSelected = true;

             // $("button.sourceItem").css("background-color","#2E292D");
             // $("button#sourceItem-"+source.id).css("background-color","#827c81");
             // $("#client_source_form").show();
             // $(".loading-gif").show();

             $scope.sourcesLoading = true;
             $scope.sourcesLoaded = false;
           SourceClients.query({sourceId : source.id}, function(data){
                if(data.success) {

                  $scope.channel = channel;
                   $scope.token_id = source.id;
                   $scope.client_accounts = data.accounts;
                   $scope.client_accounts.loaded = true;
                    // $(".loading-gif").hide();
                     FlashMessage.setMessage(data);
                     $scope.sourcesLoading = false;
                      $scope.sourcesLoaded = true;

               } else {
                   $scope.errorMessage = data.message;
                      $("#addClientError").show();
                      $scope.sourcesLoading = false;
                      $scope.sourcesLoaded = true;
                      // $(".loading-gif").hide();

               }
           });
    }


    $scope.$on('report_client_selected',function(data,args) {
        clientId  = args.client_id;


    });

    $scope.save = function() {
        if (clientId == null) {
            clientId = $routeParams.client_id;
        }
        console.log($routeParams.client_id);
            $scope.saveDisabled = true;
            $scope.client.client_id = clientId;
            angular.forEach($scope.client_accounts, function(v, k){
                if($scope.client.account==v.id) {
                    $scope.client.email = v.email;
                    $scope.client.account_name  = v.name;
                    $scope.client.channel = $scope.channel;
                    $scope.client.token_id = $scope.token_id;
                }
            });
            $scope.client.action = 'add-source';
            console.log($scope.client);
        Client.save($scope.client,function(data){

         //   $("#add-source").dialog('destroy');
            $scope.saveDisabled = false;
            FlashMessage.setMessage(data);
            $scope.$emit("client_source_added",{client_id:$scope.client.client_id});
        });
    }
}]);

JimmyDashboard.controller("AuthCallbackCtrl",["$scope", "$rootScope", "$http", "$routeParams", "ClientAccounts", function($scope, $rootScope,$http,$routeParams, ClientAccounts){

    if(typeof error != 'boolean')
       window.authorized   = true;
    else {
       window.authorized   = false;
    }
}]);


JimmyDashboard.controller("ReAuthCallbackCtrl",["$scope", "$rootScope", "$http", "$routeParams", function($scope, $rootScope,$http,$routeParams){
    window.re_authorized      = true;
}]);


JimmyDashboard.controller('DownloadReportCtrl', ["$scope", "$rootScope", "$http", "$routeParams", "$resource", "Report", "ReportList", function($scope, $rootScope,$http,$routeParams, $resource,Report,ReportList){

// For future Use

}])

JimmyDashboard.controller('CloneReportCtrl', ["$scope", "$rootScope", "$http", "$routeParams", "$resource", "Report", "ReportList", "FlashMessage", function($scope, $rootScope,$http,$routeParams, $resource,Report,ReportList,FlashMessage){

    var reports = $scope.$parent.reports;
    $scope.report_clone = true;
    $scope.clone_report = {};
    $scope.saveDisabled  = false;

    $scope.isUnlimitedUser = function() {
      var userPackage = $rootScope.generalInfo.unlimited_packages.filter(function(p) {
        return p.id == $rootScope.generalInfo.package.id;
      });
      if(userPackage.length>0) {
        return true;
      } else {
        return false;
      }
    }


      if($rootScope.generalInfo.package.id == $rootScope.generalInfo.unlimited_package.id){
        $scope.can_create = true;
      } else if($rootScope.generalInfo.templates_used<$rootScope.generalInfo.package.templates_allowed || !$scope.new_report){
        $scope.can_create = true;
      } else {
        $scope.can_create = false;
      }

    $scope.clone = function(){

        if($("#clone_form").parsley('validate')){
                $scope.clone_report.id = $scope.report.id;

                Report.clone({report_id:$scope.report.id},$scope.clone_report,function(data){

                    FlashMessage.setMessage(data);
                    if(data.success==true){
                      $scope.$emit("report_cloned");
                    }
                });
        }
    }
}])

JimmyDashboard.controller('ShareReportCtrl', ["$scope", "$rootScope", "$http", "$routeParams", "$resource", "Report", "ReportList", "FlashMessage", function($scope, $rootScope,$http,$routeParams, $resource,Report,ReportList,FlashMessage){

    var reports = $scope.$parent.reports;

    $scope.share_report  = null;
    $scope.saveDisabled  = false;

    $scope.removeSharing = function(sharing_id){
        Report.removeSharing({id:sharing_id},function(data){
            FlashMessage.setMessage(data);
            if(data.success==true){
              $scope.$emit("sharing_removed",$scope.sharing_list);
              $scope.sharing_list = _.filter($scope.sharing_list,function(share){
                  return !(share.id==sharing_id);
              });
            }
       });
    }


     $scope.save = function(){

        if($("#share_form").parsley('validate')){

                Report.share({report_id:$scope.report.id},$scope.share_report,function(data){
                    FlashMessage.setMessage(data);

                    if(data.success==true){
                     Report.getShared({report_id:$scope.report.id },function(sharing_list){
                          $scope.sharing_list =  sharing_list;
                          $scope.$emit("report_shared",sharing_list);
                      });
                    }
                });
        }
    }
}])

JimmyDashboard.controller('ScheduleReportCtrl', ["$scope", "$rootScope", "$http", "$routeParams", "$resource", "Report", "ReportList", "FlashMessage", function($scope, $rootScope,$http,$routeParams, $resource,Report,ReportList,FlashMessage){

    var reports = $scope.$parent.reports;

    $scope.schedule_report = {};
    $scope.saveDisabled    = false;
    $scope.schedule_label =  'Schedule';
    $scope.$watch('quickSend', function(data) {
                data === true ? $scope.schedule_label =  'Send Now' : $scope.schedule_label =  'Schedule';
    });

    $resource("/src/app/timezones.json").query(function(timezones){
      $scope.timezones =  timezones;
      $scope.timezones.loaded = true;
    });

    $scope.frequency = [{'id':'send-now','title':'Send-now'},{'id':'one-off','title':'One-off'},{'id':'daily','title':'Daily'},{'id':'weekly','title':'Weekly'},{'id':'fortnightly','title':'Fortnightly'},{'id':'monthly','title':'Monthly'}];

    $scope.removeSchedule = function(schedule_id){
        Report.removeSchedule({id:schedule_id},function(data){
            FlashMessage.setMessage(data);

            if(data.success==true){
              $scope.$emit("schedule_removed",$scope.scheduled_list);
              $scope.scheduled_list = _.filter($scope.scheduled_list,function(schedule){
                  return !(schedule.id==schedule_id);
              });
            }
       });
    }

    $scope.editSchedule = function(schedule_id){
        angular.forEach($scope.scheduled_list, function(value, key){
              if(value.id == schedule_id){
                $scope.schedule_report = Object.create(value);

                //Copy the properties and the value
                for (prop in value) {

                    if (value.hasOwnProperty(prop)) {
                        if(prop == "body") {
                              CKEDITOR.instances['editor2'].setData(value[prop]);
                        }
                      $scope.schedule_report[prop] = value[prop];

                    }
                }

                var start_date = value.start_date.split(" ");

                $scope.schedule_report.start_date = start_date[0];
                $scope.schedule_report.time       = start_date[1];

                angular.forEach($scope.timezones, function(v, k){
                  if(v.id == value.timezone){
                    $scope.schedule_report.timezone = v;
                    $scope.timezones.loaded = false;
                  }
                })

              }
        });

    }

    $scope.selectFreq = function(freq_id){

      if(freq_id=='send-now'){
         $scope.schedule_label =  'Send';
      } else {
         $scope.schedule_label =  'Schedule';
      }
    }

     $scope.save = function(){
        if (angular.isDefined(CKEDITOR.instances['editor2'])) {
            var body = CKEDITOR.instances['editor2'].getData();
            $scope.schedule_report.body = body;
        }
        if (angular.isDefined(CKEDITOR.instances['editor3'])) {
            var body = CKEDITOR.instances['editor3'].getData();
            $scope.schedule_report.body = body;
        }
        if ($scope.quickSend) {
           $("#send_form").parsley('validate')? $scope.sendNow = true : $scope.sendNow = false;
        } else {
            $("#schedule_form").parsley('validate')? $scope.sendNow = true : $scope.sendNow = false;
        }

        if($scope.sendNow){
            $scope.saveDisabled = true;
            if($scope.schedule_report.frequency=='send-now')
               $scope.schedule_label =  'Sending...';
            else{

               $scope.schedule_label =  'Scheduling...';
            }

             var params = {report_id:$scope.report.id}

            if($scope.schedule_report.id){
              params = {report_id:$scope.report.id,id:$scope.schedule_report.id}
              $scope.schedule_report.timezone  = $scope.schedule_report.timezone.id;
             // $scope.schedule_report.next_schedule_date = null;
              Report.updateSchedule(params,$scope.schedule_report,function(data){
                  FlashMessage.setMessage(data);
                  $scope.saveDisabled = false;
                  $scope.schedule_label =  'Schedule';
                  if(data.success==true){
                    $scope.schedule_report = {};
                    $scope.scheduled_list = Report.getScheduled({report_id:$scope.report.id });
                    $("#schedule-report").dialog("close");
                  }
              });
            } else {

                Report.schedule(params,$scope.schedule_report,function(data){
                    FlashMessage.setMessage(data);
                    $scope.saveDisabled = false;
                    $scope.quickSend ? $scope.schedule_label =  'Send Now' : $scope.schedule_label =  'Schedule';

                    if(data.success==true){
                      $scope.schedule_report = {};
                     Report.getScheduled({report_id:$scope.report.id },function(scheduled_list){
                        $scope.scheduled_list = scheduled_list;
                        $scope.$emit("scheduled",$scope.scheduled_list);
                        $scope.quickSend ? $("#send-report").dialog("close") :$("#schedule-report").dialog("close");
                      });
                    }
                });

            }
        }
    }
}])


JimmyDashboard.controller('ClientMigrateCtrl',["$scope","$rootScope",
                           function($scope, $rootScope) {




}]);

JimmyDashboard.controller('ChatCtrl', ["$scope", "$rootScope", "$http", "$routeParams",
                          "$timeout", "$resource", "Message", "MessageList", "FlashMessage",
                          function($scope, $rootScope,$http,$routeParams, $timeout,
                                   $resource,Message,MessageList,FlashMessage) {

    $scope.chat = {};

    $scope.delete  = function(id){
      Message.delete({message_id:id},function(data){
          $scope.messages = _.filter($scope.messages,function(message){
              return !(message.id==id);
          });
         FlashMessage.setMessage(data);
      });
    }

    $scope.updateMessage  = function() {

        $scope.timeout = $timeout($scope.updateMessage, 5000);

        MessageList.query({widget_id:$scope.widget_id},function(messages){
            $scope.messages = messages;
        });
    }

    $("#message").keypress(function(event,key){
        if(event.keyCode == 13){
            $scope.sendMessage();
        }
    })


    $scope.sendMessage = function(){
      $scope.chat.widget_id = $scope.widget_id;

      Message.save($scope.chat,function(){
          MessageList.query({widget_id:$scope.widget_id},function(messages){
              $scope.messages = messages;
          });
           $("#message").val('');

        //$(element).niceScroll({cursorcolor:"#ccc"});

         // $("#chat-box").niceScroll().ss
      })

    }
}])

JimmyDashboard.controller("ClientListCtrl",["$scope", "$rootScope", "$location", "$routeParams", "$timeout", "$http", "$filter", "ReportList", "ClientList", "Client", "ClientAccounts", "FlashMessage", function($scope, $rootScope,$location,$routeParams,$timeout,$http,$filter,ReportList,ClientList,Client,ClientAccounts,FlashMessage){

   ClientList.query(function(clients){
        $scope.clients    =  clients;
        $scope.clients.loaded = true;
        $scope.$emit('breadcrumbs_ready', {
          crumbs: [
            {
              title: 'Dashboard',
              url: '/',
              class: ''
            },
            {
              title: 'Clients',
              url: '',
              class: 'active'
            }
          ]
        })
    });

    $scope.$on('client_saved',function(e,client_id){
        $scope.clients          = ClientList.query({list:'recent'},function(){
        });
        if(!!$rootScope.moveToReportCreation) {
          $location.path('/report/new-report')
        } else {
          $location.path("/clients/" + client_id+"/reports")
        }
    });



    $scope.delete = function(client_id){
        Client.delete({client_id:client_id},function(data){

            FlashMessage.setMessage(data);

            if(data.success==true){
              $scope.clients = _.filter($scope.clients,function(client){
                  return !(client.id==client_id);
              })
          }
       });
    }
}]);


JimmyDashboard.controller("ReportListCtrl",["$scope", "$rootScope", "$routeParams",
    "$filter", "ReportList", "Client", "Report", "FlashMessage",
    function($scope, $rootScope,$routeParams, $filter,ReportList,Client,Report,FlashMessage){

      var params = {client_id:$routeParams.client_id }

      if($routeParams.shared=='shared')
          params.shared = 'shared';


      if(!$routeParams.client_id) // If no client_id present in the route load all reports
         ReportList.query($routeParams,function(reports){
            $scope.reports = reports;

            $scope.$emit('breadcrumbs_ready', {
              crumbs: [
                {
                  title: 'Dashboard',
                  url: '/',
                  class: ''
                },
                {
                  title: 'Reports',
                  url: '',
                  class: 'active'
                }
              ]
            });
         });


      $scope.$on("report_cloned",function(){
          $scope.reports = ReportList.query($routeParams);
      })

      // When the Sharing is removed completely from a particular report update the model
      $scope.$on("sharing_removed",function(e,data){
          if(data.length==1) {
             _.filter($scope.reports,function(report){
                   if(data[0].report_id==report.id)
                      report.shared = false;
            });
          }
      })

      // When the Sharing is added to a particular report update the model
      $scope.$on("report_shared",function(e,data){
          if(data.length==1){
             _.filter($scope.reports,function(report){
                   if(data[0].report_id==report.id)
                      report.shared = true;
            });
          }
      })

      // When the schedule is removed completely from a particular report update the model
      $scope.$on("schedule_removed",function(e,data){
          if(data.length==1){
             _.filter($scope.reports,function(report){
                   if(data[0].report_id==report.id)
                      report.scheduled = false;
            });
          }
      })

      //When the schedule is added to a particular report update the model
      $scope.$on("scheduled",function(e,data){
          if(data.length==1){
             _.filter($scope.reports,function(report){
                   if(data[0].report_id==report.id)
                      report.scheduled = true;
            });
          }
      })

      if(angular.isDefined($routeParams.client_id)){
         $scope.client  = Client.get({client_id:$routeParams.client_id},function(client){
             if(angular.isDefined(client.client_id))
                ReportList.query($routeParams,function(reports){
                     $scope.reports = reports;
                     $scope.$emit('breadcrumbs_ready', {
                      crumbs: [
                        {
                          title: 'Dashboard',
                          url: '/',
                          class: ''
                        },
                        {
                          title: client.name,
                          url: '/clients/'+client.client_id+'/reports',
                          class: ''
                        },
                        {
                          title: 'Reports',
                          url: '',
                          class: 'active'
                        }
                      ]
                    });
                });
             else
                $scope.error_msg = client.message
         });
      }

      $scope.delete = function(report_id){

          Report.delete({report_id:report_id},function(data){
              FlashMessage.setMessage(data);
              $scope.reports = _.filter($scope.reports,function(report){
                  return !(report.id==report_id);
              })

              $scope.$emit("report_deleted");
          });

      }
}]);

JimmyDashboard.controller('SideBarCtrl',["$scope", "Tour", "$rootScope", "$routeParams","$timeout",
    "$location", "$http", "$q", "CurrentReport","Template", "ClientSourceList", "Report", "FlashMessage", "ReportUpgradeService",
    function($scope, Tour, $rootScope, $routeParams, $timeout, $location, $http, $q,
      CurrentReport, Template, ClientSourceList, Report, FlashMessage, ReportUpgradeService) {
        $scope.report ={};

        $scope.adwords = {};
        $scope.analytics = {};
        $scope.bing = {};
        $scope.adwords.clients = [];
        $scope.analytics.clients = [];
        $scope.bing.clients = [];
        $scope.curReport = {};
        $scope.sourceError = false;
        $scope.curReport.channel = "";
        $scope.clientErr = false;
        $scope.widgetTypeError = false;
        $scope.graphError = false;
        $scope.tableError = false;
        $scope.piechartError = false;
        $scope.widgetTourVisited = false;
        $scope.reportBind = false;

        var downloads = 0;
        var q = $q;
        var newWidgetTourSteps = [
            {
                'next #sourceBar' : 'select a source'
            },
            {
                'next .sourceSelect': 'Select a client'
            },
            {
                'next .types' : 'Select a widget from the list'
            },
            {
                'next addWidgetBtn' : 'Click continue for more options'
            }
        ];
    
        $scope.doReportAction = function(report, action, create) {
          $scope.pendingReport = report;
          $scope.billingReportsCount = $rootScope.generalInfo.templates_used;
          if($scope.isUpgradeRequired()) {
            if(create) {
              $rootScope.$broadcast('show_create_report_dialog');
            } else {
              $rootScope.$broadcast('show_upgrade_alert_dialog');
            }
            $scope.pendingAction = action;
          } else {
            action();
          }
        }

        $scope.$on('upgradation_successful', function() {
          if(!!$scope.pendingAction) {
            $scope.pendingAction();
          }
        });

        $scope.isPaymentRequired = function() {
          if($rootScope.generalInfo.package.id==13)
            return false;
          if($rootScope.generalInfo.package.id==14)
            return true;       
          if($rootScope.generalInfo.package.id==5) {
            if($rootScope.generalInfo.templates_used>=$rootScope.generalInfo.package.templates_allowed)
              return true;
          }
          return false
        }

        $scope.isUpgradeRequired = function() {
          if($rootScope.generalInfo.package.id==13)
            return false;
          if($rootScope.generalInfo.package.id==14)
            return false;
          if($rootScope.generalInfo.package.id==5)
            return true;
          return false
        }

        $scope.resetAddWidget = function() {
            $scope.adwords = [];
            $scope.analytics = [];
            $scope.clientSources = [];
            $scope.widget = [];
            $scope.sourceError = false;
            $scope.curReport = {};
            $scope.curReport.channel = "";
            $scope.clientErr = false;
            $scope.widgetTypeError = false;
            $scope.graphError = false;
            $scope.tableError = false;
            $scope.piechartError = false;
            $scope.table= [];
            $scope.graph = [];
            $(".acc-icon").css("opacity", ".5");
            $scope.clientList = [];
            $(".type-header").css("background", "#3d363c");
            $(".type-submenu").slideUp();
             $(".arrow-up").hide();
        };


        $scope.newReport = $rootScope.$watch("reportJustMade", function() {
                   $timeout(function() {
                        if($rootScope.reportJustMade) {
                            var newReportTourSteps = [
                                {
                                    'click #add-widget-sidebar-btn' : 'Your report is empty. Start adding widgets here!'
                                }
                            ];
                            var newReportTour = new EnjoyHint({});
                            newReportTour.set(newReportTourSteps);
                            newReportTour.run();
                            $rootScope.reportJustMade = false;
                        }
                    });
                });

        $scope.addWidgetSideBar = function() {
             $scope.insightBtn = false;
             _kmq.push(['record', 'Add widget sidebar button']); //Kissmetrics call
            $scope.resetAddWidget();
            //guided tour Initialization
               if($rootScope.generalInfo.current_user.type == 'coworker') {
                  $(".sidebar").css("margin-top","-170px");
               }
                    $('[data-toggle="tooltip"]').tooltip();
                $scope.new_report    = false;
                $scope.show_wizard   = true;
                $scope.$watch('routeParams', function(){
                    Report.get({report_id:$routeParams.report_id}, function(data) {
                        $scope.report.user_id = data.user_id;
                        $scope.listSources();
                        $scope.report.widget = {};
                        $scope.report.widget.device_type  = [];
                        $scope.report.widget.network_type = [];
                        $scope.report.widget.type = '';
                    });
                });

            $scope.openSideBar("add-widget-sidebar");
            if ($scope.widgetTourVisited == false) {
                $scope.widgetTourVisited = true;
                var userId = $rootScope.generalInfo.current_user.id;
                 if ($rootScope.generalInfo.current_user.type != 'user') {
                    Tour.visitTour({tourName : "add widget" ,userId: userId }, function(data) {
                        if (!data.visited) {
                         var newWidgetTour = new EnjoyHint({});
                         newWidgetTour.set(newWidgetTourSteps);
                         newWidgetTour.run();
                        }
                    });
                }

            }
            $("#info-addWidget").on("click", function(){
                var newWidgetTour = new EnjoyHint({});
                newWidgetTour.set(newWidgetTourSteps);
                newWidgetTour.run();
            });

    }
       $scope.$on('client_source_added',function(e,args){
         $scope.resetAddWidget();
         $scope.listSources();



    });
    $scope.listSources = function() {
        $scope.clientSources = ClientSourceList.query({client_id:$scope.report.user_id}, function() {
            $scope.noSource = true;
            $scope.$broadcast('report_client_selected',{client_id:$scope.report.user_id});

            $scope.adwords.clients = [];
            $scope.analytics.clients = [];
            $scope.bing.clients = [];
            angular.forEach($scope.clientSources, function(value, key) {
                    if (value.channel == "googleadwords") {
                        $scope.noSource = false;
                        $scope.adwords.isAvailable = true;
                        $scope.adwords.clients.push(value);

                    } else if (value.channel == "googleanalytics") {
                        $scope.noSource = false;
                        $scope.analytics.isAvailable = true;
                        $scope.analytics.clients.push(value);

                    } else if (value.channel == "bingads") {
                        $scope.noSource = false;
                        $scope.bing.isAvailable = true;
                        $scope.bing.clients.push(value);
                    }
            });
        });



    }

    $scope.delete = function(report_id) {
          Report.delete({report_id:report_id},function(data){
              $scope.reports = _.filter($scope.reports,function(report){
                  return !(report.id==report_id);
              })
              $scope.$emit("report_deleted");
                FlashMessage.setMessage(data);
              $location.path("/reports");

          });
    }

    $scope.reportOptionSideBar = function() {
         _kmq.push(['record', 'report options sidebar']); //Kissmetrics call
        $scope.current_user_type = $rootScope.generalInfo.current_user.type;
        $scope.shared_with_me = Report.shared_with_me;
        $scope.openSideBar("reoprt-options-sidebar");
        $scope.report.id = $routeParams.report_id;
        if($rootScope.generalInfo.current_user.type == 'coworker') {
                   $(".sidebar").css("margin-top","-170px");
               }
        if (!$scope.reportBind) { //stop from binding multiple times!
            $('#download-pdf').click(function() {
                      $scope.closeWidget ();
                      var iframe = $("#download-report");
                      var ProgressBarScope = angular.element("#progress-bar").scope();

                        downloads = downloads+1;

                        if(downloads>1){
                           ProgressBarScope.stop({message:'Download Cancelled'});
                           q.resolve(); // resolve previous promise
                           q = $q.defer(); // create a new one
                           ProgressBarScope.start({message:'Downloading Report ( ' + CurrentReport.report.title+ " )"});
                         } else
                           ProgressBarScope.start({message:'Downloading Report ( ' +  CurrentReport.report.title+ " )"});



                        $http({
                            url: '/reports/download/' + $routeParams.report_id,
                            method: "GET",
                            timeout:q.promise
                        }).
                        success(function(data, status, headers, config) {
                            FlashMessage.setMessage(data);

                            if(data.success){
                              iframe.attr('src',data.file);
                              downloads = 0;
                              ProgressBarScope.complete({message:'Download Complete'});
                            } else {
                              ProgressBarScope.stop({message:'Download Cancelled'});
                            }
                        }).
                        error(function(data, status, headers, config) {
                          downloads = 0;
                          FlashMessage.setMessage({message:'Download Cancelled',success:false});
                        });

            });


            $('#clone-reportBtn').click(function() {
                    var   CloneCtrlScope = angular.element($( "#clone-report")).scope();

                    $scope.closeWidget ();

                    $scope.doReportAction($scope.report, function() {

                      $scope.$apply(function(){
                        CloneCtrlScope.report  = $scope.report;
                      })

		                  $("#clone-report").find("#report_title").val('');

                      $ ('.ui-widget-overlay').addClass('bg-black opacity-60');

                      $( "#clone-report").find( "#done-cancel").click(function(){
                          clone_dialog.dialog('destroy');
                      })

                     var clone_dialog =  $( "#clone-report").dialog({
                          modal: true,
                          minWidth: 400,
                          height: 500,
                          resizable:false,
                          dialogClass: "modal-dialog",
                          show: "fadeIn" ,
                          close: function(event, ui)
                          {
                              $(this).dialog('destroy');
                          }
                       });

                        CloneCtrlScope.$on('report_cloned',function(){
                          clone_dialog.dialog('destroy');
                        })

                    }, true);


              });
        $("#delete-reportBtn").on("click", function() {
            $scope.closeWidget ();
            $scope.delete($routeParams.report_id);
        });

            $('#add-template').click(function(){


                      var templateScope = angular.element($( "#new-template")).scope();

                      templateScope.report.id   = $routeParams.report_id;
                      templateScope.report.user = $rootScope.generalInfo.current_user.id;
                      templateScope.report.title  = CurrentReport.report.title;
                      var addTemplate_dialog =  $( "#new-template").dialog({
                          modal: true,
                          minWidth: 400,
                          minHeight: 200,
                          resizable:false,
                          dialogClass: "modal-dialog",
                          show: "fadeIn" ,
                          close: function(event, ui)
                          {
                              $(this).dialog('destroy');
                          }
                      });
                      $("#add-template-close").click(function() {
                          addTemplate_dialog.dialog("close");
                      });
                      $('.ui-widget-overlay').addClass('bg-black opacity-60');
            });
        }

    }
    $scope.shareSendSidebar = function() {
        $scope.openSideBar("share-send-sidebar");
             _kmq.push(['record', 'Share send Sidebar']); //Kissmetrics call
        currentUser = $rootScope.generalInfo.current_user;
        $scope.report.id = $routeParams.report_id;
         if($rootScope.generalInfo.current_user.type == 'coworker') {

                   $(".sidebar").css("margin-top","-170px");

               }
         $('#schedule-reportBtn').click(function() {
                 $scope.closeWidget ();
                 var   ScheduleCtrlScope = angular.element($("#schedule-report")).scope();

                  ScheduleCtrlScope.report      = $scope.report;
                  ScheduleCtrlScope.scheduled_list = Report.getScheduled({report_id:$scope.report.id});
                  ScheduleCtrlScope.schedule_report.from_email = currentUser.email;
                  ScheduleCtrlScope.schedule_report.from_name = currentUser.name;
                  ScheduleCtrlScope.quickSend = false;

                 $scope.doReportAction($scope.report, function() {
                   var schedule_dialog =  $( "#schedule-report").dialog({
                        modal: true,
                        minWidth: 700,
                        minHeight: 200,
                        resizable:false,
                        dialogClass: "modal-dialog",
                        show: "fadeIn" ,
                        open: function(event, ui)
                        {
                         CKEDITOR.replace( 'editor2', {
                                              toolbar : 'Basic',
                                              filebrowserImageUploadUrl: "reports/image-upload"
                           });
                        },
                        close: function(event, ui)
                        {
                            CKEDITOR.instances.editor2.destroy(false);
                            $(this).dialog('destroy');

                        }
                     });

                    $("#schedule-report").find("#email").val('');

                    $ ('.ui-widget-overlay').addClass('bg-black opacity-60');

                    $( "#schedule-report").find("#done-share").click(function(){
                        schedule_dialog.dialog('destroy');
                    })
                 });

              });
              $('#send-reportBtn').click(function() {
                $scope.closeWidget ();

                 var   SendCtrlScope = angular.element($("#send-report")).scope();

                  SendCtrlScope.report      = $scope.report;
                  SendCtrlScope.scheduled_list = Report.getScheduled({report_id:$scope.report.id});
                  SendCtrlScope.schedule_report.frequency = "send-now";
                  SendCtrlScope.schedule_report.from_email = currentUser.email;
                  SendCtrlScope.schedule_report.from_name = currentUser.name;
                  SendCtrlScope.quickSend = true;


                  $scope.doReportAction($scope.report, function() {

                   var send_dialog =  $("#send-report").dialog({
                        modal: true,
                        minWidth: 700,
                        minHeight: 150,
                        resizable:false,
                        dialogClass: "modal-dialog",
                        show: "fadeIn",
                        open: function(event, ui)
                        {
                         CKEDITOR.replace( 'editor3', {
                                              toolbar : 'Basic',
                           } );
                        },
                        close: function(event, ui)
                        {
                            CKEDITOR.instances.editor3.destroy(false);
                            $(this).dialog('destroy');

                        }
                     });

                    $("#send-report").find("#email").val('');

                    $ ('.ui-widget-overlay').addClass('bg-black opacity-60');

                    $("#send-report").find("#done-share").click(function(){
                        send_dialog.dialog('destroy');
                    })
                  });

              });
            $('#share-dashboard').click(function() {
                $scope.closeWidget();
              var   ShareCtrlScope = angular.element($("#share-report")).scope();

               ShareCtrlScope.report       = $scope.report;
               ShareCtrlScope.sharing_list = Report.getShared({report_id:$scope.report.id});

               $scope.doReportAction($scope.report, function() {
                var share_dialog =  $( "#share-report").dialog({
                     modal: true,
                     minWidth: 400,
                     minHeight: 200,
                     resizable:false,
                     dialogClass: "modal-dialog",
                     show: "fadeIn" ,
                     close: function(event, ui)
                     {
                         $(this).dialog('destroy');
                     }
                  });
                 $("#share-report").find("#email").val('');

                 $('.ui-widget-overlay').addClass('bg-black opacity-60');

                 $( "#share-report").find("#done-share").click(function(){
                     share_dialog.dialog('destroy');
                 });
               })

           });



    }

    $scope.openSideBar = function(selector) {
          if($("#page-sidebar").width()< 500) {
            $("#page-sidebar").animate({width:"500px"},'slow');
          }
          $(".sidebar").fadeOut();
          $("#"+selector).fadeIn();
          $(".sidebar-btn .wrapper").hide();
          $("#"+selector+"-btn .wrapper").show();

    }

    $scope.closeWidget = function() {
           $("#page-sidebar").animate({width:"200px"},'slow');
           $("#add-widget-sidebar").hide();
           $("#reoprt-options-sidebar").hide();
           $("#share-send-sidebar").hide();
           $(".sidebar-btn .wrapper").hide();
    }

    $scope.checkSource = function() {
       if($scope.curReport.channel.length == 0) {
           $scope.sourceError = true;
       } else {
           $scope.sourceError = false;

       }
    }

    $scope.loadWidgetType = function(type) {

            $scope.curReport.widgetType = type;
            $scope.graphError = false;
            $scope.tableError = false;
            $scope.piechartError   = false;
            $scope.widgetTypeError = false;

            if(angular.isDefined($scope.curReport.selectedSource)) {
                $scope.clientErr = false;
            } else {

               $scope.clientErr = true;
               return;
            }

           if( $scope.curReport.widgetType == 'table') {
            if($scope.curReport.channel=='googleanalytics')
              $scope.table = [
                              {'id' : 1,'title':'Source Medium'},
                              {'id' : 2,'title':'Geo'},
                              {'id' : 3,'title':'Site Content'},
                              {'id' : 4,'title' : 'E-Commerce'},
                              {'id' : 9,'title':'Channel Group'},
                              {'id' : 5,'title' : 'Campaign'},
                              {'id' : 7,'title':'Month on Month'},
                              {'id' : 8,'title':'Week on Week'},
                             
                            ];
            else if($scope.curReport.channel=='googleadwords')
              $scope.table = [
                                {'id':1,'title':'Campaign'},
                                {'id':3,'title':'Ad Group'},
                                {'id':4,'title':'AdCopy'},
                                {'id':5,'title':'Keyword'},
                                {'id':6,'title':'Search Query'},
                                {'id':7,'title':'Month on Month'},
                                {'id':8,'title':'Week on Week'},
                                {'id':9,'title':'Display Ad report'}

                            ];
            else if($scope.curReport.channel=='bingads')
              $scope.table = [
                                {'id':1,'title':'Campaign'},
                                {'id':3,'title':'Ad Group'},
                                {'id':4,'title':'AdCopy'},
                                {'id':5,'title':'Keyword'}
                            ];

           } else if( $scope.curReport.widgetType == 'graph') {
              if($scope.curReport.channel=='googleadwords')
                $scope.graph = [
                                {'id':1,'title':'Performance'},
                                {'id':2,'title':'Conversions'},
                                {'id':4,'title':'Competitive'}
                               ];
              else if($scope.curReport.channel=='googleanalytics')
                $scope.graph = [
                                {'id':1,'title':'Traffic'},
                                {'id':2,'title':'Goals'},
                                {'id':3,'title':'Ecommerce'}
                               ];
              else if($scope.curReport.channel=='bingads')
                $scope.graph = [
                                {'id':1,'title':'Performance'},
                                {'id':2,'title':'Conversions'}
                               ];
           } else if( $scope.curReport.widgetType == 'piechart') {
              if($scope.curReport.channel=='googleanalytics')
                $scope.piechart = [
                                {'id' : 1,'title':'Source Medium'},
                                {'id' : 2,'title':'Geo'},
                                {'id' : 3,'title':'Site Content'},
                                {'id' : 4,'title' : 'E-Commerce'},
                                 {'id' : 5,'title' : 'Campaign'}
                              ];
              else if($scope.curReport.channel=='googleadwords')
                $scope.piechart = [
                                  {'id':1,'title':'Campaign'},
                                  {'id':3,'title':'Ad Group'},
                                  {'id':4,'title':'AdCopy'},
                                  {'id':5,'title':'Keyword'},
                                  {'id':6,'title':'Search Query'}
                              ];
              else if($scope.curReport.channel=='bingads')
                $scope.piechart = [
                                  {'id':1,'title':'Campaign'},
                                  {'id':3,'title':'Ad Group'},
                                  {'id':4,'title':'AdCopy'},
                                  {'id':5,'title':'Keyword'}
                              ];
           }

            $(".type-header").css("background-color","#3d363c");
            $('#'+type).css("background-color","#faa71a");

            $(".type-submenu").slideUp();
            $(".type-submenu input[type='radio']").each(function() {
               $('#'+type).prop('checked', false);
            });
             $('#'+type).next().slideDown();

    }

    $scope.loadSources = function(type) {
         $scope.curReport.channel = type;
         $scope.insightBtn = false;
         $scope.clientList = [];
         $(".acc-icon").css("opacity", ".5");
         $(".arrow-up").hide();
         if (type == "googleadwords") {
            angular.forEach($scope.adwords.clients, function(value, key) {
                $scope.clientList.push({ id : value.id,
                                         name : value.name+" "+value.account_id});
            });
            $("#googleadwordsBtn").css({"opacity":"1"});
            left = $('#googleadwordsBtn').offset().left -
                    $('#googleadwordsBtn').parent().offset().left -
                    $('#googleadwordsBtn').parent().scrollLeft();
            $(".arrow-up").show();
            $(".arrow-up").animate({"margin-left" :(left +10)+"px"},"slow");
            $scope.sourceError = false;
         } else if (type == "googleanalytics" ) {
            angular.forEach($scope.analytics.clients, function(value, key) {
                $scope.clientList.push({id : value.id,
                                        name : value.name+" "+value.account_id});
            });
            $("#googleanalyticsBtn").css({"opacity": "1"});
             left = $('#googleanalyticsBtn').offset().left -
                     $('#googleanalyticsBtn').parent().offset().left -
                     $('#googleanalyticsBtn').parent().scrollLeft();
            $(".arrow-up").show();
            $(".arrow-up").animate({"margin-left" :(left +10)+"px"},"slow");
             $scope.sourceError = false;
             $scope.insightBtn = true;
         } else if (type == "bingads") {
            angular.forEach($scope.bing.clients, function(value, key) {
                $scope.clientList.push({
                                        id : value.id,
                                        name : value.name+" "+value.account_id});
            });
            $("#bingadsBtn").css("opacity", "1");
              left = $('#bingadsBtn').offset().left -
                  $('#bingadsBtn').parent().offset().left -
                  $('#bingadsBtn').parent().scrollLeft();
            $(".arrow-up").show();
            $(".arrow-up").animate({"margin-left" :(left +10)+"px"},"slow");
              $scope.sourceError = false;
         }

    }
    $scope.clientSelected = function(client) {
        $scope.curReport.selectedSource = client.id;
          $scope.clientError = false;
    }

    $scope.addWidget = function() {
        if(!angular.isDefined($scope.curReport.widgetType)) {
            $scope.widgetTypeError = true;
            return;
        } else {
             $scope.widgetTypeError = false;

            $scope.curReport.reportId = $routeParams.report_id;
            if ($scope.curReport.widgetType == "table") {
                $scope.curReport.reportTypeId = $("input:radio[name='table']:checked").val();
                if ( !angular.isDefined($scope.curReport.reportTypeId)) {
                    $scope.tableError = true;
                    return;
                }
            } else if ($scope.curReport.widgetType == "graph") {
                $scope.curReport.reportTypeId = $("input:radio[name='graph']:checked").val();
                if ( !angular.isDefined($scope.curReport.reportTypeId)) {
                    $scope.graphError = true;
                    return;
                }
            } else if ($scope.curReport.widgetType == "piechart") {
                $scope.curReport.reportTypeId = $("input:radio[name='piechart']:checked").val();
                if ( !angular.isDefined($scope.curReport.reportTypeId)) {
                    $scope.pieChartError = true;
                    return;
                }
            }         
            $rootScope.curReport = $scope.curReport;
            $scope.resetAddWidget();
            $scope.closeWidget();
            //console.log($rootScope.curReport);
            $location.path("/report/"+$routeParams.report_id+"/widget/new-widget");
        }
    }


}]);

JimmyDashboard.controller("NewTemplateCtrl",["$scope","$route","Template","FlashMessage",
    "$location", function($scope, $route,
    Template, FlashMessage, $location) {
        $scope.widgetSource = [];
        $scope.template=[];
        $scope.profile = {};
        $scope.campaign = {};
        $scope.save  = function(name) {

            if ($('#saveTemplate').parsley( 'validate' )) {
                Template.create({templateName :name, reportId : $scope.report.id, userId : $scope.report.user},
                        function(data){
                                 FlashMessage.setMessage(data);
                                 $("#new-template").dialog('destroy');
                        });
            }
        };
       $scope.deleteTemplate = function(template) {
           if (!template) {
               FlashMessage.setMessage({success: false, message: "Please Select a template"});
           } else {
            Template.delete ({ templateId  :  template.id}, function(data) {
                if (data.success) {
                    var index = $scope.templates.indexOf(template);
                    $scope.templates.splice(index, 1);
                    $scope.template.selected = '';
                }
                FlashMessage.setMessage(data);

            });
           }
       }



        $scope.useTemplate = function(template) {

            if ($('#use-template-form').parsley( 'validate' )) {
             $scope.clients = {};
             angular.forEach($scope.widgetSource, function(value, key) {
                $scope.clients[key] = value.id;
             });

                Template.use({ templateId : template.id,
                               campaign : $scope.campaign,
                               profile: $scope.profile,
                               clients : $scope.clients,
                               clientAccId : $scope.report.clientAccId,
                               reportName : $scope.report.name,
                               reportId : $scope.report.id },
                        function(data) {
                                if ($scope.report.name) {
                                     $location.path('/report/'+data.reportId);
                                } else {
                                    $route.reload();

                                }
                                $("#list-templates").dialog('destroy');
                                FlashMessage.setMessage(data);
                        }
                    );

                }
            }

            $scope.copy = function (widgetId, channel) {
                var source = $scope.widgetSource[widgetId];

                if (channel == 'googleadwords') {
                    var campaign = $scope.campaign[widgetId];
                    $scope.campaign = {};
                }
                if (channel == 'googleanalytics') {
                    var profile = $scope.profile[widgetId];
                    $scope.profile = {};
                }

                angular.forEach($scope.template.selected.widgets, function(value, key) {
                     if (value.channel == channel) {
                            $scope.widgetSource[value.id] = source;

                       if (channel == 'googleadwords') {
                          $scope.campaign[value.id] = campaign;

                       }
                       if (channel == 'googleanalytics') {
                           if(profile) {
                             $scope.profile[value.id] = profile;
                           } else {
                               $scope.profile[value.id] = null;
                           }
                       }
                    }
                });

            }
}]);



JimmyDashboard.controller("NewWidgetCtrl",["$scope","$location","CampaignList",
    "ProfileList","SegmentList","FlashMessage", "MetricsOptions", "$rootScope","Widget",
    "GoalsList", "Insight", function($scope, $location, CampaignList, ProfileList, 
    SegmentList, FlashMessage, MetricsOptions, $rootScope, Widget, GoalsList, Insight) {

    $scope.new_report    = true;
    $scope.show_wizard   = false;
    $scope.report        = {};
    $scope.report.widget = {};
    $scope.report.widget.device_type  = [];
    $scope.report.widget.network_type = [];
    $scope.report.widget.type = '';
    $scope.metrics            = null;
    $scope.client = {};
    $scope.selectedMetrics    = [];
    $scope.saveDisabled = false;
    $scope.can_create   = true;
    $scope.campaigns = {};
    $scope.metrics_compare  = null;
    $scope.widget_type_single = false;
    $scope.isdaterange=true;
    $scope.report.onecol=true;
    


    $scope.selectSource = function() {
        $scope.report.widget.client_account_id =  $rootScope.curReport.selectedSource;
        var sourceId = $rootScope.curReport.selectedSource ;
        $scope.report.channel = $rootScope.curReport.channel;
        $scope.report.widget.type = $rootScope.curReport.widgetType;
        $scope.report.type = $rootScope.curReport.reportTypeId;
        $scope.report.widget.insight = $rootScope.curReport.insight;
        Insight.list({channel:$scope.report.channel}, function(data) {
            $scope.$watch("insightTypes", function() {
                       $scope.insightTypes = data.insights;
            });

        });
         
       if ($scope.report.channel=='googleadwords' || $scope.report.channel=='bingads') {
            $scope.campaigns = CampaignList.query({client_account_id:sourceId},function(){
                $scope.campaigns.loaded = true;
            });
        } else {
            $scope.profiles = ProfileList.query({client_account_id:sourceId},function(){
                $scope.profiles.loaded = true;
            });
            $scope.segments = SegmentList.query({client_account_id:sourceId},function(){
                $scope.segments.loaded = true;
            });
        }

      }

    $scope.cancel = function() {
       window.history.back();
    }

    $scope.dateSelected = function(val) {

           $scope.$apply(function() {
                if (val == 14) {
                    $scope.showCustom = true;
                } else {
                    $scope.showCustom = false;
                }

           });

    }

     $scope.selectReportType = function(report_type_id) {
      $scope.metrics  = [];
      MetricsOptions.query(function(data) {
            if (data.$resolved) {
                $scope.metricsoptions = data;
                $scope.date_ranges = data.date_ranges;
                $scope.month_ranges = data.month_ranges;
                $scope.week_ranges = data.week_ranges;
                if($scope.report.widget.type == "table" || $scope.report.widget.type == "kpi" || $scope.report.widget.type == "piechart" ) {
                 if ($scope.report.channel == "googleadwords") {
                       $scope.report.widget.report_type = report_type_id ;
                       $scope.metrics = data.metrics[$scope.report.channel][$scope.report.widget.type];
                       if(report_type_id == 4) {
                                       $scope.metrics = data.metrics[$scope.report.channel][$scope.report.widget.type]
                                            .filter(function(el) {
                                                return el.id !== 11;
                                            });

                       } else if(report_type_id == 5) {
                                $scope.metrics = data.metrics[$scope.report.channel][$scope.report.widget.type]
                                            .filter(function(value) {
                                              return (value.id !== 12 && value.id !== 13 && value.id !== 16
                                                       && value.id !== 17 && value.id !== 18 && value.id !== 19);
                                            });


                      } else if(report_type_id == 3) {
                         $scope.metrics = data.metrics[$scope.report.channel][$scope.report.widget.type]
                                            .filter(function(el) {
                                                return el.id !== 15;
                                            });
                      } else if (report_type_id === 7 || report_type_id === 8)
                      {
                        $scope.metrics = data.metrics[$scope.report.channel]['kpi'];
                        $scope.isdaterange=false;
                      }
                      else if(report_type_id == 9) {
                                       $scope.metrics = data.metrics[$scope.report.channel][$scope.report.widget.type]
                                            .filter(function(el) {
                                                return el.id !== 11;
                                            });
                      }
                    } else {
                          if ($scope.report.channel=="googleanalytics" && $scope.report.widget.type != "kpi")  {
                              var mapping = {1 : 0, 2 : 1, 3 : 2, 4 : 3, 5: 0, 7:5, 8:5, 9:0};

                              $scope.metrics = data.metrics[$scope.report.channel][$scope.report.widget.type][mapping[report_type_id]];
                              $scope.report.widget.metrics_type = report_type_id;
                              $scope.report.widget.report_type = report_type_id; //This is not the best way of doing it.
                          } else {
                              $scope.metrics = data.metrics[$scope.report.channel][$scope.report.widget.type];

                          }
                }

              } else {
                  if ($scope.report.channel=="googleadwords") {
                        var mapping = {1 : 0, 2 : 1, 4 : 2};
                    } else if($scope.report.channel=="googleanalytics")  {
                        var mapping = {1 : 0, 2 : 1, 3 : 2};
                    } else if($scope.report.channel=="bingads") {
                        var mapping = {1 : 0, 2 : 1};
                    }
                $scope.metrics = data.metrics[$scope.report.channel][$scope.report.widget.type][mapping[report_type_id]];
                $scope.report.widget.metrics_type = report_type_id;
              }
            }
        });

    }

    $scope.selectMetricTypeCompare = function(val){
        $scope.metrics_compare  = $scope.metricsoptions.metrics[$scope.report.channel][$scope.report.widget.type][val];

        return true;
    }

    $scope.loadGoals = function() {

        angular.forEach($scope.profiles, function(value, key) {
            if(value.id== $scope.report.widget.profile_id)
              $scope.report.widget.currency = value.currency;
        });

        $scope.goals = GoalsList.query({ profile_id:$scope.report.widget.profile_id,
            client_account_id:$scope.report.widget.client_account_id},function(){
                 $scope.goals.loaded = true;
            });
    }


    $scope.selectMetricType = function(val){
        $scope.metrics  = null;
        $scope.metrics = $scope.metricsoptions.metrics[$scope.report.channel]
                            [$scope.report.widget.type][val];

        return true;
    }

    $scope.save = function() {
         if(  $("#newWidgetForm").parsley( 'validate' )) {
            if(angular.isDefined($scope.goals)) {
              $scope.report.widget.goals_list = $scope.goals;
            }
             
              $scope.report.widget.report_id = $rootScope.curReport.reportId;
              $scope.report.widget.period = ($scope.report.widget.report_type==7) ? 
                $scope.report.widget.month_range :
                  (($scope.report.widget.report_type==8) ?
                      $scope.report.widget.week_range :
                      $scope.report.widget.date_range
                  );
              Widget.save($scope.report.widget, function(data) {
                  FlashMessage.setMessage(data);

                  if(data.success) {
                    $location.path('/report/'+data.report_id);
                    $scope.report = {};
                  }
             });
         } else {
            $("#newWidgetForm").parsley( 'validate' );
             return;
         }

    }
    $scope.selectSource();
    if ($scope.report.type<7 && $scope.report.widget.type == "table") {
          $scope.report.onecol=true;
        } else if($scope.report.type==9 && $scope.report.widget.type == "table"){
          $scope.report.onecol=true;
        } else {
          $scope.report.onecol=false;
        }


     if($scope.report.widget.type=='kpi') {
              $scope.widget_type_kpi    = true;
              $scope.widget_type_single = false;
              $scope.report.onecol=true;
           } else if($scope.report.widget.type=='table') {
             $scope.widget_type_table  = true;
             $scope.widget_type_single = false;
            if($scope.report.channel=='googleanalytics')
              $scope.metrics_types = [
                                      {'id':1,'title':'Source Medium'},
                                      {'id':2,'title':'Geo'},
                                      {'id':3,'title':'Site Content'}
                                     ];
            else if($scope.report.channel=='googleadwords')
              $scope.report_types = [
                                     {'id':1,'title':'Campaign'},
                                     {'id':3,'title':'Ad Group'},
                                     {'id':4,'title':'AdCopy'},
                                     {'id':5,'title':'Keyword'},
                                     {'id':6,'title':'Search Query'},
                                      {'id':9,'title':'Display Ad report'}
                                    ];
            else if($scope.report.channel=='bingads')
              $scope.report_types = [
                                     {'id':1,'title':'Campaign'},
                                     {'id':3,'title':'Ad Group'},
                                     {'id':4,'title':'AdCopy'},
                                     {'id':5,'title':'Keyword'}
                                    ];

          } else if($scope.report.widget.type=='graph') {
               $scope.widget_type_graph  = true;
               $scope.widget_type_single = true;
              if($scope.report.channel=='googleadwords')
                $scope.metrics_types = [
                                        {'id':1,'title':'Performance'},
                                        {'id':2,'title':'Conversions'},
                                        {'id':4,'title':'Competitive'}
                                       ];
              else if($scope.report.channel=='googleanalytics')
                $scope.metrics_types = [
                                        {'id':1,'title':'Traffic'},
                                        {'id':2,'title':'Goals'},
                                        {'id':3,'title':'Ecommerce'}
                                       ];
              else if($scope.report.channel=='bingads')
                $scope.metrics_types = [
                                        {'id':1,'title':'Performance'},
                                        {'id':2,'title':'Conversions'}
                                       ];
          } else if($scope.report.widget.type=='piechart') {
               $scope.widget_type_piechart  = true;
               $scope.widget_type_single = true;

               if($scope.report.channel=='googleanalytics')
                  $scope.metrics_types = [
                                          {'id':1,'title':'Source Medium'},
                                          {'id':2,'title':'Geo'},
                                          {'id':3,'title':'Site Content'}
                                         ];
              else if($scope.report.channel=='googleadwords')
                $scope.report_types = [
                                       {'id':1,'title':'Campaign'},
                                       {'id':3,'title':'Ad Group'},
                                       {'id':4,'title':'AdCopy'},
                                       {'id':5,'title':'Keyword'},
                                       {'id':6,'title':'Search Query'}
                                    ];
              else if($scope.report.channel=='bingads')
                $scope.report_types = [
                                       {'id':1,'title':'Campaign'},
                                       {'id':3,'title':'Ad Group'},
                                       {'id':4,'title':'AdCopy'},
                                       {'id':5,'title':'Keyword'}
                                      ];
        }
        $scope.selectReportType($scope.report.type);

       $scope.date_range_compare = [
                                    {id:'previous_period',title:'Previous Period'},
                                    {id:'custom',title:'Custom'}
                                   ];

}]);

JimmyDashboard.controller('UpgradeReportCtrl', [
  '$scope', '$rootScope', '$location', '$resource', "FlashMessage", "GeneralInfo", "Report", "BraintreePayment",
  function($scope, $rootScope, $location, $resource, FlashMessage, GeneralInfo, Report, BraintreePayment){
    $scope.pendingReport = {};
    $scope.pendingAction - null;
    $scope.billing = {};
    $scope.cc = {};
    $scope.billingReportsCount = 0;

    $scope.saveDisabled = false;

    $scope.initDialogs = function() {
      $scope.upgradeAlertDialog = $('#upgradeAlertDialog').dialog({
        autoOpen: false,
        dialogClass: 'upgrade-alert-dialog',
        modal: true,
        height: 495,
        width: 595,
        create: function(event, ui) {
          var widget = $(this).dialog("widget");
          $(".ui-dialog-titlebar-close", widget)
              .addClass("closeBtn")
              .html('&nbsp;');
        },
      });

      $scope.createReportDialog = $('#createReportDialog').dialog({
        autoOpen: false,
        dialogClass: 'upgrade-alert-dialog',
        modal: true,
        height: 495,
        width: 595,
        create: function(event, ui) {
          var widget = $(this).dialog("widget");
          $(".ui-dialog-titlebar-close", widget)
              .removeClass("ui-icon-closethick")
              .addClass("closeBtn")
              .html('&nbsp;');
        }
      });

      $scope.upgradePackageDialog = $('#upgradePackageDialog').dialog({
          autoOpen: false,
          dialogClass: 'upgrade-package-dialog',
          modal: true,
          height: 500,
          width: 780,
          create: function(event, ui) {
            var widget = $(this).dialog("widget");
            $(".ui-dialog-titlebar-close", widget)
                .removeClass("ui-icon-closethick")
                .addClass("closeBtn")
                .html('&nbsp;');
          }
      });
    };

    $scope.closeAllDialogs = function() {
      if($scope.upgradeAlertDialog.dialog('isOpen'))
        $scope.upgradeAlertDialog.dialog('close');
      if($scope.createReportDialog.dialog('isOpen'))
        $scope.createReportDialog.dialog('close');
      if($scope.upgradePackageDialog.dialog('isOpen'))
        $scope.upgradePackageDialog.dialog('close');
    }

    $scope.toInt = function(val) {
      return parseInt(val);
    }

    $scope.showUser = function() {
      $scope.closeAllDialogs();
      $location.path('/user');
    }

    $scope.addReport = function(report) {
      $scope.pendingReport = report;
      $scope.billingReportsCount = $rootScope.generalInfo.templates_used + 1;
      if($scope.isPaymentRequired()) {
        if($rootScope.generalInfo.package.id!=14) {
          $scope.showCreateReportDialog();
        }
      } else {
        $scope.save(report);
      }
    }

    $scope.validateBilling = function() {
      console.log($scope.billing.country);
       _kmq.push(['record', 'Continue Payment Form']); //Kissmetrics call
      if($scope.billing.country==''||$scope.billing.country==undefined) {
        $('#billing_country').addClass('parsley-error');
      } else {
        $scope.isOnBillingAddress = false;
      }
    }

    $scope.savecc = function() {
      if($("#card-info-form").parsley( 'validate' )){
        _kmq.push(['record', 'Submit Payment Button']); //Kissmetrics call
        $scope.saveDisabled = true;
        BraintreePayment.getToken(function(data) {
          var client = new braintree.api.Client({
            clientToken: data.token
          });
          client.tokenizeCard({
            number: $scope.cc.cc_number,
            expirationDate: $scope.cc.cc_exp_month+'/'+$scope.cc.cc_exp_year
          }, function(err, nonce) {
            var userInfo = {
              firstname: $scope.cc.firstname,
              lastname: $scope.cc.lastname,
              streetaddress: $scope.billing.address,
              state: $scope.billing.state,
              country: $scope.billing.country,
              postalCode: $scope.billing.zipcode,
            }

            BraintreePayment.subscribe({
              userInfo: userInfo,
              nonce: nonce
            }, function(data) {
              if(!data.success) {
                FlashMessage.setMessage({message:'Failed to add card.', success:false});
              } else {
                $scope.$emit('billing-info-save', true);

                $scope.billing = {};
                $scope.cc = {};
                if($('#upgradePackageDialog').dialog('isOpen')==true) {
                  $('#upgradePackageDialog').dialog('close');
                }
                $scope.isOnBillingAddress = true;

                GeneralInfo.query(function(generalInfo){
                  $rootScope.generalInfo =  generalInfo;
                  $scope.creditCard = $rootScope.generalInfo.current_user.credit_card;
                });

                $rootScope.$broadcast('upgradation_successful');
              }
              $scope.saveDisabled = false;
            });
          })
        });
      }
    }

    $scope.showUpgradeDialogDirectly = function() {
      $scope.isOnBillingAddress = true;
      $scope.billingReportsCount = 0;
      $('#upgradePackageDialog').dialog('open');
      $ ('.ui-widget-overlay').addClass('bg-black opacity-60');
    }

    $scope.showUpgradeDialog = function(modal) {
      _kmq.push(['record', 'Add $5 Report']);
      $scope.closeAllDialogs();
      $scope.isOnBillingAddress = true;
      $scope.upgradePackageDialog.dialog('open');
      $ ('.ui-widget-overlay').addClass('bg-black opacity-60');
    }

    $scope.showPaymentAlertDialog = function(action) {
      $scope.upgradeAlertDialog.dialog('open');
      $ ('.ui-widget-overlay').addClass('bg-black opacity-60');
      $scope.pendingAction = action;
    }

    $scope.showPaymentReminderDialog = function() {
      $scope.paymentAlertDialog.dialog('open');
      $ ('.ui-widget-overlay').addClass('bg-black opacity-60');
    }

    $scope.showCreateReportDialog = function() {
      $scope.createReportDialog.dialog('open');
      $ ('.ui-widget-overlay').addClass('bg-black opacity-60');
    }

    $scope.addReport = function() {
      $scope.createReportDialog.dialog('close');
      $rootScope.$broadcast('add_report');
    }
  }
])

JimmyDashboard.controller("CreateReportCtrl",["ClientList", "Report",
                            "$scope", "FlashMessage","$location", "$rootScope", "$resource",
                            "GeneralInfo","$timeout", "Template", "ClientSourceList", "ProfileList", "CampaignList",
                            function(ClientList, Report, $scope,
                          FlashMessage,$location, $rootScope, $resource, GeneralInfo, $timeout, Template, ClientSourceList, ProfileList, CampaignList) {
        $scope.clients = {};

        $scope.report = {};
        $scope.campaign = {};
        $scope.profile = {};
        $scope.pendingReport = {};
        $scope.billing = {};
        $scope.cc = {};
        $scope.titleError = false;
        $scope.clientError = false;
        $scope.isOnBillingAddress = true;
        $scope.template=[];
        $scope.adwordsChannel = [];
        $scope.analyticsChannel = [];
        $scope.bingChannel = [];
        $scope.template.selected = "";
        $scope.clientSources = {};
        $scope.requests = [];
        $scope.widgetSource = [];
        $scope.showTemplateOptions = false;
        $scope.templateOptionsAvailability = false;
        $scope.templates = Template.list({userId : $rootScope.generalInfo.current_user.id});

        $scope.$on('upgradation_successful', function() {
          if($scope.showTemplateOptions) {
            $scope.useTemplate($scope.template.selected);
          } else {
            $scope.save($scope.report);
          }
        });



        $scope.$on('add_report', function() {
          if($scope.showTemplateOptions) {
            $scope.useTemplate($scope.template.selected);
          } else {
            $scope.save($scope.report);
          }
        });


        $scope.showTutorialIfRequired = function() {
          if(!!$rootScope.moveToReportCreation) {
            var createReportTourSteps =  [
                              {
                                'click #createNewReportForm' : "Now name your report and start building reports!",
                                'showSkip': false
                              }

                          ]
                    var createReportTour = new EnjoyHint({});
                    createReportTour.set(createReportTourSteps);
                    createReportTour.run();
            $rootScope.moveToReportCreation = false;
          }
          $('#reportName').focus();
        }

        $scope.showTutorialIfRequired();

        $scope.generateReport = function() {
          if ($("#createNewReportForm").parsley('validate')) {
            _kmq.push(['record', 'Generate report Button Clicked']); //kissmetrics tracking!

            if($scope.isPaymentRequired()) {
              if($scope.isUpgradeRequired()) {
                $rootScope.$broadcast('show_create_report_dialog');
              } else {
                if($scope.showTemplateOptions) {
                  $scope.useTemplate($scope.template.selected);
                } else {
                  $scope.save($scope.report);
                }
              }
            } else {
              if($scope.showTemplateOptions) {
                $scope.useTemplate($scope.template.selected);
              } else {
                $scope.save($scope.report);
              }
            }
          }
        }

        $scope.logIt = function() {
          console.log($scope.profile);
        }

        $scope.$on('cfpLoadingBar:loading', function() {

                     $('.loading-gif').show();
                    });

        $scope.$on('cfpLoadingBar:completed', function() {

             $('.loading-gif').hide();

        });
        // Selects template
        $scope.selectTemplate = function(template) {
          // blainking template selected
          angular.forEach($scope.templates , function(t) {
            t.isSelected = false;
          });
          $scope.template.selected = {};
          $scope.widgetSource = [];

          template.isSelected = true;
          $scope.template.selected = template;
        }

        $scope.blankTemplateSelections = function() {
          // important this is a blanker procedure
          // this function saves lives!
          // clear template selections when show templates selection is closed
          $scope.template.selected = {};
          angular.forEach($scope.templates , function(t) {
            t.isSelected = false;
          });
        }

        $scope.toggleTemplateOptions = function() {
          $scope.showTemplateOptions = !$scope.showTemplateOptions;
          if(!$scope.showTemplateOptions) {
               _kmq.push(['record', 'Cancel Template Grey']); //Kissmetrics call
            $scope.blankTemplateSelections();
          } else {
               _kmq.push(['record', 'Create using template']); //Kissmetrics call
          }
        }

        $scope.checkClientSelected = function() {
          if($scope.report.title.length>0) {
            if(!!$scope.report.user_id) {
              $scope.loadClientSources();
            }
          }
        }

        // Loads client sources
        $scope.loadClientSources = function() {
          console.log('loading client sources');
          // initial blanker
          $scope.showTemplateOptions = false;
          $scope.blankTemplateSelections();
          $scope.showClientSourceLoading = true;
          $scope.templateOptionsAvailability = false;
           $scope.widgetSource = {};
           $scope.campaign = {};
           $scope.profile ={};

           // important blankers
           $scope.clientSources = [];
           $scope.adwordsChannel = [];
           $scope.analyticsChannel = [];
           $scope.bingChannel = [];

          ClientSourceList.query({client_id:$scope.report.user_id},function(data) {
             $scope.clientSources = data;
             $scope.afterClientSourcesFetched();
             $scope.showClientSourceLoading = false;
             $scope.templateOptionsAvailability = true;
          });
        }

        $scope.selectWidgetSource = function(sourceId, widgetId) {
          if(sourceId==null) {
            $scope.campaign[widgetId] = {};
          } else {
            // we are blanking again
            // you will love us for this
            $scope.widgetSource[widgetId] = {};

            // here we are doing what this function is supposed to do
            // previously we were saveing your a**
            $scope.widgetSource[widget.id] = sourceId;
          }

          //TODO Handle loading of profiles and campaigs in case of multiple sources

        }

//        /**
//         * Copies the current camapaigs to all other adword widgets
//         **/
//        $scope.copyAdwordCampaignsToAllWidgets = function(widgetId) {
//
//          var theLatestCampaigns = $scope.campaign[widgetId];
//          $scope.campaign = {};
//          var theLatestWidgetSource = $scope.widgetSource[widgetId];
//
//          $scope.template.selected.widgets.filter(function(w) {
//            return w.channel == 'googleadwords';
//          }).forEach(function(w) {
//            // yet another time we save the earth by blanking the proper variables
//              //$scope.widgetSource[w.id] = null;
//
//            $scope.widgetSource[w.id] = theLatestWidgetSource;
//            $scope.campaign[w.id] = theLatestCampaigns;
//          });
//        }
//
//
//        /**
//         * Copies the current profiles to all other analytics widgets
//         **/
//        $scope.copyAnalyticsProfilesToAllWidgets = function(widgetId) {
//
//          var theLatestProfiles = $scope.profile[widgetId];
//          $scope.profile = {};
//          var theLatestWidgetSource = $scope.widgetSource[widgetId]
//
//          $scope.template.selected.widgets.filter(function(w) {
//            return w.channel == 'googleanalytics';
//          }).forEach(function(w) {
//            // yet another time we save the earth by blanking the proper variables
//              $scope.widgetSource[w.id] = null;
//
//            $scope.widgetSource[w.id] = theLatestWidgetSource;
//            $scope.profile[w.id] = theLatestProfiles.id;
//          });
//        }

        $scope.copy = function (widgetId, channel) {
                 var source = $scope.widgetSource[widgetId];
                  /// $scope.widgetSource = {};
                if (channel == 'googleadwords') {
                    var campaign = $scope.campaign[widgetId];
                    $scope.campaign = {};
                }
                if (channel == 'googleanalytics') {
                    var profile = $scope.profile[widgetId];
                    $scope.profile = {};
                    $scope.currency = {};

                }
                angular.forEach($scope.template.selected.widgets, function(value, key) {
                    if (value.channel == channel) {
                        if($scope.widgetSource[value.id] != source) {
                            $scope.widgetSource[value.id] = source;
                        }
                        if (channel == 'googleadwords') {

                          $scope.campaign[value.id] = campaign;
                        }
                       if (channel == 'googleanalytics') {
                           if(profile) {

                             $scope.profile[value.id] = profile.id;
                             $scope.currency[value.id] = profile.currency;
                           } else {
                               $scope.profile[value.id] = null;
                           }
                       }
                    }
                });




        }


        $scope.useTemplate = function(template) {
            if ($('#createNewReportForm').parsley( 'validate' )) {
             // $scope.clients = {};
             angular.forEach($scope.widgetSource, function(value, key) {
                $scope.clients[key] = value.id;
             });
             //this is to check if a copy to all widgets is used for profiles.
             //I know this is weird and not the best peactice. but this is just a bandage fix.
             if ($scope.currency) {
                 var profileTemp = {};
                 angular.forEach($scope.currency, function(value, key) {
                     profileTemp[key] ={id:$scope.profile[key], currency:value};
                 });
                 $scope.profile = profileTemp;

             }

            Template.use({ templateId : $scope.template.selected.id,
                       campaign : $scope.campaign,
                       profile: $scope.profile,
                       clients : $scope.clients,
                       clientAccId : $scope.report.user_id,
                       reportName : $scope.report.title,
                       reportId : $scope.report.id },
                        function(data) {
                          FlashMessage.setMessage(data);
                          if ($scope.report.title) {
                               $location.path('/report/'+data.reportId);
                          } else {
                              $route.reload();

                          }
                        });

              }

            }

        if($rootScope.generalInfo.package.id == $rootScope.generalInfo.unlimited_package.id) {
            $scope.can_create = true;
        } else if($rootScope.generalInfo.templates_used < $rootScope.generalInfo.package.templates_allowed || !$scope.new_report){
           $scope.can_create = true;
        } else {
           $scope.can_create = false;

        }

        if($rootScope.generalInfo.current_user.credit_card) {
          $scope.creditCard = $rootScope.generalInfo.current_user.credit_card;
        }

        $scope.clientsList  = ClientList.query(function() {
            $scope.clientsList.loaded = true;
            if($scope.clientsList.length==1) {
              $scope.report.user_id = $scope.clientsList[0].id;
            }
        });

       $scope.$on('client_saved', function() {

          $scope.clientsList = ClientList.query(function() {
                      $scope.clientsList.loaded  = true;;
          })
       });

       $scope.afterClientSourcesFetched = function() {

        angular.forEach($scope.clientSources, function(value, key) {


            if (value.channel =="googleadwords") {
                  var campaigns = CampaignList.query({client_account_id:value.id},function() {
                      $scope.adwordsChannel.push({"id": value.id, "client_id" : value.client_id,
                                     "name": value.name, "account_id" : value.account_id,
                                     "campaign_list" : campaigns});


                  });
                  $scope.requests.push($scope.adwordsChannel);

            } else if (value.channel == "googleanalytics") {
                  var profileList = ProfileList.query({client_account_id: value.id}, function() {
                      $scope.analyticsChannel.push({"id": value.id, "client_id" : value.client_id,
                                     "name": value.name, "account_id" : value.account_id,
                                     "profile_list": profileList});

                  });
                  $scope.requests.push($scope.analyticsChannel);
            } else if (value.channel == "bingads" ) {

                      var campaigns = CampaignList.query({client_account_id:value.id}, function() {
                          $scope.bingChannel.push({"id": value.id, "client_id" : value.client_id,
                                  "name": value.name, "account_id" : value.account_id,
                                   "campaign_list" : campaigns});

                      });
                     $scope.requests.push($scope.bingChannel);
            }

        });

       }



       $scope.$watch('templateUser');


       $scope.setUser = function(user_id) {
        console.log('setUser called');
          if (user_id !='') {
            console.log($scope.templateUser);
                 $scope.$watch("templateUser", function() {
                     $scope.templateUser = user_id;
                 });

                 $("#use-template").prop('disabled', false);

          } else {
               $("#use-template").prop('disabled', true);
          }
       }

       $scope.$emit('breadcrumbs_ready', {
        crumbs: [
          {
            title: 'Dashboard',
            url: '/',
            class: ''
          },
          {
            title: 'New Report',
            url: '',
            class: 'active'
          }
        ]
      });


        $scope.isPaymentRequired = function() {
          if($rootScope.generalInfo.package.id==13)
            return false;
          if($rootScope.generalInfo.package.id==14)
            return true;
          console.log($rootScope.generalInfo.templates_used+' '+$rootScope.generalInfo.package.templates_allowed);
          if([5,15].indexOf($rootScope.generalInfo.package.id)!=-1) {
            if($rootScope.generalInfo.templates_used>=$rootScope.generalInfo.package.templates_allowed)
              return true;
          }
          return false
        }

        $scope.isUpgradeRequired = function() {
          if($rootScope.generalInfo.package.id==13)
            return false;
          if($rootScope.generalInfo.package.id==14)
            return false;
          if([5,15].indexOf($rootScope.generalInfo.package.id)!=-1)
            return true;
          return false
        }

        $scope.save = function(report) {
          $scope.report = report;
          $scope.report.new_report = true;
          Report.save($scope.report, function(data){
              FlashMessage.setMessage(data);
              if(data.success){
                $rootScope.generalInfo.templates_used++;
                $rootScope.reportJustMade = true;
                $location.path('/report/'+data.report_id);

              }
          });

        }
}]);

JimmyDashboard.controller('NewReportCtrl',["$scope", "$rootScope", "$http", "$routeParams",
    "$location", "$timeout", "Widget", "CurrentReport", "FlashMessage", "Client", "ClientList",
    "ClientAccounts", "ClientSourceList", "CampaignList", "MetricsOptions", "ProfileList",
    "GoalsList", "Report", function($scope, $rootScope,$http,$routeParams,$location, $timeout,
    Widget,CurrentReport, FlashMessage, Client, ClientList, ClientAccounts, ClientSourceList,
    CampaignList,MetricsOptions,ProfileList,GoalsList,Report){

    $scope.new_report    = true;
    $scope.show_wizard   = false;

    $scope.report        = {};
    $scope.report.widget = {};
    $scope.report.widget.device_type  = [];
    $scope.report.widget.network_type = [];
    $scope.report.widget.type = '';
    $scope.metrics            = null;
    $scope.client = {};
    $scope.selectedMetrics    = [];
    $scope.saveDisabled = false;
    $scope.can_create   = true;

    $scope.listSources = function(fetchNew) {
        $scope.clientSources = CurrentReport.getSources();
        if(!$scope.clientSources || fetchNew)
            $scope.clientSources = ClientSourceList.query({client_id:$scope.report.user_id});
            $scope.$broadcast('report_client_selected',{client_id:$scope.report.user_id});

    }

    if(angular.isDefined($routeParams.report_id)) {
        $scope.new_report    = false;
        $scope.show_wizard   = true;
        $scope.report        = CurrentReport.getReport();

        // If report exists in the cache
        if(!$scope.report) {

            $scope.report    = Report.get({report_id:$routeParams.report_id},function(){
               $scope.listSources();

            //   $scope.listSources();
               $scope.report.widget = {};
               $scope.report.widget.device_type  = [];
               $scope.report.widget.network_type = [];
               $scope.report.widget.type = '';

            });
        } else {
           $scope.listSources();

           $scope.report.widget = {};
           $scope.report.widget.device_type  = [];
           $scope.report.widget.network_type = [];
           $scope.report.widget.type = '';
        }

    } else {

      if($rootScope.generalInfo.package.id == $rootScope.generalInfo.unlimited_package.id) {
        $scope.can_create = true;
      } else if($rootScope.generalInfo.templates_used < $rootScope.generalInfo.package.templates_allowed || !$scope.new_report){
        $scope.can_create = true;
      } else {
        $scope.can_create = false;
      }

      $scope.clients  = ClientList.query(function() {
        $scope.clients.loaded = true;
      });
    }

    $scope.$on('client_source_added',function(e,args) {
        $scope.clientSources = ClientSourceList.query({client_id:args.client_id});
    });


    $scope.$on('client_saved',function(e,client_id) {
        $scope.clients   = ClientList.query(function(){
                             $scope.clients.loaded = true;
        });
    });

    $scope.createReport = function() {
        if($('#report_title').parsley( 'validate' )) {
            $scope.show_wizard = true;
            return true;
        }
    }

    $scope.selectSource = function() {
        $scope.report.client_account_id = this.source.id;
        $scope.report.channel = this.source.channel;

        if($scope.report.channel=='googleadwords' || $scope.report.channel=='bingads') {
            $scope.campaigns = CampaignList.query({client_account_id:this.source.id},function(){
                $scope.campaigns.loaded = true;
            });

        } else {
            $scope.profiles = ProfileList.query({client_account_id:this.source.id},function(){
                $scope.profiles.loaded = true;
            });

        }
    }

    $scope.widget_type_table  = false;
    $scope.widget_type_graph  = false;
    $scope.widget_type_kpi    = false;
    $scope.widget_type_piechart    = false;
    $scope.widget_type_single  = false;

    $scope.metricsoptions = MetricsOptions.query();

    $scope.date_range_compare = [{id:'previous_period',title:'Previous Period'},
                                  {id:'custom',title:'Custom'}];

    $scope.selectWidget = function() {
            $scope.report.widget.type = $scope.report.widget_type;

            $scope.widget_type_table  = false;
            $scope.widget_type_graph  = false;
            $scope.widget_type_kpi    = false;

            $scope.date_ranges         = $scope.metricsoptions.date_ranges;

            $scope.report.widget.metric_type = null;
            $scope.metrics = null;
            $scope.metrics_compare  = null;

           if($scope.report.widget.type=='kpi'){
              $scope.widget_type_kpi    = true;
           } else if($scope.report.widget.type=='table'){
              $scope.widget_type_table  = true;
            if($scope.report.channel=='googleanalytics')
              $scope.metrics_types = [
                                      {'id':1,'title':'Source Medium'},
                                      {'id':2,'title':'Geo'},
                                      {'id':3,'title':'Site Content'}
                                     ];
            else if($scope.report.channel=='googleadwords')
              $scope.report_types = [
                                     {'id':1,'title':'Campaign'},
                                     {'id':3,'title':'Ad Group'},
                                     {'id':4,'title':'AdCopy'},
                                     {'id':5,'title':'Keyword'},
                                     {'id':6,'title':'Search Query'},
                                      {'id':9,'title':'Display Ad report'}
                                    ];
            else if($scope.report.channel=='bingads')
              $scope.report_types = [
                                     {'id':1,'title':'Campaign'},
                                     {'id':3,'title':'Ad Group'},
                                     {'id':4,'title':'AdCopy'},
                                     {'id':5,'title':'Keyword'}
                                    ];

          } else if($scope.report.widget.type=='graph'){
               $scope.widget_type_graph  = true;
              if($scope.report.channel=='googleadwords')
                $scope.metrics_types = [
                                        {'id':1,'title':'Performance'},
                                        {'id':2,'title':'Conversions'},
                                        {'id':4,'title':'Competitive'}
                                       ];
              else if($scope.report.channel=='googleanalytics')
                $scope.metrics_types = [
                                        {'id':1,'title':'Traffic'},
                                        {'id':2,'title':'Goals'},
                                        {'id':3,'title':'Ecommerce'}
                                       ];
              else if($scope.report.channel=='bingads')
                $scope.metrics_types = [
                                        {'id':1,'title':'Performance'},
                                        {'id':2,'title':'Conversions'}
                                       ];
          }

        $scope.metrics         = $scope.metricsoptions.metrics[$scope.report.channel][$scope.report.widget.type];
        $scope.metrics_compare = $scope.metricsoptions.metrics[$scope.report.channel][$scope.report.widget.type];

        angular.forEach($scope.report.widget.metrics, function(value, key){
            angular.forEach($scope.metrics,function(v,k){
              if(v.id == value){
                 $scope.selectedMetrics.push(v);
              }
            })
        });

        if($scope.report.widget.type=='kpi') {
          $scope.$broadcast('widget_selected');
        }
    }

    $scope.selectReportType = function(report_type_id){
        $scope.metrics  = [];

      if(report_type_id==4){

        angular.forEach($scope.metricsoptions.metrics[$scope.report.channel][$scope.report.widget.type], function(value, key){
              if(value.id != 11){ // Exclude the Search Impr Share When report type is Ad Copy
                 $scope.metrics.push(value);
              }
        });

      }  else if(report_type_id == 5) {
          angular.forEach($scope.metricsoptions.metrics[$scope.report.channel][$scope.report.widget.type], function(value, key){
              if(value.id != 12 && value.id != 13 && value.id != 16
                 && value.id != 17 && value.id != 18 && value.id != 19) { // Exclude the Search Impr Share When report type is Ad Copy
                 $scope.metrics.push(value);
              }
        });

      } else {
        $scope.metrics = $scope.metricsoptions.metrics[$scope.report.channel][$scope.report.widget.type];
      }

      $scope.$broadcast('widget_selected');

    }

    $scope.selectMetricType = function(val){
        $scope.metrics  = null;
        $scope.metrics = $scope.metricsoptions.metrics[$scope.report.channel][$scope.report.widget.type][val];

        $scope.$broadcast('widget_selected');

        return true;
    }

    $scope.displayGoals = function(val){


        return true;
    }

    $scope.selectMetricTypeCompare = function(val){
        $scope.metrics_compare  = $scope.metricsoptions.metrics[$scope.report.channel][$scope.report.widget.type][val];

        return true;
    }

    $scope.cancel = function() {
       window.history.back();
    }

    $scope.loadGoals = function() {

        angular.forEach($scope.profiles, function(value, key){
            if(value.id==$scope.report.widget.profile_id)
              $scope.report.widget.currency = value.currency;
        });

        $scope.goals = GoalsList.query({profile_id:$scope.report.widget.profile_id,
            client_account_id:$scope.report.widget.client_account_id},function(){
                 $scope.goals.loaded = true;
            });
    }

    $scope.save = function() {

      if(angular.isDefined($scope.goals))
        $scope.report.widget.goals_list = $scope.goals;

      if($scope.new_report) {
          Report.save($scope.report,function(data){
              FlashMessage.setMessage(data);
              if(data.success){
                $location.path('/report/'+data.report_id);
                $rootScope.generalInfo.templates_used++;
              }
          });
      } else {

        $scope.report.widget.report_id = $scope.report.id;

        Widget.save($scope.report.widget,function(data){
            FlashMessage.setMessage(data);

            if(data.success)
              $location.path('/report/'+data.report_id);

        });

      }
    }
}]);

/**
* formats dates retutned by Jimmy API
* assuming usual format of Jimmy API date is yyyy-MM-dd HH:mm
**/
JimmyDashboard.filter('dateFormat', function($filter) {
  return function(input, format) {
    var dateSplit = input.split(' ');
    input = dateSplit[0]+'T'+dateSplit[1]+'Z';
    return $filter('date')(input, format);
  };
});


JimmyDashboard.directive('onChange',["$location", function($location){

  return {
        scope:false,
        link:function($scope, element, attrs){
            var list    = attrs['chosenSelect'];
            var elem_id = attrs['onChange'];

             $(element).chosen()
              .change(function(e) {
                  var val                = $(element).chosen().val()
                  var newVal             = [];

                  $scope.selectedMetrics.length=0;

                  angular.forEach(val, function(value, key){
                      newVal.push($scope[list][value].title);
                      $scope.selectedMetrics.push($scope[list][value]);
                  });

                  $scope.selectedMetrics.loaded = true;
                  $scope.$apply();

                  if ($.inArray("Goal Completions",newVal)>=0 ||
                          $.inArray("Goal Conversion Rate",newVal)>=0 ||
                          $.inArray("Goal Value",newVal)>=0||
                          $.inArray("Goal Total Abandonment",newVal)>=0) {                 // console.log(newVal);
                      $("#"+elem_id).removeClass('ng-hide');
                      $("#"+elem_id).addClass('ng-show');
                  } else {
                      $scope.report.widget.goals = null;
                      $("#"+elem_id).removeClass('ng-show')
                      $("#"+elem_id).addClass('ng-hide');
                  }

              });
      }
  }
}]);

JimmyDashboard.directive('displayGoalsCompare',["$location", function($location){

  return {
        link:function($scope, element, attrs){
            var elem_id = attrs['displayGoalsCompare'];

           $(element).click(function(e) {
                if($scope.report.channel == 'googleanalytics' && $scope.report.widget.type=='graph' && $scope.report.widget.metrics_type_compare==2){
                    $("#"+elem_id).removeClass('ng-hide');
                    $("#"+elem_id).addClass('ng-show');
                } else {
                    $scope.report.widget.goals_compare = null;
                    $("#"+elem_id).removeClass('ng-show')
                    $("#"+elem_id).addClass('ng-hide');
                }
            });
      }
  }
}]);

JimmyDashboard.directive('chosenSelect',["$location", "$timeout", function($location, $timeout){
 return {
        require: '?ngModel',
        link: function($scope, element, attrs,ngModel){
            var list  = attrs['chosenSelect'];
            var watchAttr = attrs['watchResource'];

            $scope.$watchCollection('campaign', function() {
              $timeout(function() {
                $(element).chosen();
                $(element).trigger('chosen:updated');
              }, 0, false);
            });

            $scope.$watchCollection('profile', function() {
              $timeout(function() {
                $(element).chosen();
                $(element).trigger("liszt:updated");
                $(element).trigger('chosen:updated');
              }, 0, false);
            });

            $(element).chosen();

            if(watchAttr==null)
                watchAttr = list;


            $scope.$watch(watchAttr, function(){
                $(element).chosen();
                $(element).trigger("liszt:updated");
                $(element).trigger("chosen:updated");
            });

            $(element).chosen()
            .change(function(e) {
                var val    = $(element).chosen().val();
                var newVal = [];
               if ($scope[list]) {
                    if($(element).prop("multiple")){

                        angular.forEach(val, function(value, key) {
                            newVal[key] = $scope[list][value].id;
                        });

                    } else {
                      if($scope[list][val])
                        newVal = $scope[list][val].id;

                    }
            } else {// for templates!
                 if($(element).prop("multiple")){
                           angular.forEach(val, function(value, key) {
                            newVal[key] = $scope.widgetSource[list].campaign_list[value].id;
                        });

                    } else {
                      if($scope.widgetSource[list].profile_list[val])
                        newVal = {id: $scope.widgetSource[list].profile_list[val].id,
                                  currency: $scope.widgetSource[list].profile_list[val].currency};
                    }
            }
                ngModel.$setViewValue(newVal);
            });

        }
    }

}])

JimmyDashboard.directive('upgradeDialog',
  ["$location", "$timeout", "$rootScope","$resource", "FlashMessage", "GeneralInfo", "Report",
  function($location, $timeout, $rootScope, $resource, FlashMessage, GeneralInfo, Report) {
    return {
      require: '?ngModel',
      scope: false,
      restrict: 'A',
      link: function ($scope, element, attrs, controller) {
        var dialogsScope = angular.element($('#upgradeReportDialogs')).scope();
        dialogsScope.initDialogs();

        $scope.$on('show_upgrade_alert_dialog', function() {
          dialogsScope.billingReportsCount = $rootScope.generalInfo.templates_used;
          console.log(dialogsScope.billingReportsCount);
          dialogsScope.upgradeAlertDialog.dialog('open');
          $ ('.ui-widget-overlay').addClass('bg-black opacity-60');
        });

        $scope.$on('show_upgrade_package_dialog', function() {
          dialogsScope.upgradePackageDialog.dialog('open');
          $ ('.ui-widget-overlay').addClass('bg-black opacity-60');
        });

        $scope.$on('show_create_report_dialog', function() {
          dialogsScope.billingReportsCount = $rootScope.generalInfo.templates_used+1;
          console.log(dialogsScope.billingReportsCount);
          dialogsScope.createReportDialog.dialog('open');
          $ ('.ui-widget-overlay').addClass('bg-black opacity-60');
        })
      },
      templateUrl: 'src/app/dashboard/upgrade.html'
    }
}]);

JimmyDashboard.controller("ReportCtrl",["$q", "$scope", "$resource", "$rootScope",
                            "$http", "$routeParams", "$location", "$timeout",
                            "FlashMessage", "WidgetList", "Report", "ClientSourceList",
                            "ProfileList", "CampaignList", "MetricsOptions", "Widget",
                            "CurrentReport", "GoalsList","SegmentList","Tour","Client",
                            "Insight",
                            function($q,$scope,$resource, $rootScope,$http,$routeParams,
                            $location, $timeout,FlashMessage,WidgetList,Report,ClientSourceList,
                            ProfileList,CampaignList,MetricsOptions,Widget,CurrentReport,GoalsList,
                            SegmentList, Tour, Client, Insight){
    var timeout;
    $scope.template = "/src/app/widget/list.html";
    $scope.show_wizard   = false;
    $scope.device_types  = [];
    $scope.network_types = [];
    $scope.reload_after_reauthorization = false;
    $scope.saveDisabled  = false;
    $scope.showCustom  = false;
    $scope.natural = $rootScope.natural;
   
         


          $("payment-form").submit(function(e) {
              e.preventDefault();
          });


  $scope.data2 = 20;
    $scope.options2 = {
    width:"128",
    displayPrevious:true,
    thickness:".2",
        fgColor: "#e25357",
        angleOffset:'90',
    linecap:'round'
    }

  $scope.easypiechart = {
      percent: 65,
      options: {
        animate: {
          duration: 1000,
          enabled: true
        },
        barColor: '#31C0BE',
        lineCap: 'round',
        size: 180,
        lineWidth: 5
      }
    };

  /**
     * Data for Line chart
     */
    $scope.lineData = {
        labels: ["January", "February", "March", "April", "May", "June", "July"],
        datasets: [
            {
                label: "Example dataset",
                fillColor: "rgba(220,220,220,0.5)",
                strokeColor: "rgba(220,220,220,1)",
                pointColor: "rgba(220,220,220,1)",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(220,220,220,1)",
                data: [65, 59, 80, 81, 56, 55, 40]
            },
            {
                label: "Example dataset",
                fillColor: "rgba(26,179,148,0.5)",
                strokeColor: "rgba(26,179,148,0.7)",
                pointColor: "rgba(26,179,148,1)",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(26,179,148,1)",
                data: [28, 48, 40, 19, 86, 27, 90]
            }
        ]
    };

  $scope.lineOptions = {
        scaleShowGridLines : true,
        scaleGridLineColor : "rgba(0,0,0,.05)",
        scaleGridLineWidth : 1,
        bezierCurve : true,
        bezierCurveTension : 0.4,
        pointDot : true,
        pointDotRadius : 4,
        pointDotStrokeWidth : 1,
        pointHitDetectionRadius : 20,
        datasetStroke : true,
        datasetStrokeWidth : 2,
        datasetFill : true,
    };
  //


    $scope.metricsoptions = MetricsOptions.query();

    $scope.report  = Report.get({report_id:$routeParams.report_id },function(report){
        CurrentReport.setReport(report);
        $scope.client = Client.query({client_id:report.user_id }, function(client){
          $scope.$emit('breadcrumbs_ready', {
            crumbs: [
              {
                title: 'Dashboard',
                url: '/',
                class: ''
              },
              {
                title: 'Reports',
                url: '/reports',
                class: ''
              },
              {
                title: client.name,
                url: '/clients/'+client.client_id+'/reports',
                class: ''
              },
              {
                title: report.title,
                url: '',
                class: 'active'
              }
            ]
          });
        });

        if($rootScope.generalInfo.current_user.type!='user'){


            $scope.clientSources = ClientSourceList.query({client_id:report.user_id},function(){
                CurrentReport.setSources($scope.clientSources);

            });
        }
    });

    $scope.kpiwidgetsbg   = ['error-bg','success-bg','warning-bg','notice-bg','error-bg','success-bg','warning-bg','notice-bg','error-bg','success-bg','warning-bg','notice-bg','error-bg','success-bg','warning-bg','notice-bg',]

    if(!widgetCanceler)
        widgetCanceler = $q.defer();



    $scope.loadWidget  = function(widget_id,key,opParams){

        var params = {widget_id:widget_id};

        if(angular.isObject(opParams)) {
             if(angular.isDefined(opParams.min) && angular.isDefined(opParams.max)){
                params.min = opParams.min;
                params.max = opParams.max;
                params.date_range = opParams.date_range
            } else {
                params.date_range = opParams.date_range;
            }
        }
        $scope.widgets[key].loaded = false;
        $scope.widgets[key].error_msg = null;

        var WidgetRes = $resource("/widget/:widget_id",{}, {'get':{method:'GET',params:{widget_id:'@widget_id'},timeout:widgetCanceler.promise,isArray: false}});

        $scope.widgets[key].data = WidgetRes.get(params,function(data){
              //  alert(JSON.stringify($scope.widgets[key].data));
                $scope.widgets[key].loaded = true;
                $scope.$broadcast("widget-loaded");
                if(data.success==false){
                    $scope.widgets[key].error_msg = data.message;
                } else {

                if($scope.widgets[key].type=='table'){
                    var headers = new Array();
                    angular.forEach($scope.widgets[key].data.args.fields_raw_data, function(value,key) {
                        headers.push(value);
                    });
                    $scope.widgets[key].data.headers = headers;

                } else if($scope.widgets[key].type == 'graph'){
                        var totals      = $scope.widgets[key].data.totals;
                        var totals_comp = $scope.widgets[key].data.totals_comp;
                      //  $scope.widgets[key].graph_type = $scope.widgets[key].data.args.graph_type;

                     if($scope.widgets[key].data.args){
                        var yKeys  = ['y'];
                        var labels = [$scope.widgets[key].data.args.field[2]];

                        if($scope.widgets[key].data.args.field_compare){
                          yKeys[1]  = 'z';
                          labels[1] = $scope.widgets[key].data.args.field_compare[2];
                        }

                        $scope.widgets[key].chartData = {};
                        $scope.widgets[key].chartData = {
                               data: totals,
                               xkey: 'x',
                               ykeys: yKeys,
                               labels: labels,
                               barColors:['#e15258','#fa7753'],
                               lineColors:['#e15258','#fa7753'],
                               yMax:4,
                               parseTime: false,
                               integerYLabels: true,
                               hideHover: "auto",

                               hoverCallbacks:function(index, options, content){
                                  var row = "<b>"+options.data[index].x + "</b><br><b>" + options.labels[0] + "</b>: " + options.data[index].y;

                                  if(options.data[index].z && options.labels[1])
                                    row+= "<br><b>" + options.labels[1] + "</b>: " + options.data[index].z;

                                  return row;
                              }
                        }
                     }
                } else if($scope.widgets[key].type == 'piechart'){
                      var rawdata      = $scope.widgets[key].data.rawData;
                      var ctitle = $scope.widgets[key].data.args.ctitle; //Piechart Title  "Dimension by Metrics"
                    $scope.widgets[key].piechartOption  = {
                                      chart: {
                                          type: 'pieChart',
                                          height: 400,
                                          width:1000,
                                          x: function(d){return (d.key.length>35)?(d.key.substring(0,35)+"..."):(d.key);},
                                          y: function(d){return d.y;},
                                         // color: function(d){return d.color},
                                          showLabels: true,
                                          duration: 500,
                                          labelThreshold: 0.05,
                                          labelSunbeamLayout: false,
                                          donut: true,
                                          labelsOutside: true,
                                          donutRatio: 0.35,
                                          transitionDuration: 500,
                                          legendPosition: "vertical",
                                          margin: {
                                              top:50,
                                              left:100
                                          },
                                         legend: {
                                              vers: 'classic',
                                              padding: 10,
                                              margin: {
                                                  top: 10,
                                                  right: 35,
                                                  bottom: 20,
                                                  left: 10,
                                              },
                                          },
                                        tooltip: {
                                          enabled: true,
                                          valueFormatter: d3.format('g')
                                        }

                                      },
                                      title: {
                                              enable: true,
                                              text: ctitle
                                            },

                                  };
                  $scope.widgets[key].piechartData = rawdata; // Data for drawing piechart.
                 }
                 }


        });


    }

  /**
   * Checks if a field is filtered.
   * @param widget_id ID of the widget
   * @param key Name of the field in lowercase
   **/
  $scope.isFiltered = function(widget_id,key) {
    var widget = $scope.widgets.filter(function(w) {
      return w.id == widget_id;
    });
    widget = widget[0];
    if(widget.data.args.filter!=undefined) {
      if(key in widget.data.args.filter) {
        if(widget.data.args.filter[key].length!='') {
          return true;
        }
      }
    }
    return false;
  };

  /**
   * Finds a segment from the id of the segment
   * @param widget the widget
   */
  $scope.getSegmentName = function(widget) {
    if('segment' in widget) {
      var selectedSegment = widget.segments.filter(function(s) {
        return s.id == widget.segment;
      });
      if(selectedSegment.length>0) return selectedSegment[0].name;
    }
  };

  $scope.isUnlimitedUser = function() {
    var userPackage = generalInfo.unlimited_packages.filter(function(p) {
      return p.id == generalInfo.package.id;
    });
    if(userPackage.length>0) {
      return true;
    } else {
      return false;
    }
  };

  $scope.valuknob = 4;
  $scope.sho = 1;
  $scope.changeKPI = function (widget_index,kpi_index) {

      if(angular.isDefined($scope.widgets[widget_index].kpi_type)){

          if($scope.widgets[widget_index].kpi_type[kpi_index] == 1){
            $scope.widgets[widget_index].kpi_type[kpi_index] = 2;
          } else if($scope.widgets[widget_index].kpi_type[kpi_index] == 2){
            $scope.widgets[widget_index].kpi_type[kpi_index] = 3;
          } else if($scope.widgets[widget_index].kpi_type[kpi_index] == 3){
            $scope.widgets[widget_index].kpi_type[kpi_index] = 4;
          } else if($scope.widgets[widget_index].kpi_type[kpi_index] == 4){
            $scope.widgets[widget_index].kpi_type[kpi_index] = 5;
          } else {
            $scope.widgets[widget_index].kpi_type[kpi_index] = 1;
          }

          var widget = jQuery.extend(true, {}, $scope.widgets[widget_index]);

          delete widget.chartData;

          widget.report_id = $scope.report.id;
          return  Widget.update({widget_id:widget.id},widget,function(data){
                  FlashMessage.setMessage(data);
          });
      } else {
            $scope.widgets[widget_index].kpi_type = [];

            angular.forEach($scope.widgets[widget_index].metrics, function(metric, key){
                  $scope.widgets[widget_index].kpi_type[key] = 1;
            });
            $scope.changeKPI(widget_index,kpi_index);

      }

  }


  $scope.calcvalue  = function(vq, vs){



    var num1 = vq.toString();;
    num1 = num1.replace(/[\,\%]/,"");
    var num2 = vs.toString();
    num2 = num2.replace(/[\,\%]/,"");


    //console.log("feORI");
    //console.log(num1);
    //console.log("feORI");

    var val = Math.round(((num1*1-num2*1)/num2)*100,2);
    val = val.toString().replace(/-/,"")
    $scope.valuknob = val*1;

    /*if(isNaN(val)) {
           val ='n/a';
         } else if(val=='Infinity'){
           val ='n/a';
         } else if(val>=0){
           val+='% ';
         } else {
           val =  val.toString().replace(/-/,"");

           val+='% <i class="glyph-icon icon-long-arrow-down font-gray-dark"></i>';
         }*/

    console.log(val*1);
  }

    $scope.changeGraph = function(key){

        var chartData = Object.create($scope.widgets[key].chartData);
        console.log($scope.widgets[key]);
         $(chartData.element).html('');

        if(angular.isDefined($scope.widgets[key].graph_type)){
          if($scope.widgets[key].graph_type == 'line'){
            Morris.Area(chartData);
            $scope.widgets[key].graph_type='area';
          } else if($scope.widgets[key].graph_type == 'area'){
            Morris.Bar(chartData);
            $scope.widgets[key].graph_type='bar';
          } else {
            Morris.Line(chartData);
            $scope.widgets[key].graph_type='line';
          }
        } else {
          $scope.widgets[key].graph_type = 'line';
          Morris.Line(chartData);
        }

      //var widget = Object.create($scope.widgets[key]);
      var widget = jQuery.extend(true, {}, $scope.widgets[key]);

      delete widget.chartData;

      widget.report_id = $scope.report.id;
      return  Widget.update({widget_id:widget.id},widget,function(data){
              FlashMessage.setMessage(data);
      });
    }

    $scope.loadWidgets = function(){
       if(!campaignCanceler)
           campaignCanceler = $q.defer();

       if(!profileCanceler)
           profileCanceler = $q.defer();

        $scope.widgets = WidgetList.query({report_id:$routeParams.report_id },function(data){
            var campaignList  = $resource("/clients/campaigns/:client_account_id",{}, {'query':{method:'GET',params:{client_account_id:'@client_account_id'},timeout:campaignCanceler.promise,isArray: true}});
            var profileList   = $resource("/clients/profiles/:client_account_id",{}, {'query':{method:'GET',params:{client_account_id:'@client_account_id'},timeout:profileCanceler.promise,isArray: true}});
            $scope.widgetCount= 0;

            angular.forEach($scope.widgets, function(widget, key) {
                $scope.widgetCount++;
                if(widget.channel=='googleadwords')
                    $scope.campaigns =  campaignList.query({client_account_id:widget.client_account_id});
                else if(widget.channel=='googleanalytics') {
                    $scope.profiles  =  profileList.query({client_account_id:widget.client_account_id});
                    // if a segment key is present in widget
                    // list all segment for the widget in the widget itself
                    if(!!widget.segment) widget.segments = SegmentList.query({client_account_id:widget.client_account_id});
                }

                if(angular.isDefined($scope.reauth_source_id) && widget.client_account_id == $scope.reauth_source_id)
                  $scope.loadWidget(widget.id,key);
                else
                  $scope.loadWidget(widget.id,key);

            });

        });
    }

    $scope.widgetLoadCount=0;
    $scope.$on("widget-loaded", function(){
                $scope.widgetLoadCount++;
        if ($scope.widgetLoadCount == $scope.widgetCount) {
            var userId = $rootScope.generalInfo.current_user.id;
                 if ($rootScope.generalInfo.current_user.type != 'user') {
                    Tour.visitTour({tourName : "filter table" ,userId: userId }, function(data) {
                        if (!data.visited) {
                            $timeout(function() {
                                createTourSteps =  [
                                {'click .filter:first' : "Apply Filters to the table content.\n\
                                                           Click the button to move on"
                                },
                                {
                                    'next .applyFilter':"Type in your text to filter the table.\n\
                                                         You can apply filter on multiple fields too!"
                                }]
                                var createTour = new EnjoyHint({});
                                 createTour.set(createTourSteps);
                                 createTour.run();
                            }, 5000);
                        }

                    });
                }

        }
    });

    $scope.$watch("report.id",function(){

       if($scope.report.id){
          $scope.report.show = true;
          $scope.loadWidgets();

       } else {
          $scope.report.show = false;
       }

    })

    $scope.newNote = function(){
         _kmq.push(['record', 'Add Note Top']);
        $scope.template = "/src/app/widget/note.html"
        $scope.report.widget = {};
        $scope.report.widget.type = 'notes';
    }

    $scope.createReport = function(){
        if($('#report_title').parsley( 'validate' )){
            $scope.show_wizard = true;
            return true;
        }
    }

    $scope.report_title_edit = false;

    $scope.toggleEdit = function(){
        if(!$scope.report_title_edit)
           $scope.report_title_edit = true;
        else
           $scope.report_title_edit = false;
    }

    $scope.date_range_compare = [{id:'previous_period',title:'Previous Period'},{id:'custom',title:'Custom'}];

    $scope.$on('report_cloned_broadcast',function(){
        $scope.$broadcast("report_cloned");
    });

    $scope.$on('report_shared_done_broadcast',function(){
        $scope.$broadcast("report_shared");
    });

    $scope.applyFilter = function(val, index) {
        var filterCtrlScope = angular.element($( "#applyFilter")).scope();
                filterCtrlScope.widget =  $scope.widgets[index];
               angular.forEach(filterCtrlScope.widget.data.args.filter, function(data, index) {

                    filterCtrlScope.filter[index] = data;

               });

                    $( "#applyFilter").dialog({
                        modal: true,
                        minWidth: 500,
                        minHeight: 200,
                        resizable:false,
                        dialogClass: "modal-dialog",
                        show: "fadeIn",
                        close: function(event, ui)
                        {
                             $timeout.cancel(filterCtrlScope.timeout);
                        }
                    });

                    $('.ui-widget-overlay').addClass('bg-black opacity-60');



    }
    //To edit insight.
     $scope.widgetEdit = [];
    $scope.widgetInsight = [];
    $scope.editInsight = function(val, insights) {
        $scope.$watch('widgetEdit', function() {
            $scope.widgetEdit[val] = true;
        });
        
        
        
        Insight.widgetInsight({id:val}, function(data) {              
            var editorInstance = $('#editInsight-'+val).ckeditor({
                on: {
                    pluginsLoaded: function() {
                        var editor = this,
                        config = editor.config;
                        var insightOptions;
                        Insight.insightOptions({channel:"googleanalytics", insights: insights}, function(data) {
                            insightOptions = data.insightOptions;
                        });
            
                        editor.ui.addRichCombo( 'Insight', {
                            label: 'Insight List',
                            title: 'Insight List',

                            panel: {               
                                css: [ CKEDITOR.skin.getPath( 'editor' ) ].concat( config.contentsCss ),
                                multiSelect: false,
                                attributes: { 'aria-label': 'Insight List' }
                            },

                            init: function() {         
                                var self = this;                                  
                                    angular.forEach(insightOptions, function(val, index) {
                                        self.startGroup(index);
                                        angular.forEach(val, function(itemName,index) {
                                              self.add(itemName, index );
                                        });
                                    });                              
                                 
                        },

                            onClick: function( value ) {
                                editor.focus();
                                editor.fire( 'saveSnapshot' );

                                editor.insertHtml( value );

                                editor.fire( 'saveSnapshot' );
                            }
                        } );         
                    }
                }
            });
            CKEDITOR.instances['editInsight-'+val].setData(data.insightRaw);
        });
      
    }
    
    $scope.saveInsights = function(widgetId, index) {
        var insightData = $("#editInsight-"+widgetId).val();
        Insight.saveInsight({widgetId : widgetId, insightData: insightData}, function(data) {
            if (data.success == true) {
                FlashMessage.setMessage(data);
                $scope.loadWidget(widgetId, index);
            }
             $scope.widgetEdit[widgetId] = false;
        });
        
    }
    
    $scope.cancelSaveInsights = function(widgetId) {        
             $scope.widgetEdit[widgetId] = false;
               
    }

    $scope.editWidget = function(val,index) {
        $scope.report.widget = $scope.widgets[index];
        $scope.report.onecol=true;
       /// console.log($scope.report);
       // console.log($scope.report.widget);
        $scope.report.index  = index;
        $scope.widget_type_single  = false;
        $scope.showCustom=false;
       
         
        Insight.list({channel:$scope.report.widget.channel}, function(data) {
               $scope.$watch("insightTypes", function() {
                         $scope.insightTypes = data.insights;
            });
          
        });
      
        if($scope.report.widget.data.args != null && [7,8].indexOf($scope.report.widget.data.args.report_type_id)!=-1) {
            console.log($scope.report.widget.period);
            $scope.showCustom=($scope.report.widget.period==14)?true:false;
        } else {           
           $scope.showCustom=($scope.report.widget.date_range==14)?true:false;
        }

        if ($scope.report.widget.channel=="googleanalytics" && $scope.report.widget.type=='table' && [7,8].indexOf($scope.report.widget.data.args.report_type_id)==-1) {
          console.log("ha1");
        } else {
           $scope.report.onecol=false;
        }

        if ($scope.report.widget.channel=="googleadwords" && $scope.report.widget.type=='table' && [7,8].indexOf($scope.report.widget.data.args.report_type_id)==-1) {
          console.log("ha1");
          $scope.report.onecol=true;
        } else {
          $scope.report.onecol=false;
        }

        if($scope.report.widget.type=='notes'){
          $scope.report.widget.notes =  $scope.report.widget.data.notes;
          $scope.template = "/src/app/widget/note.html";
          return;
        }

        // Campaigns
        angular.forEach($scope.report.widget.campaigns, function(v, k){
            $scope.report.widget.campaigns[k]    = parseInt(v);
        });

        // Metrics
        if(typeof $scope.report.widget.metrics=='string'){
            $scope.report.widget.metrics    = parseInt($scope.report.widget.metrics);
        } else {
            angular.forEach($scope.report.widget.metrics, function(v, k){
                $scope.report.widget.metrics[k]    = parseInt(v);
            });
        }

        // Date Range
        $scope.report.widget.date_range         = parseInt( $scope.report.widget.date_range);


        if($scope.report.widget.device_type==null)
            $scope.report.widget.device_type = [];
        else {
            // Device Types
            angular.forEach($scope.report.widget.device_type, function(v, k){
                $scope.report.widget.device_type[k]    = parseInt(v);
            });
        }

        // Network Types
        $scope.report.widget.network_type    = $scope.report.widget.network_type?parseInt($scope.report.widget.network_type):null;

        if($scope.report.widget.type=='kpi'){
            $scope.widget_type_kpi  = true;
            $scope.widget_type_single  = false;
        } else if($scope.report.widget.type=='table'){
            $scope.widget_type_table  = true;
            $scope.widget_type_single  = false;
        } else if($scope.report.widget.type=='graph'){
            $scope.widget_type_graph  = true;
            $scope.widget_type_single  = true;
        } else if($scope.report.widget.type=='piechart'){
              $scope.widget_type_piechart  = true;
              $scope.widget_type_single  = true;

        }


        if($scope.report.widget.channel == 'googleadwords' || $scope.report.widget.channel == 'bingads'){
            $scope.campaigns = CampaignList.query({client_account_id:$scope.report.widget.client_account_id},function(){
                $scope.campaigns.loaded = true;
            });
        } else if($scope.report.widget.channel == 'googleanalytics'){
            $scope.profiles = ProfileList.query({client_account_id:$scope.report.widget.client_account_id},function(){
                $scope.profiles.loaded = true;
            });
            $scope.segments = SegmentList.query({client_account_id:$scope.report.widget.client_account_id},function(){
                $scope.segments.loaded = true;
            });

        }

        $scope.report.channel = $scope.report.widget.channel;
        $scope.report.widget.report_type=$scope.report.widget.data.args.report_type_id;

        angular.forEach($scope.clientSources, function(value, key){
            if(value.id==$scope.report.widget.client_account_id)
                $scope.report.client_source = value;
        });

        $scope.metrics             = $scope.metricsoptions.metrics[$scope.report.widget.channel][$scope.report.widget.type];
        $scope.metrics_compare     = $scope.metricsoptions.metrics[$scope.report.widget.channel][$scope.report.widget.type];
        $scope.selectedMetrics     = [];

        if($scope.report.channel=='googleanalytics'){
              if($scope.report.widget.type=='table' || $scope.report.widget.type == 'graph' || $scope.report.widget.type == 'piechart'){
                var mapping = {1 : 0, 2 : 1, 3 : 2, 4 : 3, 5: 0, 7:5, 8:5, 9:0};

                $scope.metrics = $scope.metricsoptions.metrics[$scope.report.channel][$scope.report.widget.type][mapping[$scope.report.widget.metrics_type]];
                $scope.metrics_compare   = $scope.metricsoptions.metrics[$scope.report.widget.channel][$scope.report.widget.type][parseInt($scope.report.widget.metrics_type_compare)-1];
                $scope.metrics_types = [{'id':1,'title':'Traffic'},{'id':2,'title':'Goals'},{'id':3,'title':'Ecommerce'}];
              }

        $scope.loadGoals();

        } else if($scope.report.channel=='googleadwords'){
            if($scope.report.widget.type == 'graph'){
                 $scope.metrics           = $scope.metricsoptions.metrics[$scope.report.widget.channel][$scope.report.widget.type][parseInt($scope.report.widget.metrics_type)-1];
                 $scope.metrics_compare   = $scope.metricsoptions.metrics[$scope.report.widget.channel][$scope.report.widget.type][parseInt($scope.report.widget.metrics_type_compare)-1];
                 $scope.metrics_types = [{'id':1,'title':'Performance'},{'id':2,'title':'Conversions'},{'id':4,'title':'Competitive'}];
              }
        } else if($scope.report.channel=='bingads'){
            if($scope.report.widget.type == 'graph'){
                $scope.metrics     = $scope.metricsoptions.metrics[$scope.report.widget.channel][$scope.report.widget.type][parseInt($scope.report.widget.metrics_type)-1];
                $scope.metrics_compare   = $scope.metricsoptions.metrics[$scope.report.widget.channel][$scope.report.widget.type][parseInt($scope.report.widget.metrics_type_compare)-1];
                $scope.metrics_types = [{'id':1,'title':'Performance'},{'id':2,'title':'Conversions'}];
              }
        }

        angular.forEach($scope.report.widget.metrics, function(value, key){
            angular.forEach($scope.metrics,function(v,k){
              if(v.id == value){
                 $scope.selectedMetrics.push(v);
              }
            })
        });

        $scope.date_ranges = $scope.metricsoptions.date_ranges;
        $scope.month_ranges = $scope.metricsoptions.month_ranges;
        $scope.week_ranges = $scope.metricsoptions.week_ranges;
        $scope.date_ranges.loaded = true;
        $scope.template = "/src/app/widget/edit.html";
    }
    $scope.dateSelected = function(val) {

           $scope.$apply(function() {
                if (val == 14) {
                    $scope.showCustom = true;
                } else {
                    $scope.showCustom = false;
                }

           });

    }
    $scope.loadGoals = function(){

      if(!$scope.report.widget.profile_id.length)
        return false;

        angular.forEach($scope.profiles, function(value, key){
            if(value.id==$scope.report.widget.profile_id)
              $scope.report.widget.currency = value.currency;
        });

        $scope.goals = GoalsList.query({profile_id:$scope.report.widget.profile_id,client_account_id:$scope.report.widget.client_account_id},function(){
          $scope.goals.loaded = true;
        });
    }

    $scope.showGoals = function(element){

            var newVal = [];
            angular.forEach($scope.report.widget.metrics, function(value, key){
               if($scope.metrics[value])
                  newVal.push($scope.metrics[value].title);
            });

            if($.inArray("Goal Completions",newVal)>=0 || $.inArray("Goal Conversion Rate",newVal)>=0){                 // console.log(newVal);
              return true;
            }
        return false;
    }

    $scope.setMetrics = function(el){

        $(element).chosen()
            .change(function(e) {
                var val    = $(element).chosen().val()

                $scope.sort_metrics = [];
                angular.forEach(val, function(value, key){
                    $scope.sort_metrics.push($scope.widget.metric);
                });
            });
    }

    $scope.selectMetricTypeCompare = function(val){
        $scope.metrics_compare  = $scope.metricsoptions.metrics[$scope.report.channel][$scope.report.widget.type][val];
        return true;
    }

    $scope.check_reauthorization = function() {

        timeout = $timeout($scope.check_reauthorization, 1000);

        if(angular.isDefined($scope.reauthorization_window)){
            $scope.re_authorized      = $scope.reauthorization_window.re_authorized;
           if($scope.re_authorized){
            if(angular.isDefined($scope.reload))
                $scope.loadWidgets($scope.reauth_source_id);

              $timeout.cancel(timeout);
              $scope.reauthorization_window.close();
           }
        }
    }

    $scope.reauthorizeAccess = function(source_id,reload){
        var left  = ($(window).width()/2)-(900/2);
        var top   = ($(window).height()/2)-(600/2);
        $scope.reauthorization_window = window.open('/re-authapp/'+source_id,
                                       'Reauthorization Window', 'width=600,height=600,top=' + top + ', left=' + left);
        $scope.reauthorization_window.focus();
        $scope.re_authorized = false;
        $scope.reload = true;
        $scope.reauth_source_id = source_id;
        $timeout($scope.check_reauthorization, 1000);
    }

    $scope.save = function(){
      if($('#report_form').parsley( 'validate' )) {

         $scope.report.widget.data = null;
         delete $scope.report.widget.chartData;

         if($scope.report.widget.report_type==7)
         {
          $scope.report.widget.month_range = $scope.report.widget.period;
         }

         if($scope.report.widget.report_type==8)
         {
          $scope.report.widget.week_range = $scope.report.widget.period;
         }

         if( $scope.report.widget.type == 'notes'  && $scope.report.widget.id==null){
           $scope.report.widget.report_id  = $scope.report.id;

           return  Widget.save($scope.report.widget,function(data){
                FlashMessage.setMessage(data);
                if(data.success){
                 $scope.loadWidgets();
                 $scope.template = "/src/app/widget/list.html";
                }
           });
         }

        if(angular.isDefined($scope.goals))
          $scope.report.widget.goals_list = $scope.goals;

         return  Widget.update({widget_id:$scope.report.widget.id},$scope.report.widget,function(data){
              FlashMessage.setMessage(data);
              if(data.success){
               $scope.loadWidgets();
               $scope.template = "/src/app/widget/list.html";
              }
         });
      }
    }

    $scope.updateTitle = function(title){

         return  Report.update({report_id:$scope.report.id},{title:title,action:'update-title'});
    }

    $scope.updateOrder = function(idsInOrder){

       return  Report.update({report_id:$scope.report.id},{widget_ids:idsInOrder,action:'update-widget-orders'},function(data){
            FlashMessage.setMessage(data);
       });
    }

    $scope.deleteWidget = function(widget_id){

       return  Widget.delete({widget_id:widget_id},function(data){
            $scope.widgets = _.filter($scope.widgets,function(widget){
                    return !(widget.id==widget_id);
            })
           FlashMessage.setMessage(data);

       });
    }

    $scope.cancel = function(){
        $scope.template = "/src/app/widget/list.html";
    }

    $scope.delete = function(report_id){
          Report.delete({report_id:report_id},function(data){
              $scope.reports = _.filter($scope.reports,function(report){
                  return !(report.id==report_id);
              })

              $location.path("/reports");
              FlashMessage.setMessage(data);
              $scope.$emit("report_deleted");

          });
    }

}]);

JimmyDashboard.controller('FilterCtrl', ["$scope", "Widget", "FlashMessage", function($scope,Widget, FlashMessage){
    $scope.filter = {};
    $scope.nullSafe = function ( field ) {
        if ( !$scope.filter[field] ) {
          $scope.filter[field] = "";
        }
    };

    $scope.save = function() {
       var notEmpty = false;
        angular.forEach($scope.filter, function(value, key) {
            if (value) {
               notEmpty = true;
               return;
            }
        });

        if (notEmpty) {
            $scope.widget.filter = $scope.filter;
        } else {
            $scope.widget.filter = null;
        }

        return  Widget.update({widget_id:$scope.widget.id},$scope.widget,function(data){
             FlashMessage.setMessage(data);
             if (data.success) {
               $scope.loadWidgets();
               $scope.template = "/src/app/widget/list.html";
               $('#applyFilter').dialog('destroy');
             }
        });
    }
}]);

JimmyDashboard.controller("UpgradeCtrl",["$scope", "$rootScope", "$http", "$resource", "$location", "$timeout", "$route", "$routeParams", "GeneralInfo", "PackageList", "FlashMessage", function($scope, $rootScope,$http,$resource,$location,$timeout,$route,$routeParams,GeneralInfo,PackageList,FlashMessage){
    $scope.packages    = PackageList.query();
    $scope.step = 1;
    $scope.checkout = {};
    $scope.msg  = '';
    $scope.bg = '';
    $scope.msg_header = '';

    $resource("/src/app/countries.json").query(function(countries){
      $scope.countries = countries;
    });

    $scope.saveDisabled = false;

    $scope.bg   = ['bg-blue','bg-orange','bg-green','bg-blue','bg-orange','bg-green','bg-blue','bg-orange','bg-green','bg-blue','bg-orange','bg-green','bg-blue','bg-orange','bg-green','bg-blue','bg-orange','bg-green']

    $scope.package = $rootScope.generalInfo.unlimited_package;//PackageList.get({package_id:1});

    $scope.$on('$locationChangeSuccess', function(event) {
      var locationParams = $location.path().split('/');
      if(locationParams[1]=='upgrade-successful') {
        $scope.$emit('breadcrumbs_ready', {
          crumbs: [
            {
              title: 'Dashboard',
              url: '/',
              class: ''
            },
            {
              title: 'User',
              url: '/user',
              class: ''
            },
            {
              title: 'Upgrade Successful',
              url: '',
              class: 'active'
            }
          ]
        })
      } else {
        $scope.$emit('breadcrumbs_ready', {
          crumbs: [
            {
              title: 'Dashboard',
              url: '/',
              class: ''
            }
          ]
        })
      }
    })

    $scope.doCheckout = function(){
     $scope.checkout.email = $rootScope.generalInfo.current_user.email;
    }


    $scope.doContinue = function(){
     $scope.step = 3;
     if($("#billing-form").parsley( 'validate' ))
        $("#checkout h3").click();
    }


    $scope.doPay = function(){

      $scope.step = 4;
      $scope.checkout.package = $scope.package.id;

        if($("#cc-form").parsley( 'validate' )){
            $scope.saveDisabled = true;
            $("#do-pay").text("Processing...");
            $resource('/upgrade').save($scope.checkout,function(data){
                $("#do-pay").text("Make Payment");
                $scope.saveDisabled = false;
                FlashMessage.setMessage(data);
                if(data.success==true){

                  $location.path ("/upgrade-successful");
                  $( "#upgrade-package" ).dialog('destroy');

                  GeneralInfo.query(function(generalInfo){
                    $rootScope.generalInfo =  generalInfo;
                  });

                  $timeout(function(){
                    $location.path("#/");
                  },300000);

                }
            });
        }

    }
}]);

JimmyDashboard.directive("renderForm",['$timeout',function(timer){
    return {

        link:function(scope,elem,attrs,ctrl){
            var render = function(){
                if(widgetHtml){
                    elem.html(widgetHtml.html);
                }
            }

            timer(render,0);
        }
    }
}])

JimmyDashboard.directive("renderChart",["$rootScope", "$timeout", function($rootScope,$timeout){
    return {

        link:function($scope,elem,attrs,ctrl){

                $rootScope.$watch("templates_used_perc",function(){
                   if(!angular.isDefined($(elem).data('easyPieChart'))){

                     $(elem).easyPieChart({
                         barColor: function(percent) {

                              percent /= 100;
                              return "rgb(" + Math.round(254 * (1-percent)) + ", " + Math.round(255 * percent) + ", 0)";
                          },
                          animate: false,
                          scaleColor: '#e15258',
                          trackColor: '#f25f67',
                          scaleLength: 4,
                          lineWidth: 5,
                          size: 210,
                          lineCap: 'round',
                          onStep: function() {
                              this.$el.find('span').text(~~$rootScope.templates_used_perc);
                          }
                      });
                  } else {
                   $(elem).data('easyPieChart').update($rootScope.templates_used_perc);
                  }
                });

        }
    }

}])

JimmyDashboard.directive("renderChartReport",["$rootScope", "$timeout", function($rootScope,$timeout){
    return {

        link:function($scope,elem,attrs,ctrl){

                $rootScope.$watch("templates_used_perc",function(){
                   if(!angular.isDefined($(elem).data('easyPieChart'))){
                     $(elem).easyPieChart({
                         barColor: function(percent) {
                              percent /= 100;
                              return "rgb(" + Math.round(254 * (1-percent)) + ", " + Math.round(255 * percent) + ", 0)";
                          },
                          animate: 1000,
                          scaleColor: '#fff',
                          trackColor: '#e25158',
                          lineWidth: 10,
                          size: 100,
                          lineCap: 'cap',
                          onStep: function() {
                              this.$el.find('span').text(~~$rootScope.templates_used_perc);
                          }
                      });
                  } else {
                   $(elem).data('easyPieChart').update($rootScope.templates_used_perc);
                  }
                });

        }
    }

}])

JimmyDashboard.directive("reportWizard",["$resource", function($resource){
 return {
        link: function($scope, element, attrs) {
            var wiz = $(element).smartWizard({
              onLeaveStep:leaveAStepCallback,
              onFinish:finishCallback,
              transitionEffect: 'slide'
            });

            var cancel =  angular.element('<a href="javascript:;" class="btn medium bg-gray float-left" ng-click="cancel()"><span class="glyph-icon icon-separator"><i class="glyph-icon icon-remove"></i></span><span class="button-content">Cancel</span></a>');
            angular.element(".actionBar").prepend(cancel);

            cancel.click(function(){
              $scope.cancel();
            });

            $scope.$on('widget_selected',function(){
                wiz.smartWizard('goToStep',3);
            })

            function leaveAStepCallback(obj, context){

                return validateSteps(context.fromStep); // return false to stay on step and true to continue navigation
            }

            function finishCallback(obj, context){

               if($('#report_form').parsley( 'validate' )){
                  angular.element(".actionBar").find(".buttonFinish").addClass("disabled");
                  angular.element(".actionBar").find(".buttonFinish").val("Saving...");
                  $scope.save();
               }

            }

            function validateSteps(stepnumber){
                var isStepValid = true;

                if(stepnumber == 1){

                    if($('#client-select').parsley( 'validate' )){
                        return $("input[name='client-accounts']").parsley( 'validate' );
                    } else                         return $("input[name='client-accounts']").parsley( 'validate' );


                } else if(stepnumber == 2){

                      $("input[name='type']").parsley( 'validate' );

                     if($('#widget_graph').prop('checked')){
                        $("#report_type_error").html('');
                        return $("input[name='metric_type']").parsley( 'validate' );
                     } else if($('#widget_table').prop('checked')){
                        $("#metric_type_error").html('');
                        return  $("input[name='report_type']").parsley( 'validate' ) ||  $("input[name='metric_type']").parsley( 'validate' );
                     }  else {
                       return $("input[name='type']").parsley( 'validate' );
                     }
                }

                return true;
            }


        }
    }
}]);

JimmyDashboard.directive("deviceCheckboxList",function(){
    return {

         link:function($scope,element,attrs){

            element.on('click',function(){
                var  index = $scope.report.widget.device_type.indexOf($scope.device.id);

                if($scope.device.id == 1){
                    $("input[name='deviceTypes[]']:not(:first)").prop('checked', false);
                    $scope.report.widget.device_type = [1];
                } else {
                    $("input[name='deviceTypes[]']").first().prop('checked', false);
                    var i = $scope.report.widget.device_type.indexOf(1);

                    if(i!=-1)
                      $scope.report.widget.device_type.splice(i,1);

                    if($(element).is(":checked")){
                        $scope.report.widget.device_type.push($scope.device.id)
                    } else {
                        $scope.report.widget.device_type.splice(index,1);
                    }
                }

                $scope.$apply();

            })
         }

    }
});

JimmyDashboard.directive("dateSelect",function(){
  return {
        link: function($scope, element, attrs) {
            $(element).chosen()
            .on('change', function() {
                    if($(element).chosen().val()==13){
                       $("#"+attrs.dateSelect).removeClass('ng-hide');
                    } else {
                       $("#"+attrs.dateSelect).addClass('ng-hide');
                    }
            });
        }
    }
})

JimmyDashboard.directive("dateSelectCompare",function(){
  return {
        link: function($scope, element, attrs) {
            $(element).chosen();

            $(element).on('change', function() {

                if($(element).val()==1){
                // $("#"+attrs.dateSelectCompare).show();
                 $("#"+attrs.dateSelectCompare).removeClass('ng-hide');
                }
                else {
                 //$("#"+attrs.dateSelectCompare).hide();
                 $("#"+attrs.dateSelectCompare).addClass('ng-hide');
                }

            });

        }
    }
})

JimmyDashboard.directive("datePicker",function(){

    return {
        require: '?ngModel',
        link:function($scope,element,attrs,ngModel){
           var format  = 'yy-mm-dd';
           var minDate = attrs['minDate'];
           var maxDate = attrs['maxDate'];

           var dp= $(element).datepicker({
                  changeMonth: true,
                  changeYear: true,
                  numberOfMonths: 1,
                  dateFormat:format,
                  maxDate: maxDate,
                  minDate:minDate,
            });


             $(element).on('change',function(val){
                ngModel.$setViewValue($(element).val());
             });
        }
    }

})

JimmyDashboard.directive("timePicker",function(){

    return {
        require: '?ngModel',
        link:function($scope,element,attrs,ngModel){

           var dp= $(element).timepicker({showMeridian:false,minuteStep:30, showInputs:false});

             $(element).on('change',function(val){
                ngModel.$setViewValue($(element).val());
             });
        }
    }
})

JimmyDashboard.directive("widgetButton",function(){
  return {
        link: function($scope, element, attrs) {
            $(".buttonNext").addClass("disabled");

             element.on('click', function() {
                $("a.widget").removeClass("bg-blue-alt");
                element.next("input").attr("checked","checked");
                $scope.report.widget_type = attrs.widgetButton;
                element.addClass("bg-blue-alt");
                $(".buttonNext").removeClass("disabled");
            });
        }
    }
})

JimmyDashboard.directive("compareFields",function(){
  return {
        link: function($scope, element, attrs) {
            element.on('click', function() {

                $scope.report.compare = $(element).is(":checked");
                if($(element).is(":checked")){
                    $("."+attrs.compareFields).show();
                } else {
                    $("."+attrs.compareFields).hide();
                }
            });

        }
    }
})

JimmyDashboard.directive('jimmyChart',["$location", function($location){
 return {
        require: '?ngModel',

        link: function($scope, element, attrs,ngModel){
             var key  = attrs['key'];

            $scope.$watch("widgets[" + key + "].chartData", function(){
                 var opts =  {"dataFormatX": function (x) { return d3.time.format('%Y-%m-%d').parse(x); },
                              "tickFormatX": function (x) { return d3.time.format('%d')(x); }
                             };

                if(angular.isDefined($scope.widgets[key].chartData))  {
                    $(element).html('');
                    $scope.widgets[key].chartData.element = element[0];
                    if($scope.widgets[key].graph_type=='bar')
                      Morris.Bar($scope.widgets[key].chartData);
                    else if($scope.widgets[key].graph_type=='area')
                      Morris.Area($scope.widgets[key].chartData);
                    else
                      Morris.Line($scope.widgets[key].chartData);
                }
            });
        }
    }

}]);

JimmyDashboard.directive('jimmyChartKpi',["$location", function($location){
 return {
        require: '?ngModel',

        link: function($scope, element, attrs,ngModel){


        var key  = attrs['key'];
        var aerw = JSON.parse(key);
        var typeg= attrs['typeg'];

        var max = Math.max.apply(null,
                          Object.keys(aerw).map(function(e) {
                                  return aerw[e][0];
                          }));

        //
        var ac = JSON.parse(key);
        var totasi = [];
        //
        angular.forEach(ac, function (item)
        {
          /*if(item.length == 1){
            //item.push(20 - item);
            totasi.push({"x": "", "y": item, "z": max});
          }else{

          }*/

          totasi.push({"x": "", "y": item[0], "z": item[1]});
        });
      //
      //

      //
      var yKeys  = ['y'];
      var labels = "Click";

      if(1==1){
        yKeys[1]  = 'z';
        labels[1] = "Impre";
      }
      //

      var totals = totasi;//[{x: "2015-02-10", y: "243", z: "323"}, {x: "2015-02-10", y: "244", z: "232"}, {x: "2015-02-10", y: "278", z: "232"}, {x: "2015-02-10", y: "298", z: "232"}, {x: "2015-02-10", y: "170", z: "232"}];
      $scope.chartData={};
      $scope.chartData = {
           data: totals,
           xkey: 'x',
           ykeys: yKeys,
           labels: labels,
           barColors:['#ffcc33','#fa7753'],
           lineColors:['#fff','#fa7753'],
           gridTextColor: '#fff',
           eventLineColors: '#fff',
           yMax:4,
           parseTime: false,
           integerYLabels: true,
           hideHover: "auto",

           hoverCallbacks:function(index, options, content){
            alert("fe")
            var row = "<b>"+options.data[index].x + "</b><br><b>" + options.labels[0] + "</b>: " + options.data[index].y;

            if(options.data[index].z && options.labels[1])
            row+= "<br><b>" + options.labels[1] + "</b>: " + options.data[index].z;

            return row;
          }
      }

      //if(angular.isDefined($scope.widgets[key].chartData))  {

          var opts =  {"dataFormatX": function (x) { return "fe"; }, "tickFormatX": function (x) { return "asas"; }
          };

                    $(element).html('');
                    $scope.chartData.element = element[0];
                    if(typeg=='bar')
                      Morris.Bar($scope.chartData);
                    else
                      Morris.Line($scope.chartData);
            //}

            /*$scope.$watch("widgets[" + key + "].chartData", function(){
                 var opts =  {"dataFormatX": function (x) { return d3.time.format('%Y-%m-%d').parse(x); },
                              "tickFormatX": function (x) { return d3.time.format('%d')(x); }
                             };


                if(angular.isDefined($scope.widgets[key].chartData))  {
                    $(element).html('');
                    $scope.widgets[key].chartData.element = element[0];
                    if(angular.isDefined($scope.widgets[key].graph_type) &&  $scope.widgets[key].graph_type=='bar')
                      Morris.Bar($scope.widgets[key].chartData);
                    else
                      Morris.Line($scope.widgets[key].chartData);
                }
            });*/
        }
    }

}]);

JimmyDashboard.directive('jimmyChartAreaKpi',["$location", function($location){
 return {
        require: '?ngModel',

        link: function($scope, element, attrs,ngModel){
            var key  = attrs['key'];
      var typeg= attrs['typeg'];
      var ac = JSON.parse(key);
      var totasi = [];
      var aerw = JSON.parse(key);

      var max = Math.max.apply(null,
                        Object.keys(aerw).map(function(e) {
                                return aerw[e][0];
                        }));

      angular.forEach(ac, function (item)
      {
        /*if(item.length == 1){
          totasi.push({"x": "", "y": item[0], "z": max});
        }else{
          totasi.push({"x": "", "y": item[0], "z": item[1]});
        }*/

        totasi.push({"x": "", "y": item[0], "z": item[1]});
      });

      var yKeys  = ['y'];
      var labels = "Click";

      if(1==1){
        yKeys[1]  = 'z';
        labels[1] = "Impre";
      }

      var totals = totasi;
      $scope.chartData={};
      $scope.chartData = {
           data: totals,
           xkey: 'x',
           ykeys: yKeys,
           labels: labels,
           barColors:['#ffcc33','#fa7753'],
           lineColors:['#f19d54','#fa7753'],
           gridTextColor: '#95a2ab',
           eventLineColors: '#dcdddf',
           yMax:4,
           parseTime: false,
           integerYLabels: true,
           hideHover: "auto",

           hoverCallbacks:function(index, options, content){
            var row = "<b>"+options.data[index].x + "</b><br><b>" + options.labels[0] + "</b>: " + options.data[index].y;
            if(options.data[index].z && options.labels[1])
            row+= "<br><b>" + options.labels[1] + "</b>: " + options.data[index].z;
            return row;
          }
      }

      var opts =  {"dataFormatX": function (x) { return "fe"; }, "tickFormatX": function (x) { return "asas"; }};

      $(element).html('');
      $scope.chartData.element = element[0];
      if(typeg=='bar')
        Morris.Area($scope.chartData);
      else
        Morris.Area($scope.chartData);
        }
    }

}]);

JimmyDashboard.directive('kpiknob', ['$timeout', function($timeout) {
    'use strict';

    return {
        restrict: 'EA',
        replace: true,
        template: '<input value="{{ knobData }}"/>',
        scope: {
            knobData: '=',
            knobOptions: '&'
        },
        link: function($scope, $element) {
      alert("fe");
            var knobInit = $scope.knobOptions() || {};

            knobInit.release = function(newValue) {
                $timeout(function() {
                    $scope.knobData = newValue;
                    $scope.$apply();
                });
            };

            $scope.$watch('knobData', function(newValue, oldValue) {
                if (newValue != oldValue) {
                    $($element).val(newValue).change();
                }
            });

            $($element).val($scope.knobData).knob(knobInit);
        }
    };
}]);

JimmyDashboard.directive('kpiSparkline',["$location", function($location){

  return {
    link:function($scope, element, attrs){
           var data = attrs['data'];

           $(element).sparkline(JSON.parse(data), {
               type: 'bar',
               height: '120',
               width: '8%',
               barWidth: 13,
               barSpacing: 2,
               zeroAxis: false,
               barColor: '#ccc',
               negBarColor: '#ddd',
               zeroColor: '#ccc',
               stackedBarColor: ['#5bccf6','#ffebeb']
          });
    }
  }
}]);

JimmyDashboard.directive('kpiSparklineTop',["$location", function($location){

  return {
    link:function($scope, element, attrs){
        var data = attrs['data'];
    var ac = JSON.parse(data);
    var aerw = JSON.parse(data);

    var max = Math.max.apply(null,
                        Object.keys(aerw).map(function(e) {
                                return aerw[e][0];
                        }));

    angular.forEach(ac, function (item)
    {
      if(item.length == 1){
        item.push(max - item);
      }
        });
    //

          $(element).sparkline(ac, {
               type: 'bar',
               height: '120',
               width: '12%',
               barWidth: 10,
               barSpacing: 2,
               zeroAxis: false,
               barColor: '#ccc',
               negBarColor: '#ffcc33',
               zeroColor: '#a9a298',
               stackedBarColor: ['#ffcc33','#a9a298']
          });
    }
  }
}]);

JimmyDashboard.directive('migrateClientsDialog',["$interval", "AppAuth","UnmappedClients",
    "AccountSource","Migration","FlashMessage",function($interval,AppAuth,
    UnmappedClients, AccountSource, Migration, FlashMessage) {
        return {
            require : '?ngModel',
            scope : false,
            link : function($scope, element, attrs) {
                    var client_dialog = null;
                    var timer = null;
                    var width  = attrs['dialogWidth'];
                    var height = attrs['dialogHeight'];

                    if(!angular.isDefined(width) || width =="")
                        width = 500;

                    if(!angular.isDefined(height) || height =="")
                        height = 300;

                    $(element).on("click", function() {

                        var dialogElement = angular.element($( "#"+attrs.migrateClientsDialog));
                        var DialogCtrlScope = dialogElement.scope();
                        var authWin = null;
                        UnmappedClients.query(function(data){
                           if (data.clients) {
                                DialogCtrlScope.unmappedClientList = data.clients;
                           } else {
                                 $("#"+attrs['migrateClientsDialog']).find("#migrateSuccessMessage").show();
                                  DialogCtrlScope.clientsSuccessMessage =   "No clients need migration. Happy Reporting!";
                           }

                        });

                        dialogElement.find("button.sourceItem").click(function() {
                            DialogCtrlScope.channelName = $(this).attr("data");
                            dialogElement.find("button.sourceItem").css("background-color","#2E292D");
                            $(this).css("background-color","#878787");

                        });

                        client_dialog = $( "#"+attrs.migrateClientsDialog).dialog({
                            modal: true,
                            minWidth: width,
                            minHeight: height,
                            resizable:false,
                            dialogClass: "modal-dialog",
                            show: "fadeIn" ,
                            closeOnEscape: false,
                            open: function(event,ui)
                            {
                                dialogElement.find("button.sourceItem").css("background-color","#2E292D");
                                $( "#"+attrs.migrateClientsDialog).find("#migrateSourceAdd").attr("disabled",true);
                                $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                                $('.ui-widget-overlay').addClass('bg-black opacity-60');
                            }

                        });
                         $("#migrationDone").click(function() {
                                     client_dialog.dialog('destroy');
                                     Migration.done(function(data) {
                                          FlashMessage.setMessage(data);
                                     });

                                 });

                        dialogElement.find(".sourceItem").click(function() {
                            if($("#migrateForm").parsley('validate')) {
                                var left  = ($(window).width()/2)-(900/2);
                                var top   = ($(window).height()/2)-(600/2);
                                authWin = window.open('/authapp/'+$(this).attr("data")+"/addclient", 'window name', 'width=600,height=600,top=' + top + ', left=' + left);
                                authWin.focus();
                                 $("#"+attrs['migrateClientsDialog']).find("#migrateError").hide();
                                 $("#"+attrs['migrateClientsDialog']).find("#migrateMessage").hide();

                                timer = $interval(function(e) {

                                    if(angular.isDefined(authWin.authorized)) {
                                        if(authWin.authorized) {
                                          $scope.auth   = true;
                                          authWin.close();
                                          $interval.cancel(timer);
                                          timer = null;
                                          authWin.authorized = false;
                                          DialogCtrlScope.authorized  = authWin.authorized;
                                          DialogCtrlScope.authorized  = false;
                                          var  sourceName = $("#"+attrs['migrateClientsDialog']).find("#migrateSourceName").val();
                                          $("#"+attrs['migrateClientsDialog']).find(".loading-gif").show();
                                          AccountSource.create({sourceName : sourceName, channel:  DialogCtrlScope.channelName, migrate:true},
                                            function(data) {
                                                $("#"+attrs['migrateClientsDialog']).find(".loading-gif").hide();
                                                $("#"+attrs['migrateClientsDialog']).find("#migrateSourceName").val("");
                                                dialogElement.find("button.sourceItem").css("background-color","#2E292D");
                                                if (data.success) {
                                                   FlashMessage.setMessage(data);
                                                 UnmappedClients.query(function(data){
                                                    DialogCtrlScope.unmappedClientList = data.clients;

                                                    if (data.clients) {
                                                        $("#"+attrs['migrateClientsDialog']).find("#migrateMessage").show();
                                                        DialogCtrlScope.clientsAddMessage =  "Add another account for the remaining clients";
                                                    } else {
                                                        $("#"+attrs['migrateClientsDialog']).find("#migrateSuccessMessage").show();
                                                        DialogCtrlScope.clientsSuccessMessage =   "Hurray! All Clients Migrated. Happy Reporting!";
                                                    }
                                                 });

                                                } else {
                                                    $("#"+attrs['migrateClientsDialog']).find("#migrateError").show();
                                                    DialogCtrlScope.errorMessage = data.message;
                                                }


                                          });
                                         } else {

                                           $interval.cancel(timer);
                                           timer = null;
                                           authWin.close();
                                           DialogCtrlScope.authorized  = authWin.authorized;
                                           DialogCtrlScope.errorMessage   = authWin.error_msg;
                                           $("#"+attrs['migrateClientsDialog']).find("#migrateError").show();
                                        }
                                    }

                                }, 1000);
                            }
                        });
                    });
                }
        }
    }]);



JimmyDashboard.directive('sparkline', function() {
    return {
        restrict: 'A',
        scope: {
            sparkData: '=',
            sparkOptions: '=',
        },
        link: function (scope, element, attrs) {
            scope.$watch(scope.sparkData, function () {
                render();
            });
            scope.$watch(scope.sparkOptions, function(){
                render();
            });
            var render = function () {
                $(element).sparkline(scope.sparkData, scope.sparkOptions);
            };
        }
    }
});

JimmyDashboard.directive('useTemplate',["$q", "$rootScope", "$interval","CampaignList",
    "ProfileList", "Template", "ClientSourceList", "Report", function($q, $rootScope,
    $interval, CampaignList, ProfileList,  Template, ClientSourceList, Report){

 return {
        require: '?ngModel',
        scope:false,
        link: function($scope, element, attrs,ngModel) {

            $(element).on('click', function() {
                var templateScope = angular.element($( "#list-templates")).scope();
                 $('.loading-gif').show();
                 var reportId = attrs['reportId'];
                 var reportUser = attrs['reportUser'];
                 var widgetLocation = attrs['location'];

                 if (widgetLocation == "create-report") {
                     if (!$("#createNewReportForm").parsley('validate')) {
                         return;
                     } else {
                          templateScope.report.name =attrs["reportName"];
                     }
                 }
                 var share_dialog =  $("#list-templates").dialog({
                          modal: true,
                          minWidth: 700,
                          minHeight: 200,
                          resizable:false,
                          dialogClass: "modal-dialog",
                          show: "fadeIn" ,
                          close: function(event, ui)
                          {
                              $(this).dialog('destroy');
                          }
                      });
                      $('.ui-widget-overlay').addClass('bg-black opacity-60');
                      if (typeof $scope.closeWidget == 'function') {
                            $scope.closeWidget();
                      }

                      templateScope.adwordsChannel = [];
                      templateScope.analyticsChannel = [];
                      templateScope.bingChannel = [];
                      templateScope.template.selected = "";


                      if (reportUser) {
                        ClientSourceList.query({client_id:reportUser},function(data) {
                            clientSources = data;
                            $scope.$emit("clients_fetched");
                        });

                      } else {
                        Report.get({report_id:reportId}, function(data) {
                            reportUser = data.user_id;
                            ClientSourceList.query({client_id:reportUser},function(data) {
                                 clientSources = data;
                                 $scope.$emit("clients_fetched");

                             });
                        });
                      }

                     $scope.$on("clients_fetched", function() {


                         angular.forEach(clientSources, function(value, key) {

                          if (value.channel =="googleadwords") {

                                var campaigns = CampaignList.query({client_account_id:value.id},function() {
                                    templateScope.adwordsChannel.push({"id": value.id, "client_id" : value.client_id,
                                                   "name": value.name, "account_id" : value.account_id,
                                                   "campaign_list" : campaigns});

                                });


                          } else if (value.channel == "googleanalytics") {
                               var profileList = ProfileList.query({client_account_id: value.id}, function() {
                                    templateScope.analyticsChannel.push({"id": value.id, "client_id" : value.client_id,
                                                   "name": value.name, "account_id" : value.account_id,
                                                   "profile_list": profileList});


                                });

                          } else if (value.channel == "bingads" ) {
                               var campaigns = CampaignList.query({client_account_id:value.id}, function() {
                                    templateScope.bingChannel.push({"id": value.id, "client_id" : value.client_id,
                                            "name": value.name, "account_id" : value.account_id,
                                             "campaign_list" : campaigns});

                                });

                          }



                      });

                    $scope.$on('cfpLoadingBar:loading', function() {
                         $('.loading-gif').show();
                        });

                    $scope.$on('cfpLoadingBar:completed', function() {

                         $('.loading-gif').hide();

                    });


                     });


                    templateScope.report.id   = reportId;
                    templateScope.report.clientAccId = reportUser;
                    templateScope.report.user = $rootScope.generalInfo.current_user.id;
                    templateScope.templates = Template.list({userId : $rootScope.generalInfo.current_user.id});

            });

    }
  }
 }]);

JimmyDashboard.directive('addClientDialog',["$q", "$rootScope",  "$location",
    "$timeout", "$interval", "AppAuth", "ClientAccounts","AccountSource",
    "SourceClients", "FlashMessage", "ClientList", function($q,$rootScope, $location,$timeout,
    $interval,AppAuth, ClientAccounts, AccountSource, SourceClients, FlashMessage, ClientList){

 var DialogCtrlScope = null;
 var sourceLength = 0;
 var displayDialog = function(scope, dialogId, width, height) {

  client_dialog = $( "#"+dialogId ).dialog({
      modal: true,
      minWidth: width,
      minHeight: height,
      resizable:false,
      dialogClass: "client-source-dialog",
      show: "fadeIn" ,
      close: function(event, ui)
      {
          client_dialog.dialog('destroy');
      },
      open: function(event, ui)
      {

        DialogCtrlScope = angular.element($( "#"+dialogId )).scope();
        DialogCtrlScope.initDialog();
        $( "#"+dialogId ).find("#addSourceBtn").attr("disabled",true);
        $( "#"+dialogId ).find("#client_form").hide();
        $( "#"+dialogId ).find("#client_source_form").hide();
        $("#addClientError").hide();
        $("#addSourceError").hide();
      },
      create: function(event, ui) {
        var widget = $("#"+dialogId).dialog("widget");
        $(".ui-dialog-titlebar-close", widget)
            .addClass("closeBtn")
            .html('&nbsp;');

      }
  });

  DialogCtrlScope = angular.element($( "#"+dialogId)).scope();
  DialogCtrlScope.show_upload = false;
  DialogCtrlScope.authorized  = false;
  DialogCtrlScope.client_accounts = null;
  DialogCtrlScope.client.name = null;
  AccountSource.list(function(data) {
      if (data.sourceList) {
          DialogCtrlScope.sources = data.sourceList;
          if(data.sourceList.length==0) {
            sourceLength = 0;
          }
          DialogCtrlScope.noSource = false;
      } else {
          DialogCtrlScope.noSource = true;
      }

  });

  $( "#"+dialogId ).find('#add-source-data').hide();
  $( "#"+dialogId ).find('#sourceForm').show();
  $( "#"+dialogId ).find('#show-upload').hide();

  $( "#"+dialogId ).find("#backBtn").click(function() {
     $( "#"+dialogId ).find('#add-source-data').hide();
     $( "#"+dialogId ).find('#sourceForm').show();
     $( "#"+dialogId ).find('#show-upload').hide();
  });
  $( "#"+dialogId ).find("button#adSourceBtn").click(function() {
     $( "#"+dialogId ).find('#add-source-data').show();
     $( "#"+dialogId ).find('#sourceForm').hide();
  });

$('.ui-widget-overlay').addClass('bg-black opacity-60');

$( "#"+dialogId ).find("a#skip").click(function() {
  client_dialog.dialog('destroy');

})

$( "#"+dialogId ).find("a#done").click(function() {
  client_dialog.dialog('destroy');

})


$( "#"+dialogId ).find("button#done").click(function() {
  client_dialog.dialog('destroy');

})

$( "#"+dialogId ).find("button.sourceItem").click(function() {

      scope.channelName = $(this).attr("data");

});

$("#"+dialogId).find(".sourceItem").click(function() {
  $("#addSourceError").hide();
  if ($("#addSourceForm").parsley('validate')) {
      var left  = ($(window).width()/2)-(900/2);
      var top   = ($(window).height()/2)-(600/2);


      DialogCtrlScope.client.channel = $(this).attr("id");


      authWin = window.open('/authapp/'+$(this).attr("data")+"/addclient", 'window name', 'width=600,height=600,top=' + top + ', left=' + left);
      authWin.focus();

      timer = $interval(function(e) {

          if(angular.isDefined(authWin.authorized)) {
              if(authWin.authorized) {

                  scope.auth   = true;
                  authWin.close();
                  $interval.cancel(timer);
                  timer = null;

                  DialogCtrlScope.authorized  = authWin.authorized;
                  DialogCtrlScope.authorized  = false;
                  var sourceName = $("#"+dialogId).find("#sourceName").val();
                  if (sourceName) {
                      AccountSource.create({sourceName : sourceName, channel: scope.channelName},
                          function(data) {
                              $("#"+dialogId).find("#sourceName").val("");
                              if (data.success) {
                                AccountSource.list(function(data){
                                    if (data.sourceList) {
                                      DialogCtrlScope.sources = data.sourceList;
                                      DialogCtrlScope.noSource = false;
                                      var sourceItem =  scope.reports = _.filter(DialogCtrlScope.sources,function(source){
                                             if (source.name==sourceName) {
                                                 return source;
                                             }
                                      });
                                      DialogCtrlScope.selectSource(sourceItem[0], sourceItem[0].channel);


                                    } else {
                                           DialogCtrlScope.noSource = true;
                                    }
                                });
                                $( "#"+dialogId ).find('#add-source-data').hide();
                                $( "#"+dialogId ).find('#sourceForm').show();
                                  FlashMessage.setMessage(data);
                                  var sourceListDiv = $("#"+dialogId).find(".section-content");
                                          sourceListDiv.animate({ scrollTop: sourceListDiv[0].scrollHeight}, 1000);
                                  if($('#'+dialogId).dialog('isOpen')) {
                                    // close the dialog if add-source dialog is displayed

                                      // hide the source addition if add-client dialog
                                      // with source addition is displayed
                                      DialogCtrlScope.showClientAddition();

                                  }
                              } else {
                                  DialogCtrlScope.error_msg   = data.message;
                                  $("#addSourceError").show();
                              }
                          });
                  }
              } else {

                 $interval.cancel(timer);
                 timer = null;
                 authWin.close();
                 DialogCtrlScope.authorized  = authWin.authorized;
                 DialogCtrlScope.error       = true;
                 DialogCtrlScope.error_msg   = authWin.error_msg;
                 $("#addSourceError").show();
              }
          }
      }, 1000);
  }
});
 }

 return {
        require: '?ngModel',
        scope:false,
        link: function($scope, element, attrs,ngModel) {
            var timer = null;
            var client_dialog=null;

            var width  = attrs['dialogWidth'];
            var height = attrs['dialogHeight'];

            if(!angular.isDefined(width) || width =="")
                width = 470;

            if(!angular.isDefined(height) || height =="")
                height = 300;

            $scope.$on('client_source_added',function(data){
              if(client_dialog) client_dialog.dialog('destroy');
            });

            $scope.$on('source_addition_closed', function() {
              if(client_dialog) client_dialog.dialog('destroy');
            });

            $scope.$on('client_saved',function(data,args){
               $( "#add-client" ).find('#sourceSelect').hide();
               $( "#add-client").find('#sourceForm').hide();
               $( "#add-client").find('#show-upload').show();
            })

              var authWin = null;
              DialogCtrlScope = angular.element($( "#"+attrs['addClientDialog'])).scope();

            var checkAuth = function() {
                    if(angular.isDefined(authWin.authorized)) {
                        $scope.auth = authWin.authorized;

                        if(authWin.authorized){
                          authWin.close();
                          $interval.cancel(timer);
                          timer = null;

                          DialogCtrlScope.authorized  = authWin.authorized;
                          DialogCtrlScope.authorized  = false;

                          $( "#"+attrs['addClientDialog'] ).find('#sourceSelect').hide();
                          $( "#"+attrs['addClientDialog'] ).find('#sourceForm').show();


                        } else {
                           DialogCtrlScope.authorized  = authWin.authorized;
                           DialogCtrlScope.error       = true;
                           authWin.close();
                        }
                    }

            }

            $( element).click(function() {
              displayDialog($scope, attrs['addClientDialog'], width, height);
            });

            if($location.path()=='/') {
              ClientList.query(function(clients) {
                if(clients.length == 0) {
                    displayDialog($scope, "add-client", width, height);
                  if(sourceLength==0) {
                    DialogCtrlScope.showSourceAddition = true;
                    console.log(DialogCtrlScope.showSourceAddition);
                    var createSourceTourSteps =  [
                              {
                                'click .source-addition' : "To build your first report, select your source and add a client",
                                'showSkip': false
                              }

                          ]
                    var createSourceTour = new EnjoyHint({});
                    createSourceTour.set(createSourceTourSteps);
                    createSourceTour.run();
                  }
                  $rootScope.moveToReportCreation = true;
                  console.log($rootScope.moveToReportCreation);
                }
              })
            }


        }
    }

}]);


JimmyDashboard.directive('addCoworkerDialog',["$location", function($location){
 return {
        require: '?ngModel',

        link: function($scope, element, attrs,ngModel){

             $scope.authorized   = false;
             $scope.saveDisabled = false;

             var width = attrs['dialogWidth'];
             var height = attrs['dialogHeight'];

             if(!angular.isDefined(width) || width =="")
                width = 500;

             if(!angular.isDefined(height) || height =="")
                height = 300;


            $scope.$on('coworker_saved',function(data){
                $( "#"+ attrs['addCoworkerDialog']   ).dialog('destroy');
            })

            $( element).click(function() {

              $( "#"+attrs['addCoworkerDialog'] ).dialog({
                modal: true,
                minWidth: width,
                minHeight: height,
                resizable:false,
                dialogClass: "modal-dialog",
                show: "fadeIn"
              });

              $('.ui-widget-overlay').addClass('bg-black opacity-60');


            });

        }
    }
}]);

JimmyDashboard.directive('upgradePackage',["$location", "$resource", "PackageList", function($location,$resource,PackageList){
 return {
        require: '?ngModel',
        scope:false,

        link: function($scope, element, attrs,ngModel){

             $scope.authorized   = false;
             $scope.saveDisabled = false;

             var width  = attrs['dialogWidth'];
             var height = attrs['dialogHeight'];

             if(!angular.isDefined(width) || width =="")
                width = 500;

             if(!angular.isDefined(height) || height =="")
                height = 300;


            $scope.$on('package_updated',function(data){
              console.log(111);

                $( "#"+ attrs['upgradePackage']   ).dialog('destroy');
            })

            $( element).click(function() {
              console.log($scope);
              $( "#"+attrs['upgradePackage'] ).dialog({
                modal: true,
                minWidth: width,
                minHeight: height,
                resizable:false,
                dialogClass: "modal-dialog",
                show: "fadeIn"
              });

              $('.ui-widget-overlay').addClass('bg-black opacity-60');

            });



        }
    }
}]);

JimmyDashboard.directive('sidebarMenu',["$q", "$location", "$timeout","$routeParams", "$rootScope", "$http",
    function($q,$location,$timeout,$routeParams, $rootScope, $http){
    return {
        restrict: 'EA',
        scope:false,
        templateUrl:'/src/app/dashboard/sidebar-menu.html',
        link: function($scope, element, attrs, ngModel) {


            $rootScope.$watch("generalInfo.current_user",function() {

                if(angular.isDefined($rootScope.generalInfo.current_user)){
                    // intercom implementation !!!!

                    function showIntercom() {                        
                        window.intercomSettings = {
                                                 app_id: "a84nafcw",
                                                 name : $rootScope.generalInfo.current_user.name, // Full name
                                                 email: $rootScope.generalInfo.current_user.email, // Email address
                                                 created_at: $rootScope.generalInfo.current_user.created_timestamp,// Signup date as a Unix timestamp
                                                 "package":$rootScope.generalInfo.package.title, //User Package
                                                 "user type" : $rootScope.generalInfo.current_user.type, //User Type (Agency/Coworker)
                                                 "number of reports": $rootScope.generalInfo.templates_used


                        }
                        var w=window;var ic=w.Intercom;if(typeof ic==="function")
                                {ic('reattach_activator');ic('update',intercomSettings);}
                                else{var d=document;var i=function(){i.c(arguments)};
                                i.q=[];i.c=function(args){i.q.push(args)};w.Intercom=i;
                                function l(){var s=d.createElement('script');
                                s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/';

                                var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);
                                }if(w.attachEvent){w.attachEvent('onload',l);}
                                else{w.addEventListener('load',l,false);}};
                    }
                   
                    //intercom ends;

                    // kissmetrics setting user
                     function setKissmetricsUser () {
                         _kmq.push(['identify',  $rootScope.generalInfo.current_user.email]);
                     }
                    //kiss metrics ends;

                    if($rootScope.generalInfo.current_user.type == 'agency'){
                        $scope.menu = [
                                       {title:'Dashboard',route:'#/',icon:'icon-dashboard'},
                                       {title:'Clients',route:'#/clients',icon:'icon-users'},
                                       {title:'Reports',icon:'icon-bar-chart-o',children:[{title:'All',route:'#/reports',icon:'icon-chevron-right'},
                                       {title:'Shared With Me',route:'#/reports/shared',icon:'icon-chevron-right'}]},
                                       {title:'Co-worker',route:'#/coworker',icon:'icon-smile-o'}
                                      ];
                       showIntercom();// calling intercom
                       setKissmetricsUser();

                    } else if($rootScope.generalInfo.current_user.type == 'coworker'){
                        $scope.menu = [{title:'Dashboard',route:'#/',icon:'icon-dashboard'},
                                       {title:'Clients',route:'#/clients',icon:'icon-users'},
                                       {title:'Reports',icon:'icon-bar-chart-o',color:'font-white',children:[{title:'All',route:'#/reports',icon:'icon-chevron-right'},
                                       {title:'Shared With Me',route:'#/reports/shared',icon:'icon-chevron-right'}]}];
                       showIntercom();  //calling intercom
                       setKissmetricsUser();

                    } else if($rootScope.generalInfo.current_user.type == 'user'){
                        $scope.menu = [{title:'Dashboard',route:'#/',icon:'icon-dashboard'}];

                    }
                   $rootScope.$watch("sideBarOptions",function() {
                        if($rootScope.generalInfo.current_user.type == 'coworker'
                                || $rootScope.generalInfo.current_user.type == 'agency'){
                             if($rootScope.sideBarOptions) {
                                        $scope.sideBarOptions = true;
                             } else {
                                  $scope.sideBarOptions = false;
                             }

                        } else {
                             $scope.sideBarOptions = false;
                        }
                   });
                }

                $timeout(function () {

                  $('#sidebar-menu li').on('click', function(){

                //      console.log($(this).is('.active'));
                            if($(this).is('.active')) {
                              $(this).removeClass('active');
                              $('ul', this).slideUp();

                            } else {
                              $('#sidebar-menu li ul').slideUp();
                              $('ul', this).slideDown();
                              $('#sidebar-menu li').removeClass('active');
                              $(this).addClass('active');

                            }

                    });



                    $(".main-menu .main-item").click(function() {

                         var width = $('#page-sidebar').width();
                            if(width >200) {
                                $('#page-sidebar').animate({"width":"200px"},"slow");
                            }
                    });


                 });


            });

        }

    }

}]);

JimmyDashboard.directive('reportMenu',["$q", "$timeout", "$location", "$http", "Report", "FlashMessage", function($q,$timeout,$location,$http,Report,FlashMessage){
 var downloads = 0;
 var q;
 return {
        restrict: 'E',
        replace:true,
        templateUrl:'/src/app/report/menu.html',
        link: function($scope, element, attrs,ngModel){


          $timeout(function() {

              $(element).find('a.clone').click(function(){
                var   CloneCtrlScope = angular.element($( "#clone-report")).scope();

                  $scope.$apply(function(){
                    CloneCtrlScope.report       = $scope.report;
                  })


                 var clone_dialog =  $( "#clone-report").dialog({
                      modal: true,
                      minWidth: 400,
                      minHeight: 200,
                      resizable:false,
                      dialogClass: "modal-dialog",
                      show: "fadeIn" ,
                      close: function(event, ui)
                      {
                          $(this).dialog('destroy');
                      }
                   });

                  CloneCtrlScope.$on('report_cloned',function(){
                    clone_dialog.dialog('destroy');
                  })


                  $("#clone-report").find("#report_title").val('');

                  $ ('.ui-widget-overlay').addClass('bg-black opacity-60');

                  $( "#clone-report").find( "#done-cancel").click(function(){
                      clone_dialog.dialog('destroy');
                  })

              });

              $(element).find('a.share').click(function(){
                 var   ShareCtrlScope = angular.element($( "#share-report")).scope();

                  ShareCtrlScope.report       = $scope.report;
                  ShareCtrlScope.sharing_list = Report.getShared({report_id:$scope.report.id});

                 var share_dialog =  $( "#share-report").dialog({
                      modal: true,
                      minWidth: 400,
                      minHeight: 200,
                      resizable:false,
                      dialogClass: "modal-dialog",
                      show: "fadeIn" ,
                      close: function(event, ui)
                      {
                          $(this).dialog('destroy');
                      }
                   });
                  $("#share-report").find("#email").val('');

                  $ ('.ui-widget-overlay').addClass('bg-black opacity-60');

                  $( "#share-report").find("#done-share").click(function(){
                      share_dialog.dialog('destroy');
                  })
              });

              $(element).find('a.schedule').click(function(){
                 var   ScheduleCtrlScope = angular.element($( "#schedule-report")).scope();

                  ScheduleCtrlScope.report      = $scope.report;
                  ScheduleCtrlScope.scheduled_list = Report.getScheduled({report_id:$scope.report.id});

                 var schedule_dialog =  $( "#schedule-report").dialog({
                    modal: true,
                      minWidth: 700,
                      minHeight: 200,
                      resizable:false,
                      dialogClass: "modal-dialog",
                      show: "fadeIn" ,
                      zIndex : -1,
                       open: function(event, ui)
                      {


                        CKEDITOR.replace('editor2');
                      },
                      close: function(event, ui)
                      {
                        CKEDITOR.instances['editor2'].destroy('true');
                        $(this).dialog('destroy');

                      }
                   });

                  $("#schedule-report").find("#email").val('');

                  $ ('.ui-widget-overlay').addClass('bg-black opacity-60');

                  $( "#schedule-report").find("#done-share").click(function(){
                      schedule_dialog.dialog('destroy');
                  })

              });

               q  = $q.defer();


              $(element).find('a.download').click(function(){
                  $scope.$parent.report = $scope.report;
                  var iframe = $("#download-report");
                  var ProgressBarScope = angular.element("#progress-bar").scope();

                    downloads = downloads+1;

                    if(downloads>1){
                       ProgressBarScope.stop({message:'Download Cancelled'});
                       q.resolve(); // resolve previous promise
                       q = $q.defer(); // create a new one
                       ProgressBarScope.start({message:'Downloading Report ( ' + $scope.$parent.report.title+ " )"});
                     } else
                       ProgressBarScope.start({message:'Downloading Report ( ' +  $scope.$parent.report.title+ " )"});



                    $http({
                        url: '/reports/download/' + $scope.$parent.report.id,
                        method: "GET",
                        timeout:q.promise
                    }).
                    success(function(data, status, headers, config) {
                        FlashMessage.setMessage(data);

                        if(data.success){
                          iframe.attr('src',data.file);
                          downloads = 0;
                          ProgressBarScope.complete({message:'Download Complete'});
                        } else {
                          ProgressBarScope.stop({message:'Download Cancelled'});
                        }
                    }).
                    error(function(data, status, headers, config) {
                      downloads = 0;
                      FlashMessage.setMessage({message:'Download Cancelled',success:false});
                    });


              });

              $(element).find('a.delete').click(function(){
                  $scope.delete($scope.report.id);
              });

          },0);
        }
    }

}]);





JimmyDashboard.directive('reportDashboardMenu',["$q", "$timeout", "$location", "$http", "Report", "FlashMessage", function($q,$timeout,$location,$http,Report,FlashMessage){
 var downloads = 0;
 var q;
 return {
        restrict: 'E',
        replace:true,
        templateUrl:'/src/app/report/menu-dashboard.html',
        link: function($scope, element, attrs,ngModel){


          $timeout(function() {

              $(element).find('a.clone').click(function(){
                var   CloneCtrlScope = angular.element($( "#clone-report")).scope();

                  $scope.$apply(function(){
                    CloneCtrlScope.report       = $scope.report;
                  })


                 var clone_dialog =  $( "#clone-report").dialog({
                      modal: true,
                      minWidth: 400,
                      minHeight: 200,
                      resizable:false,
                      dialogClass: "modal-dialog",
                      show: "fadeIn" ,
                      close: function(event, ui)
                      {
                          $(this).dialog('destroy');
                      }
                   });

                  CloneCtrlScope.$on('report_cloned',function(){
                    clone_dialog.dialog('destroy');
                  })


                  $("#clone-report").find("#report_title").val('');

                  $ ('.ui-widget-overlay').addClass('bg-black opacity-60');

                  $( "#clone-report").find( "#done-cancel").click(function(){
                      clone_dialog.dialog('destroy');
                  })

              });

              $(element).find('a.share').click(function(){
                 var   ShareCtrlScope = angular.element($( "#share-report")).scope();
                  ShareCtrlScope.report       = $scope.report;
                  ShareCtrlScope.sharing_list = Report.getShared({report_id:$scope.report.id});

                 var share_dialog =  $( "#share-report").dialog({
                      modal: true,
                      minWidth: 400,
                      minHeight: 200,
                      resizable:false,
                      dialogClass: "modal-dialog",
                      show: "fadeIn" ,
                      close: function(event, ui)
                      {
                          $(this).dialog('destroy');
                      }
                   });
                  $("#share-report").find("#email").val('');

                  $ ('.ui-widget-overlay').addClass('bg-black opacity-60');

                  $( "#share-report").find("#done-share").click(function(){
                      share_dialog.dialog('destroy');
                  })
              });

              $(element).find('a.schedule').click(function(){
                 var   ScheduleCtrlScope = angular.element($( "#schedule-report")).scope();

                  ScheduleCtrlScope.report      = $scope.report;
                  ScheduleCtrlScope.scheduled_list = Report.getScheduled({report_id:$scope.report.id});

                 var schedule_dialog =  $( "#schedule-report").dialog({
                      modal: true,
                      minWidth: 700,
                      minHeight: 200,
                      resizable:false,
                      dialogClass: "modal-dialog",
                      show: "fadeIn" ,
                      open: function(event, ui)
                      {

                       CKEDITOR.replace( 'editor2');
                      },
                      close: function(event, ui)
                      {
                          CKEDITOR.instances['editor2'].destroy('true');
                          $(this).dialog('destroy');


                      }
                   });

                  $("#schedule-report").find("#email").val('');

                  $ ('.ui-widget-overlay').addClass('bg-black opacity-60');

                  $( "#schedule-report").find("#done-share").click(function(){
                      CKEDITOR.instances['editor2'].destroy('true');
                      schedule_dialog.dialog('destroy');
                  })

              });

               q  = $q.defer();


              $(element).find('a.download').click(function(){
                  $scope.$parent.report = $scope.report;
                  var iframe = $("#download-report");
                  var ProgressBarScope = angular.element("#progress-bar").scope();

                    downloads = downloads+1;

                    if(downloads>1){
                       ProgressBarScope.stop({message:'Download Cancelled'});
                       q.resolve(); // resolve previous promise
                       q = $q.defer(); // create a new one
                       ProgressBarScope.start({message:'Downloading Report ( ' + $scope.$parent.report.title+ " )"});
                     } else
                       ProgressBarScope.start({message:'Downloading Report ( ' +  $scope.$parent.report.title+ " )"});



                    $http({
                        url: '/reports/download/' + $scope.$parent.report.id,
                        method: "GET",
                        timeout:q.promise
                    }).
                    success(function(data, status, headers, config) {
                        FlashMessage.setMessage(data);

                        if(data.success){
                          iframe.attr('src',data.file);
                          downloads = 0;
                          ProgressBarScope.complete({message:'Download Complete'});
                        } else {
                          ProgressBarScope.stop({message:'Download Cancelled'});
                        }
                    }).
                    error(function(data, status, headers, config) {
                      downloads = 0;
                      FlashMessage.setMessage({message:'Download Cancelled',success:false});
                    });


              });

              $(element).find('a.delete').click(function(){
                  $scope.delete($scope.report.id,$scope.report.title);
              });

          },0);
        }
    }

}]);

JimmyDashboard.directive('reportButton',["$q", "$rootScope", "$timeout", "$location","Report","FlashMessage", "$http", "ReportUpgradeService",
    function($q,$rootScope,$timeout,$location, Report, FlashMessage, $http, ReportUpgradeService){


 return {
        restrict: 'EA',
        replace:true,
        templateUrl:'/src/app/report/button.html',
        link: function($scope, element, attrs,ngModel){

            $scope.isUpgradeRequired = function() {
              if($rootScope.generalInfo.package.id==13)
                return false;
              if($rootScope.generalInfo.package.id==14)
                return false;
              if($rootScope.generalInfo.package.id==5)
                return true;
              return false
            }

            $scope.doReportAction = function(report, action, create) {
              $scope.pendingReport = report;
              $scope.billingReportsCount = $rootScope.generalInfo.templates_used;
              if($scope.isUpgradeRequired()) {
                if(create) {
                  $rootScope.$broadcast('show_create_report_dialog');
                } else {
                  $rootScope.$broadcast('show_upgrade_alert_dialog');
                }
                $scope.pendingAction = action;
              } else {
                action();
              }
            }

            $timeout( function() {
                $(element).find('#share-report-btn').click(function(){
                 var   ShareCtrlScope = angular.element($( "#share-report")).scope();

                  ShareCtrlScope.report       = $scope.report;
                  ShareCtrlScope.sharing_list = Report.getShared({report_id:$scope.report.id});
                  $scope.doReportAction($scope.report, function() {
                    var share_dialog =  $( "#share-report").dialog({
                      modal: true,
                      minWidth: 400,
                      minHeight: 200,
                      resizable:false,
                      dialogClass: "modal-dialog",
                      show: "fadeIn" ,
                      close: function(event, ui)
                      {
                          $(this).dialog('destroy');
                      }
                    });
                    $("#share-report").find("#email").val('');
  		              $ ('.ui-widget-overlay').addClass('bg-black opacity-60');

                    $( "#share-report").find("#done-share").click(function(){
                        share_dialog.dialog('destroy');
                    });

                  });

              });

              q  = $q.defer();
              downloads =0;

              $(element).find('#report-download-btn').click(function(){
                  $scope.$parent.report = $scope.report;
                  var iframe = $("#download-report");
                  var ProgressBarScope = angular.element("#progress-bar").scope();

                    downloads = downloads+1;

                    if(downloads>1){
                       ProgressBarScope.stop({message:'Download Cancelled'});
                       q.resolve(); // resolve previous promise
                       q = $q.defer(); // create a new one
                       ProgressBarScope.start({message:'Downloading Report ( ' + $scope.$parent.report.title+ " )"});
                     } else
                       ProgressBarScope.start({message:'Downloading Report ( ' +  $scope.$parent.report.title+ " )"});



                    $http({
                        url: '/reports/download/' + $scope.$parent.report.id,
                        method: "GET",
                        timeout:q.promise
                    }).
                    success(function(data, status, headers, config) {
                        FlashMessage.setMessage(data);

                        if(data.success){
                          iframe.attr('src',data.file);
                          downloads = 0;
                          ProgressBarScope.complete({message:'Download Complete'});
                        } else {
                          ProgressBarScope.stop({message:'Download Cancelled'});
                        }
                    }).
                    error(function(data, status, headers, config) {
                      downloads = 0;
                      FlashMessage.setMessage({message:'Download Cancelled',success:false});
                    });
            });
        });

        }}}]);



JimmyDashboard.directive('widgetMenu',["$location", "$timeout", "$http", "Report", "MessageList", function($location,$timeout,$http,Report,MessageList){
 return {
        restrict: 'E',
        replace:true,
        templateUrl:'/src/app/widget/menu.html',
        link: function($scope, element, attrs,ngModel){

          $timeout(function() {

              $(element).find('a.chat').click(function(a,b){
                  var   ChatCtrlScope     = angular.element($( "#conversation")).scope();
                  var   widget_id         = $(a.currentTarget).attr("widget_id");

                  ChatCtrlScope.widget_id = widget_id;

                  ChatCtrlScope.updateMessage();

                    $( "#conversation").dialog({
                        modal: true,
                        minWidth: 600,
                        minHeight: 300,
                        resizable:false,
                        dialogClass: "modal-dialog",
                        show: "fadeIn",
                        close: function(event, ui)
                        {
                             $timeout.cancel(ChatCtrlScope.timeout);
                        }
                    });

                    $('.ui-widget-overlay').addClass('bg-black opacity-60');

                });

          });


          $scope.$watch('metricsoptions.date_ranges', function(oldval,newval) {
           // console.log(oldval)
            $timeout(function(){
              //console.log($(element));
             // console.log($(element).find("a.date_range_selector"));

             $(element).find("a.date_range_selector").click(function(a,b){
                    var date_range_id = $(a.target).attr("date_range_id");
                    var widget_id     = $(a.target).attr("widget_id");
                    var key           = $(a.target).attr("key");

                    if(date_range_id==14){

                      var date_selector_dialog =  $( "#custom_date_selector_dialog").dialog({
                            modal: true,
                            minWidth: 400,
                            minHeight: 100,
                            resizable:false,
                            dialogClass: "modal-dialog",
                            show: "fadeIn"
                        });


                        $('.ui-widget-overlay').addClass('bg-black opacity-60');

                        $("#custom_date_selector_dialog").find("a#apply-custom").click(function(){

                          if($("#date_picker").parsley('validate')){
                            $scope.loadWidget(widget_id,key,{date_range:date_range_id,min:$scope.date_min,max:$scope.date_max});
                            date_selector_dialog.dialog('destroy');
                          }

                        })

                    } else {
                        $scope.loadWidget(widget_id,key,{date_range:date_range_id});
                    }
                })
            },10);
          }) ;
        /*
                    $timeout(function(){
                      $(element).find("a").tooltip({ container: 'body'});
                    })
        */
        }
    }

}]);

JimmyDashboard.directive('cloneReportDialog',["$location", function($location){
 return {
        restrict: 'E',
        replace:true,
        templateUrl:'/src/app/report/clone.html',
        link: function($scope, element, attrs,ngModel){


            $("."+attrs['bindTo']).click(function(data) {

                $scope.$parent.report = $scope.report;

                $( "#clone-report").dialog({
                    modal: true,
                    minWidth: 400,
                    minHeight: 200,
                    resizable:false,
                    dialogClass: "modal-dialog",
                    show: "fadeIn"
                });

                $('.ui-widget-overlay').addClass('bg-black opacity-60');


            });


        }
    }

}]);



JimmyDashboard.directive('fileUpload', ['$location', function($location) {
    return {
        link: function($scope, element, attrs) {
            var callback = attrs['finishCallback'];
            var myDropzone = new Dropzone(element[0], {
              url: attrs['action'],
              maxFiles: 1,
              addRemoveLinks: true
            });

                 myDropzone.on('complete',function(data){
                    $scope[callback](data);
                 })

        }
    }
}]);

JimmyDashboard.directive('clickLink', ['$location', function($location) {
    return {
        link: function(scope, element, attrs) {
            element.on('click', function() {
                scope.$apply(function() {
                    $location.path(attrs.clickLink);
                });
            });
        }
    }
}]);

JimmyDashboard.filter('sprintf', function() {
    function parse(str) {
        var args = [].slice.call(arguments, 1);
            i = 0;

        return str.replace(/%s/g, function() {
            return args[i++];
        });
    }

    return function(str) {

        return parse(str, arguments[1][0], arguments[1][1]);
    };
});


JimmyDashboard.directive("compareCalc",function(){

return {
    link: function(scope, element, attrs) {
        var v  = attrs['value'].replace(/[\,\%]/,"");
        var v1 = attrs['compareValue'].replace(/[\,\%]/,"");

    //console.log("fe1");
    //console.log(v);
    //console.log("fe2");

         var val = Math.round(((v-v1)/v1)*100,2);// + '%';

         if(isNaN(val)) {
           val ='n/a <i class="glyph-icon icon-long-arrow-up font-gray-dark" style="visibility:hidden"></i>';
         } else if(val=='Infinity'){
           val ='n/a <i class="glyph-icon icon-long-arrow-up font-gray-dark" style="visibility:hidden"></i>';
         } else if(val>=0){
           val+='% <i class="glyph-icon icon-long-arrow-up font-gray-dark"></i>';
         } else {
           val =  val.toString().replace(/-/,"");

           val+='% <i class="glyph-icon icon-long-arrow-down font-gray-dark"></i>';
         }


         element.addClass('font-gray-dark');

         element.html(val);
         // $(element).accordion();
        }
    }
});


JimmyDashboard.directive("compareCalcCaption",function(){

return {
    link: function(scope, element, attrs) {
        var v  = attrs['value'].replace(/[\,\%]/,"");
        var v1 = attrs['compareValue'].replace(/[\,\%]/,"");

         var val = Math.round(((v-v1)/v1)*100,2);// + '%';

         if(isNaN(val)) {
           text ='n/a';
         } else if(val=='Infinity'){
           text ='n/a';
         } else if(val>=0){
           text ='Increase in '+attrs["compareCalcCaption"]  + " by " + val + "%";
         } else {
           text ='Decrease in '+attrs["compareCalcCaption"]  + " by " + Math.abs(val) + "%";
         }


         element.addClass('font-gray-dark');

         element.html(text);
         // $(element).accordion();
        }
    }
});

JimmyDashboard.directive("accordion",function(){
 return {
    link: function(scope, element, attrs) {

          $(element).accordion();
        }
    }
});

JimmyDashboard.directive("reauthLink",["$rootScope", function($rootScope){
 return {
    link: function($scope, element, attrs) {
          var client_account_id = attrs["reauthLink"]
          var message = attrs['message'];

          if($scope.generalInfo.current_user.type=='agency' ||$scope.generalInfo.current_user.type=='coworker'){
           if (!message == 'Migration not done') {
                $(element).append(' Please click the re-authorization icon on the right <a href="javascript:;" class="btn medium reauth"><i class="glyph-icon icon-refresh font-red"></i></a> ');

                $(element).find('.reauth').click(function(){;
                       $scope.reauthorizeAccess(client_account_id,true);
                });
            } else {
                 $(element).append('. Please migrate your accounts \n\
                                    <a href="javascript:;" class="btn medium migrate"><i class="glyph-icon icon-refresh font-red"></i></a>');
                  $(element).find('.migrate').click(function(){
                     $("#migrateBtn").trigger("click");
                });


            }
           }
        }
    }
}]);



JimmyDashboard.directive("customSort", function() {
return {
    restrict: 'A',
    transclude: true,
    scope: {
      order: '=',
      sort: '='
    },
    template :
      ' <a ng-click="sort_by(order)" style="color: #555555;">'+
      '    <span ng-transclude></span>'+
      '    <i ng-class="selectedCls(order)"></i>'+
      '</a>',
    link: function(scope) {
     //scope.sort = {sortingOrder : 'campaign',reverse : true };
    // change sorting order
    scope.sort_by = function(newSortingOrder) {
        var sort = scope.sort;

        if (sort.sortingOrder == newSortingOrder){
            sort.reverse = !sort.reverse;
        }

        sort.sortingOrder = newSortingOrder;
        scope.$parent.search();
    };


    scope.selectedCls = function(column) {
        if(!angular.isDefined(scope.sort)) return;

        if(column == scope.sort.sortingOrder){
            return ('glyph-icon icon-chevron-' + ((scope.sort.reverse) ? 'down' : 'up'));
        } else{
            return 'glyph-icon  icon-sort'
        }



    };
  }// end link
}
});

/*<li ng-if="directionLinks" ng-class="{ disabled : currentPage == 0 }" class="no-hover">
        <a href="javascript:;" ng-click="setCurrent(currentPage - 1)">&lsaquo;</a>
    </li>*/


JimmyDashboard.directive("paginate", ["$filter", function($filter) {
return {
    restrict: 'EA',
    transclude: true,
    scope:false,
    template :
      '    <ul class="pagination" ng-show="pagedItems.length>1">'+
      '        '+
      '            <!--span class="label" >Showing Page {{currentPage+1}} of {{totalPages}}</span-->'+
    '            <li ng-class="{disabled: currentPage == 0}" class="no-hover">'+
      '            <a href="javascript:;" class="no-hover" ng-click="firstPage()" >'+
      '            &laquo; First</a>'+
      '            </li>'+

    '            <li ng-class="{ disabled : currentPage == 0 }" class="no-hover">'+
      '            <a href="javascript:;" ng-click="prevPage()">&lsaquo;</a>'+
      '            </a>'+
      '            </li>'+

      '            <li ng-repeat="n in range(pagedItems.length, currentPage, currentPage+gap ) " ng-class="{ active : currentPage == n, disabled : n == null }">'+
      '            <a href="javascript:;" class="btn small ui-state-default" '+
      '                ng-click="setPage()" ng-bind="n + 1">'+
      '            </a>'+
      '            </li>'+

    '            <li ng-class="{ disabled : currentPage == last }" class="no-hover">'+
      '            <a href="javascript:;" ng-click="nextPage()">&rsaquo;</a>'+
      '            </a>'+
      '            </li>'+

      '            <li   ng-class="{disabled: (currentPage) == pagedItems.length - 1}" class="no-hover">'+
      '            <a href="javascript:;" ng-click="lastPage()">'+
      '                Last &raquo;'+
      '            </a>'+
      '            </li>'+
      '        '+
      '    </ul>',
    link: function($scope,element,attrs) {
        var source = attrs['source'];

        $scope.filteredItems = [];
        $scope.groupedItems  = [];

        $scope.itemsPerPage  = 10;

        if(attrs['perPage'])
          $scope.itemsPerPage  = parseInt(attrs['perPage']);

        $scope.pagedItems    = [];
        $scope.currentPage   = 0;
        $scope.gap           = 5;


        $scope.sort = {sortingOrder : 'id',reverse : true };

        if(attrs["sortingOrder"])
          $scope.sort.sortingOrder  = null;//attrs["sortingOrder"];

        if(attrs["reverse"])
          $scope.sort.reverse  = attrs["reverse"];


        $scope.$watchCollection(source, function(oldval,newval) {

           if(angular.isDefined(oldval) && oldval && oldval.length){
            $scope.source = oldval;
            $scope.search();
           }

        });


        var searchMatch = function (haystack, needle) {
            if (!needle) {
                return true;
            }

            if(haystack!=null)
              return haystack.toString().toLowerCase().indexOf(needle.toLowerCase()) !== -1;

        };

        // init the filtered items
        $scope.search = function () {

            $scope.filteredItems = $filter('filter')($scope.source, function (item) {
                for(var attr in item) {
                    if (searchMatch(item[attr], $scope.query))
                        return true;
                }
                return false;
            });

            // take care of the sorting order
            if ($scope.sort.sortingOrder !== '') {
                  $scope.filteredItems = $filter('orderBy')($scope.filteredItems, $scope.sort.sortingOrder, $scope.sort.reverse);

            }

            $scope.currentPage = 0;
            // now group by pages
            groupToPages();
        };

        // calculate page in place
        var groupToPages = function () {
            $scope.pagedItems = [];
            for (var i = 0; i < $scope.filteredItems.length; i++) {
                if (i % $scope.itemsPerPage === 0) {
                    $scope.pagedItems[Math.floor(i / $scope.itemsPerPage)] = [ $scope.filteredItems[i] ];
                } else {
                    $scope.pagedItems[Math.floor(i / $scope.itemsPerPage)].push($scope.filteredItems[i]);
                }
            }
        };

        $scope.range = function (size,start, end) {
            var ret = [];



            if (size < end) {
                end   = size;

                if(size && $scope.gap >  size){
                   $scope.gap =  size - 2;


                }

                start = size - $scope.gap;
            }

            $scope.totalPages = size;

            for (var i = start; i < end; i++) {
                ret.push(i);
            }

            return ret;
        };

        $scope.prevPage = function () {
            if ($scope.currentPage > 0) {
                $scope.currentPage--;
            }
        };

        $scope.nextPage = function () {
            if ($scope.currentPage < $scope.pagedItems.length - 1) {
                $scope.currentPage++;
            }
        };

        $scope.setPage = function () {
            $scope.currentPage = this.n;
        };

    $scope.firstPage = function () {
            $scope.currentPage = 0;
        };

    $scope.lastPage = function () {
            $scope.currentPage = $scope.pagedItems.length-1;
        };


    }// end link
}
}]);


JimmyDashboard.directive("flashMessage", ["FlashMessage", "$timeout",
                                            function(FlashMessage,$timeout) {
    return {
        restrict: 'E',
        link: function($scope) {

            $scope.$watch('flashMessage.message', function() {
                if($scope.flashMessage.message.length > 0){
                      var msg = '<i class="glyph-icon icon-bullhorn font-size-12"></i> ' +
                                '<b>'+FlashMessage.getHeaderMessage()+ '</b> <br/>'
                                 +FlashMessage.getMessage();
                      $.jGrowl( msg, {
                         sticky: false,
                         position: 'bottom-left', //set the position of the
                         theme: FlashMessage.getBg()
                       });
                }

            });
      }
    }
}]);

JimmyDashboard.directive("loadingSpinner",["FlashMessage", "$timeout", function(FlashMessage,$timeout){
return {
        restrict: 'EA',
        scope:false,
        template:'<i class="glyph-icon icon-spinner icon-spin icon-large font-gray ng-hide" ng-if="start"></i> <small class="font-gray">{{loadingText}}</small>',
        link: function($scope,element,attrs) {
          $scope.lading_start = true;
          $scope.loadingText  = attrs["text"];
          //$(document).loadingbar();

        }
      }
}])



JimmyDashboard.directive("progressBar", ["FlashMessage", "$timeout", "$rootScope", function(FlashMessage,$timeout, $rootScope) {
    return {
        restrict: 'EA',
        templateUrl:'/src/app/dashboard/progressbar.html',
        link: function($scope,element) {
            var prgBar = $(element).find('.progressbar');
            var timeout,timeout1,timeout2,timeout3;

            $scope.updateProgress = function(){
             timeout1 =  $timeout($scope.updateProgress,200);
                var perc    = Math.ceil(Math.random()*100);

                if(perc > $scope.perc && perc < 90 )
                   $scope.perc = Math.ceil(perc);
                else if($scope.loadComplete) {
                   $scope.perc = 100;
                }
                else
                   return;


                var progressBarWidth = perc * element.width() / 100;
                prgBar.find('.progressbar-value').animate({ width: progressBarWidth }, 1000);

            }

            $scope.start = function(data){
              $scope.$apply(function(){
                $(element).show();
                $scope.message = '';
                $scope.perc    = '';

                prgBar.prop('data-value',0)
                $scope.message = data.message;
                $scope.perc = 0;

                timeout =  $timeout(function(){
                    $scope.updateProgress();
                    $timeout.cancel(timeout);
                },200);

              })

            }

            $scope.complete = function(data){
              $scope.perc    = 100;
              $scope.message = data.message;
              $timeout.cancel(timeout1);
              prgBar.find('.progressbar-value').animate({ width: 98.5 * element.width() / 100 }, 1000);

              timeout2 =  $timeout(function(){
                      $scope.message = '';
                      $scope.perc    = '';
                      prgBar.prop('data-value',0)
                      prgBar.find('.progressbar-value').animate({ width: 0 * element.width() / 100 }, 1000);
                      $(element).hide();
                      $timeout.cancel(timeout2);
              },1000)

            }


            $scope.stop = function(data){
              $scope.perc    = 0;
              $scope.message = data.message;
              prgBar.find('.progressbar-value').animate({ width: 0 * element.width() / 100 }, 1000);
              $timeout.cancel(timeout1);
              $(element).hide();
            }

        }
    }
}]);


JimmyDashboard.directive("ckEditor", ["FlashMessage", "$timeout", function(FlashMessage,$timeout) {
    return {
        require: '?ngModel',
        link: function($scope,element,attrs,ngModel) {
            //var ck  = $(element).ckeditor();
            var ck = CKEDITOR.replace(element[0],
                                     { filebrowserImageUploadUrl: "reports/image-upload"

            });

            if (!ngModel) return;

            if(attrs["ckHeight"]){
               ck.config.height = attrs["ckHeight"];
            }

            if(attrs["ckWidth"]){
               ck.config.width = attrs["ckWidth"];
            }

            ck.on('instanceReady', function() {
             if(ngModel.$viewValue)
              ck.setData(ngModel.$viewValue);
            });

            function updateModel() {
                $scope.$apply(function() {
                    ngModel.$setViewValue(ck.getData());
                });
            }

            function updateModelDataReady() {
                $scope.$apply(function() {
                  if(ck.getData())
                    ngModel.$setViewValue(ck.getData());
                });
            }

            ck.on('change', updateModel);
            ck.on('key', updateModel);
            ck.on('dataReady', updateModelDataReady);

            ngModel.$render = function(value) {

              //ck.setData(ngModel.$viewValue);
            };
        }
    }
}]);

JimmyDashboard.directive("sortable", ["FlashMessage", "$timeout", function(FlashMessage,$timeout) {
    return {
        link: function($scope,element,attrs,ngModel) {
          var sortable = $( element ).sortable({axis:"y", cursor:"move", handle:".handle"});
                sortable.on('sortupdate',function(){
                  var idsInOrder = sortable.sortable("toArray");
                   $scope.$parent.updateOrder(idsInOrder);
                })

        }
    }
}]);



JimmyDashboard.filter('to_trusted', ['$sce', function($sce){
        return function(text) {
          if(typeof text =="string")
            return $sce.trustAsHtml(text);
        };
}]);

JimmyDashboard.filter('to_trusted_metric_value', ['$sce', function($sce){
        return function(text) {
           if(typeof text =="string")
            return $sce.trustAsHtml(" "+text);
           else
            return $sce.trustAsHtml(" "+text);
        };
}]);


JimmyDashboard.filter('naturalSort', ['$sce','naturalService',function($sce,naturalService){
        return function(data,sort_by,sort_order) {
          var sortedItems = [];
          angular.forEach(data,function(value,key){
            data[key][sort_by] =naturalService.naturalValue(value[sort_by]);
          })
          return data;
        };
}]);

JimmyDashboard.filter('pagenate', [
  function() {
    return function(arr, pagenationProps) {
      var resultArr = [];
      for (
        var ctr = pagenationProps.currentPage*pagenationProps.itemsPerPage;
        ctr < (pagenationProps.currentPage+1)*pagenationProps.itemsPerPage && ctr < arr.length;
        ctr++
      ) {
        resultArr.push(arr[ctr]);
      }
      return resultArr;
    }
}]);


JimmyDashboard.directive("showCampaign",function(){

  return{
    link:function($scope,element,attrs,ngModel){
        $scope.$parent.$watch('widget.data.args.show_campaign', function() {


          if($scope.$parent.widget.show_campaign){
           $(".data-campaign").show();

         } else {
           $(element).find(".data-campaign").hide();
         }
        });

    }
  }
})


JimmyDashboard.directive("saveAnimate",["$timeout", function($timeout){

  return{
    scope:false,
    link:function($scope,element,attrs,ngModel){
         $scope.saveDisabled  = false;

         $(element).click(function(){
           $scope.saveDisabled = true;
            var text = null;
          if($(element).closest("form").parsley().isValid()){
            text = $(element).find(".button-content").text();
            $(element).find(".button-content").text(attrs['loadingText'])

            $timeout(function(){
              $(element).find(".button-content").text(text);
              $scope.saveDisabled = false;
            },500);

          } else {
            $scope.saveDisabled = false;
          }
          return true;
        })

    }
  }
}])




JimmyDashboard.directive("filter",["$routeParams", function($routeParams){

  return{
    scope:false,
    link:function($scope,element,attrs,ngModel){
      $(function() {
          $(element).find('input')
            .focus(function() {
                $(this).stop().animate({width: 200}, 'slow');
            })
            .blur(function() {
                $(this).stop().animate({width: 100}, 'slow');
            })
            .keyup(function(){
              var val = $(this).val();
                              console.log($ctrlScope);
                              console.log(angular.element(attrs["filter"]));

              if(attrs["filter"]){
                var $ctrlScope = angular.element(attrs["filter"]).scope();
                $ctrlScope.query = val;

                $ctrlScope.$apply(function(){
                   $ctrlScope.search();
                })
              } else {

                $scope.query = val;

                $scope.$apply(function(){
                   $scope.search();

                });
              }
            })

      });

    }
  }
}])



JimmyDashboard.directive("niceScroll",["$routeParams", function($routeParams){

  return{
    link:function($scope,element,attrs,ngModel){
      $(function() {

          $(element).niceScroll({cursorcolor:"#ccc"});

      });

    }
  }
}])




JimmyDashboard.directive("customTitle",["$routeParams", function($routeParams){

  return{
    link:function($scope,element,attrs,ngModel){
        var len = attrs["length"];

        if(!len)
            len = 10;

        if(attrs["customTitle"]){
          if(attrs["customTitle"].length>len)
            $(element).html(attrs["customTitle"].substr(0,len)+'...');
          else
            $(element).html(attrs["customTitle"]);
        }
    }
  }
}])


JimmyDashboard.directive("titleEdit",["$routeParams", "FlashMessage", function($routeParams,FlashMessage){

  return{
    link:function($scope,element,attrs,ngModel){

        $(element).click(function(){
          $(element).after('<div class="form-input col-md-12 row" style="padding-left:10px"><input class="'+attrs["titleEdit"]+'_title_edit" placeholder="" class="" type="text" name="title" id="title" data-required="true"></div>');
          $("#title").val( $(element).html());
          $(element).hide();
          $("#title").focus();

          $("#title").blur(function(){
            $(element).show();
            $("#title").parent().remove();
          })

          $("#title").keyup(function(e){
            if(e.keyCode==13) {
              if($("#title").val().length<1)
                return false;
              var old_title =  $(element).html();
              $scope.updateTitle($("#title").val()).$promise.then(function(data){
                FlashMessage.setMessage(data);
                if(!data.success)   $(element).html(old_title);
              });
              $(element).html($("#title").val());
              $(element).show();
              $("#title").parent().remove();
            }
          })

        })

        $(element).tooltip({ container: 'body'});

    }
  }
}])


JimmyDashboard.directive("tabs",function(){

  return {
    scope:false,
    link:function($scope,element){
       $(function() {
         $(element).tabs();
       });
    }
  }
})

JimmyDashboard.directive("resizeBtn", function($window) {
    return function (scope, element) {
        var w = angular.element($window);
        scope.getWindowDimensions = function () {
            return {
                'h': w.height(),
                'w': w.width()
            };
        };
        scope.$watch(scope.getWindowDimensions, function (newValue, oldValue) {
               var available = newValue.h-277;
            if (available < 500) {
                var btnHeight = (available-70)/3;
                var newWidth  = (btnHeight/130)*150;
                var wrapperleft = 30+(150-newWidth)/2;
                $(".sidebar-btn .wrapper").height(btnHeight+"px");
                $(".sidebar-btn .wrapper").width(newWidth+"px");
                $(".sidebar-btn .wrapper").css("left", wrapperleft+"px");
            }else {
                var btnHeight = 130;
                $(".sidebar-btn .wrapper").height("130px");
                $(".sidebar-btn .wrapper").width("150px");
                $(".sidebar-btn .wrapper").css("left", "30px");
            }
                scope.style = function () {
                    return {
                        'height': (btnHeight) + 'px',

                    };
                };

        }, true);

        w.bind('resizeBtn', function () {
            scope.$apply();
        });
    }
});


JimmyDashboard.directive("imageFallback",function(){

  return {
    link:function($scope,element){
      $(element).error(function(e){
          $(element).attr('src', '/images/noimage.jpeg');
          e.preventDefault();
          return false;
      });

    }
  }

})

JimmyDashboard.directive("letterFallBack",function(){

  return { 
       restrict: 'A',
       link:function(scope,element, attrs){
            var img_element =element[0].querySelector('img');
            var userName =  scope.$eval(attrs.user);;
          $(img_element).error(function(e){
              var color = "#5FBA7D";
             // var color = colors[Math.floor(Math.random() * colors.length)];
              $(element).html(userName.substring(0,1));
               $(element).css({'background': color, 
                                'color': "white", 
                                'text-align':'center',
                                'font-size':'28px',
                                'line-height': 1.3,
                                'font-weight' : 'bolder'
                              });
              e.preventDefault();
              return false;
          });

    }
  }
});



JimmyDashboard.directive('ngCsv', ['$parse', '$q', function ($parse, $q) {
    return {
      restrict: 'AC',
      replace: false,
      transclude: true,
      scope: {
        data:'&ngCsv',
        filename:'@filename',
        header: '&csvHeader',
        txtDelim: '@textDelimiter',
        fieldSep: '@fieldSeparator',
        lazyLoad: '@lazyLoad',
        ngClick: '&'
      },
      controller: [
        '$scope',
        '$element',
        '$attrs',
        '$transclude',
        function ($scope, $element, $attrs, $transclude) {

          var stringifyCell = function(data) {
            if (typeof data === 'string') {
              data = data.replace(/"/g, '"""'); // Escape double qoutes
              data = data.replace(/\+/gi, '"+"'); // Escape double qoutes
              data = data.replace(/,/g, ''); // Escape comma
              data = data.replace(/<[^>]*>/g, "");
              if ($scope.txtDelim) data = $scope.txtDelim + data + $scope.txtDelim;

              return data;
            }

            if (typeof data === 'boolean') {
              return data ? 'TRUE' : 'FALSE';
            }

            return data;
          };

          $scope.csv = '';

          if (!angular.isDefined($scope.lazyLoad) || $scope.lazyLoad != "true"){
            if (angular.isArray($scope.data)){
              $scope.$watch("data", function (newValue) {
                $scope.buildCsv($scope.data(), function() { } );
              }, true);
            }
          }

          $scope.buildCsv = function (data, callback)   {
            var csvContent = "data:text/csv;  charset=utf-8";

            $q.when(data).then(function (responseData){

              // Check if there's a provided header array
              if (angular.isDefined($attrs.csvHeader))
              {
                var header = $scope.header;
                var encodingArray, headerString;

                if (angular.isArray(header)) {
                  encodingArray = header;
                } else  {
                  encodingArray = [];
                  angular.forEach(header, function(title, key){
                    this.push(stringifyCell(title));
                  }, encodingArray);
                }

                headerString = encodingArray.join($scope.fieldSep ? $scope.fieldSep : ",");

                csvContent += "\n,"+headerString + "\n";
              }

              var arrData;

              if (angular.isArray(responseData))
                arrData = responseData;
              else
                arrData = responseData();


              angular.forEach(arrData, function(row, index){
                var dataString, infoArray;

                if (angular.isArray(row)) {

                  infoArray = [];

                  angular.forEach(row, function(field, key){
                    this.push(stringifyCell(field));
                  }, infoArray);

                }  else {
                  infoArray = [];

                  angular.forEach(row, function(field, key)
                  {
                    this.push(stringifyCell(field));
                  }, infoArray);
                }

                dataString = infoArray.join($scope.fieldSep ? $scope.fieldSep : ",");
                csvContent += index < arrData.length ? dataString + "\n" : dataString;


              });

              $scope.csv = encodeURI(csvContent);

              }).then(function() {
                callback();
              });

            };

            $scope.getFilename = function ()
            {
              return $scope.filename+'.csv' || 'download.csv';
            };
        }
      ],
      template:'<div class="element" ng-transclude></div>' +
        '<a class="hidden-link"  ng-hide="true" download="{{ getFilename() }}"></a>',
      link: function (scope, element, attrs) {

        var subject = angular.element(element.children()[0]),
            link    = angular.element(element.children()[1]);

        $(element).tooltip();

        function doClick() {
          link[0].href = "";
          link[0].click();
          link[0].href = scope.csv;
          link[0].click();
        }

        subject.bind('click', function (e)
        {
          //console.log(scope.data().data);
           var keys   =  [];
           var data   =  [];
           var total =  [];
           var i = 0;

           angular.forEach(scope.data().data.args.extra_fields, function(field, key){
             keys[i]      = field[2];
             total[i]     = ""; // padding
             i++;
           })


          angular.forEach(scope.data().data.args.fields_raw_data, function(field, key){
             keys[i]      = field[2];
             i++;
          })

          scope.header = keys;

          angular.forEach(scope.data().data.rawData, function(field, key){
             delete field.$$hashKey;
             delete field.__proto__;

             data[key]      = field;
          })

          var len = total.length;
          angular.forEach(scope.data().data.rawDataTotal, function(field, key){
            total[len] = field.value;
            len++;
          });

          data[data.length] =  total;

          scope.buildCsv(data, function(){
            doClick();
          });

          if (!!scope.ngClick) {
            scope.ngClick();
          }
        });
      }
    };
  }]);

JimmyDashboard.directive('tooglefour', function () {
    return function (scope, elem, attrs) {
        scope.sho = 1;
        scope.choose = function () {
            if(scope.sho == 1){
              scope.sho = 2;
            } else if(scope.sho == 2){
              scope.sho = 3;
            } else if(scope.sho == 3){
              scope.sho = 4;
            } else if(scope.sho == 4){
              scope.sho = 5;
            } else {
              scope.sho = 1;
            }
        }
    }
});

JimmyDashboard.directive('tooglefourb', function () {
    return function (scope, elem, attrs) {
        scope.sho = 1;
        scope.choose = function () {
      if(scope.sho == 1){
        scope.sho = 2;
      } else if(scope.sho == 2){
        scope.sho = 1;
      } else {
        scope.sho = 1;
      }
        }
    }
});

var angles = angular.module("angles", []);

angles.chart = function (type) {
    return {
        restrict: "A",
        scope: {
            data: "=",
            options: "=",
            id: "@",
            width: "=",
            height: "=",
            resize: "=",
            chart: "@",
            segments: "@",
            responsive: "=",
            tooltip: "=",
            legend: "="
        },
        link: function ($scope, $elem) {
            var ctx = $elem[0].getContext("2d");
            var autosize = false;

            $scope.size = function () {
                if ($scope.width <= 0) {
                    $elem.width($elem.parent().width());
                    ctx.canvas.width = $elem.width();
                } else {
                    ctx.canvas.width = $scope.width || ctx.canvas.width;
                    autosize = true;
                }

                if($scope.height <= 0){
                    $elem.height($elem.parent().height());
                    ctx.canvas.height = ctx.canvas.width / 2;
                } else {
                    ctx.canvas.height = $scope.height || ctx.canvas.height;
                    autosize = true;
                }
            }

            $scope.$watch("data", function (newVal, oldVal) {
                if(chartCreated)
                    chartCreated.destroy();

                // if data not defined, exit
                if (!newVal) {
                    return;
                }
                if ($scope.chart) { type = $scope.chart; }

                if(autosize){
                    $scope.size();
                    chart = new Chart(ctx);
                };

                if($scope.responsive || $scope.resize)
                    $scope.options.responsive = true;

                if($scope.responsive !== undefined)
                    $scope.options.responsive = $scope.responsive;

                chartCreated = chart[type]($scope.data, $scope.options);
                chartCreated.update();
                if($scope.legend)
                    angular.element($elem[0]).parent().after( chartCreated.generateLegend() );
            }, true);

            $scope.$watch("tooltip", function (newVal, oldVal) {
                if (chartCreated)
                    chartCreated.draw();
                if(newVal===undefined || !chartCreated.segments)
                    return;
                if(!isFinite(newVal) || newVal >= chartCreated.segments.length || newVal < 0)
                    return;
                var activeSegment = chartCreated.segments[newVal];
                activeSegment.save();
                activeSegment.fillColor = activeSegment.highlightColor;
                chartCreated.showTooltip([activeSegment]);
                activeSegment.restore();
            }, true);

            $scope.size();
            var chart = new Chart(ctx);
            var chartCreated;
        }
    }
}

JimmyDashboard.directive('scrollHitBottom',['ActivityLog', function (ActivityLog) {

  return {

    restrict: 'A',
    link: function (scope, element, attrs) {
      var fn = scope.$eval(attrs.execOnScrollToBottom),
          clientHeight = element[0].clientHeight;
         var scopeObj = element.scope();
      element.on('scroll', function (e) {
        var el = e.target;       
        if ((el.scrollHeight - el.scrollTop) === clientHeight) { // fully scrolled
            limit = scopeObj.limit+10;
            scopeObj.limit = limit;
          ActivityLog.query({"limit":limit},function(data) {
            if (data.success) {
                scopeObj.activityLog = data.logData;
            }
          });
          scope.$apply(fn);
        }
      });
    }

  };

}]);


JimmyDashboard.directive("linechart", function () { return angles.chart("Line"); });

/**
 * Tools - Directive for iBox tools elements in right corner of ibox
 */
function iboxTools($timeout) {
    return {
        restrict: 'A',
        scope: true,
        templateUrl: 'src/app/report/ibox_tools.html',
        controller: ["$scope", "$element", "$attrs", function ($scope, $element,$attrs) {
            // Function for collapse ibox
            $scope.showhide = function () {
                var ibox = $element.closest('div.ibox');
                var icon = $element.find('i:first');
                var content = ibox.find('div.ibox-content');
                content.slideToggle(200);
                // Toggle icon from up to down
                icon.toggleClass('fa-chevron-up').toggleClass('fa-chevron-down');
                ibox.toggleClass('').toggleClass('border-bottom');
                $timeout(function () {
                    ibox.resize();
                    ibox.find('[id^=map-]').resize();
                }, 50);
            },
                // Function for close ibox
                $scope.closebox = function () {
                    var ibox = $element.closest('div.ibox');
                    ibox.remove();
                }
        }]
    };
}
iboxTools.$inject = ["$timeout"];;

JimmyDashboard.directive('iboxTools', iboxTools)

//Make a field editable

JimmyDashboard.directive('knob', ['$timeout', function($timeout) {
    'use strict';

    return {
        restrict: 'EA',
        replace: true,
        template: '<input value="{{ knobData }}"/>',
        scope: {
            knobData: '=',
            knobOptions: '&'
        },
        link: function($scope, $element, $attrs) {

      var v  = $attrs['value'];
      var v1 = $attrs['compareValue'];

      // new mod
      var num1 = v.toString();;
      num1 = num1.replace(/[\,\%]/,"");
      var num2 = v1.toString();
      num2 = num2.replace(/[\,\%]/,"");


      //console.log("feORI");
      //console.log(num1);
      //console.log("feORI");

      var val = Math.round(((num1*1-num2*1)/num2)*100,2);
      val = val.toString().replace(/-/,"")
      //$scope.valuknob = val*1;
      // new mod

      /*var v1 = $attrs['knobData'];
      console.log(v1);
      var v2 = $attrs['knobDrt'];
      console.log(v2);


      var val = Math.round(((v2-v1)/v2)*100,2);// + '%';
      //knobData = val;
      //$scope.knobData = val;*/
            var knobInit = $scope.knobOptions() || {};

            /*knobInit.release = function(newValue) {
                $timeout(function() {
                    $scope.knobData = newValue;
                    $scope.$apply();
                });
            };

            $scope.$watch('knobData', function(newValue, oldValue) {
                if (newValue != oldValue) {
                    $($element).val(newValue).change();
                }
            });*/

            $($element).val(val*1).knob(knobInit);
        }
    };
}]);
