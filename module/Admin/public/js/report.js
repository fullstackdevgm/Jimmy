// JavaScript Document

//JimmyJS = Class.Create();

var JimmyJS = {
		baseUrl: baseUrl,
		adminUrl: adminUrl,
	    _init:function(){
			/*jQuery.ajaxSetup({
				  beforeSend: function() {
					 $('#loader').show();
				  },
				  done: function(){
					 $('#loader').hide();
				  }
			});*/
		},
		working:function(div){
			jQuery("#"+div).html('<div style="marin:0px auto"><img src="/images/loading-new.gif" id="loading" /><span></span></div>');	
		},
		saving:function(id){
			this.disable(id);	
			jQuery('#'+id).html('<i class="icon-refresh icon-spin"></i> Saving ...');	
		},
		disable:function(el_id){
			jQuery('#'+el_id).attr('disabled','disabled');
		},
		enable:function(el_id){
			jQuery('#'+el_id).removeAttr('disabled');
		},
		loadGraphData:function(report_id){
			 jQuery.ajax({
				   		   url:this.adminUrl+'/client/graph/'+report_id,
						   beforeSend: function(){JimmyJS.working('container')},
						 })
						 .done(function ( data ) {
							var jsonData = jQuery.parseJSON(data);
							    if(jsonData.success == true){
								  eval(jsonData.script)
								}else
								  jQuery("#container").html(jsonData.error)
						});
		
		},
		loadRawData:function(report_id){
			 jQuery.ajax({
				   		   url:this.adminUrl+'/client/raw/'+report_id,
						   beforeSend: function(){JimmyJS.working('raw-data')},
						 })
						 .done(function ( data ) {
							var jsonData = jQuery.parseJSON(data);
							    if(jsonData.success == true){
								  jQuery("#raw-data").html(jsonData.html)
								}else
								  jQuery("#raw-data").html(jsonData.error)
						});
		
		},
		saveClient:function(){
			    this.saving('save');
				 
		 		jQuery.post(this.adminUrl+'/client/save',$('#client_form').serialize())
						 .done(function ( data ) {
							var jsonData = jQuery.parseJSON(data);
								 if(jsonData.success == true){
									 
									if(jsonData.html)
									   jQuery('#account').html(jsonData.html);
									
									jQuery("#save").html('Saving changes');	
									jQuery("#save").removeAttr('disabled');
									
								    jQuery('#account').toggle(); 
	 								jQuery('#edit').toggle(); 
									jQuery('#message').html('<div class="alert alert-success"><a class="close" data-dismiss="alert">×</a><strong>Success!</strong>Client  updated successfully</div>');
									
								 } else {
								 	jQuery('#message').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>Error!</strong>There were some problems while updating the client.</div>');
								 }

							
						});
		
		
		},
		changePwd:function(){
			    this.saving('savePwd');
				 
		 		jQuery.post(this.adminUrl+'/client/changepwd',$('#change_pwd_form').serialize())
						 .done(function ( data ) {
							var jsonData = jQuery.parseJSON(data);
							 if(jsonData.success == true){
								 
								if(jsonData.html)
								   jQuery('#account').html(jsonData.html);
								
								//jQuery("#save").html('Saving changes');	
								jQuery("#save").removeAttr('disabled');
								
								jQuery('#account').toggle(); 
								jQuery('#changePwdForm').toggle(); 
								jQuery('#message').html('<div class="alert alert-success"><a class="close" data-dismiss="alert">×</a><strong>Success!</strong> The password has been changed</div>');
								
							 } else {
								jQuery('#message').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>Error!</strong>There were some problems while changing the password.</div>');
							 }
							
						});
		},
		updateAdminAccount:function(){
			    this.saving('save');
				 
		 		jQuery.post(this.adminUrl+'/settings/updateaccount',$('#admin_account_form').serialize())
						 .done(function ( data ) {
							var jsonData = jQuery.parseJSON(data);
								 if(jsonData.success == true){
									 
									if(jsonData.html)
									   jQuery('#account').html(jsonData.html);
									
									jQuery("#save").html('Saving changes');	
									jQuery("#save").removeAttr('disabled');
									
								    jQuery('#account').toggle(); 
	 								jQuery('#edit').toggle(); 
									jQuery('#message').html('<div class="alert alert-success"><a class="close" data-dismiss="alert">×</a><strong>Success!</strong>Account updated successfully</div>');
									
								 } else {
								 	jQuery('#message').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>Error!</strong>There were some problems while updating the account.</div>');
								 }

							
						});
		
		
		},
		changeAdminPwd:function(){
			    this.saving('savePwd');
				 
		 		jQuery.post(this.adminUrl+'/settings/changeadminpwd',$('#admin_change_pwd').serialize())
						 .done(function ( data ) {
							var jsonData = jQuery.parseJSON(data);
							 if(jsonData.success == true){
								 
								if(jsonData.html)
								   jQuery('#account').html(jsonData.html);
								
								//jQuery("#save").html('Saving changes');	
								jQuery("#save").removeAttr('disabled');
								
								jQuery('#account').toggle(); 
								jQuery('#changePwdForm').toggle(); 
								jQuery('#message').html('<div class="alert alert-success"><a class="close" data-dismiss="alert">×</a><strong>Success!</strong> The password has been changed</div>');
								
							 } else {
								jQuery('#message').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>Error!</strong>There were some problems while changing the password.</div>');
							 }
							
						});
		},
		
}






jQuery(document).ready(function(){
	JimmyJS._init();
})