// JavaScript Document

//JimmyJS = Class.Create();
var baseUrl = '';
var JimmyJS = {
		baseUrl: baseUrl,
	    _init:function(){
			
				
		},
		alertSuccess:function(msg,no_autoclose){
			this.alert('alert-container',msg,'success');
			
			if(!no_autoclose)
			   this.autoCloseAlerts('alert-container',5000);
		},
		alertError:function(msg,no_autoclose){
			this.alert('alert-container',msg,'error');
			
			if(!no_autoclose)
			   this.autoCloseAlerts('alert-container',5000);
		},
		alertInfo:function(msg,no_autoclose){
			this.alert('alert-container',msg,'info');
			
			if(!no_autoclose)
			   this.autoCloseAlerts('alert-container',5000);
		},
		alertOption:function(msg,no_autoclose){
			this.alert('alert-container',msg,'block');
			
			if(!no_autoclose)
			   this.autoCloseAlerts('alert-container',5000);
		},
		alert:function(container_id,msg,type,no_autoclose){
			
			var alert_class = 'alert-'+type;
			var alert_msg   = "<div class='alert "+ alert_class +"'><a class='close' data-dismiss='alert'>×</a>" + msg + "</div>";
			
			jQuery('#'+container_id).html(alert_msg);

			if(!no_autoclose)
			   this.autoCloseAlerts(container_id,5000);
		},
		autoCloseAlerts:function(container_id,autoclose_time){
				window.setTimeout(function() {
					$("#" + container_id + " .alert").fadeTo(500, 0).slideUp(500, function(){
						$(this).remove(); 
					});
				}, autoclose_time);
		},
		working:function(div){
			jQuery("#"+div).html('<div class="spinner"><i class="icon-spinner icon-spin icon-large"></i><span></span></div>');	
		},
		saving:function(id){
			this.disable(id);	
			jQuery('#'+id).html('<i class="icon-spinner icon-spin icon-large"></i> Saving ...');	
		},
		disable:function(el_id){
			jQuery('#'+el_id).attr('disabled','disabled');
		},
		enable:function(el_id){
			jQuery('#'+el_id).removeAttr('disabled');
		},
		loadAdminGraphData:function(report_id){
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
		loadAdminRawData:function(report_id){
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
		filterData:function(id,form){
			
			var id = id;
			var splitted_id = id.split('_');
			var widget_id   = splitted_id[1];

			var postParams = $(form).serialize();
				
				 JimmyJS.working('widget_'+widget_id);
				
				 jQuery.post('/widget/load/'+widget_id,postParams)
				 .done(function ( data ) {
					var jsonData = jQuery.parseJSON(data);
						 if(jsonData.success == true){
							 if(jsonData.script)	
							  eval(jsonData.script)
							 else
							  jQuery("#widget_"+widget_id).html(jsonData.html)
	
							}else
							  jQuery("#widget_"+widget_id).html(jsonData.error)
				});
		},
		loadGraphData:function(report_id,container){
			 jQuery.ajax({
				   		   url:this.baseUrl+'report/graph/'+report_id,
						   beforeSend: function(){JimmyJS.working('container')},
						 })
						 .done(function ( data ) {
							var jsonData = jQuery.parseJSON(data);
							    if(jsonData.success == true){
								  eval(jsonData.script)
								}else
								  jQuery("#"+container).html(jsonData.error)
						});
		
		},
		loadTableData:function(report_id,container){
			 jQuery.ajax({
				   		   url:this.baseUrl+'report/raw/'+report_id,
						   beforeSend: function(){JimmyJS.working('raw-data')},
						 })
						 .done(function ( data ) {
							var jsonData = jQuery.parseJSON(data);
							    if(jsonData.success == true){
								  jQuery("#"+container).html(jsonData.html)
								}else
								  jQuery("#"+container).html(jsonData.error)
						});
		
		},
		loadWidgets:function(){
			
			var widgets = jQuery('div.widgets-holder');
			//console.log(widgets);
			
			   widgets.each(function(index,el){
				   	
					var id = jQuery(el).attr('id');
					var splitted_id = id.split('_');
					var widget_id   = splitted_id[1];

					 jQuery.ajax({
				   		   url:'/widget/load/'+widget_id,
						   beforeSend: function(){JimmyJS.working(id)},
						 })
						 .done(function ( data ) {
							var jsonData = jQuery.parseJSON(data);

							    if(jsonData.success == true){
							    	 jQuery("#"+id).prev().show();
								 if(jsonData.script){	
								   eval(jsonData.script);
								 } else if(jsonData.html!=""){
								   jQuery("#"+id).html(jsonData.html)
								 } else {
								  	jQuery("#"+id).html("No data returned!");
								 }

								}else
								  jQuery("#"+id).html(jsonData.error)
						});
				   
				   })		
		},
		saveUser:function(url){
			 var formData = JSON.stringify(jQuery('#client_form').serialize());
			 var id	 			   = jQuery('[name="id"]').val();
			 var type	 		   = jQuery('[name="type"]').val();
			 var name 			   = jQuery('#name').val();
			 var email			   = jQuery('#email').val();
			 var self = this;
			 this.saving('save')
				 $.ajaxFileUpload({
					url:url,
					secureuri:false,
					fileElementId:'logo',
					dataType: 'json',
					data:{id:id,type:type,name:name,email:email},
					success: function (data, status){
						var jsonData = jQuery.parseJSON(data);
						//console.log(jsonData)
						 if(jsonData.success == true){
							 
							if(jsonData.html)
							   jQuery('#account').html(jsonData.html);
							
							jQuery("#save").html('Saving changes');	
							jQuery("#save").removeAttr('disabled');
							
							jQuery('#account').toggle(); 
							jQuery('#edit').toggle(); 
							
							self.alertSuccess('Client Updated.');			
							 
						 } else {
							self.alertError('Client couldnot be updated.')	
						 }
						 
	
					},
					error: function (data, status, e){
							//alert(e);
					}
				})
		},
		cancelUser:function(url){
			var self = this;
			
		 		jQuery.post('/cancel',null)
						 .done(function ( data ) {
							var jsonData = jQuery.parseJSON(data);
							 console.log(jsonData)
							 if(jsonData.success == true){
								self.alertSuccess(jsonData.message);	
								
								window.setTimeout(function() {
										location.href='/user/logout';	
									}, 2000);	

							 } else {
								self.alertError(jsonData.message)	
							 }
							
						});
		},
		saveClient:function(url){
			 var formData = JSON.stringify(jQuery('#client_form').serialize());
			 var id	 	   = jQuery('[name="id"]').val();
			 var name 	   = jQuery('[name="name"]').val();
			 var accountid = jQuery('[name="accounts[0][accountid]"]').val()?jQuery('[name="accounts[0][accountid]"]').val():'';
			 var channel   = jQuery('[name="accounts[0][channel]"]').val()?jQuery('[name="accounts[0][channel]"]').val():'';
			 var api_auth_info   = jQuery('[name="accounts[0][apiauthinfo]"]').val()?jQuery('[name="accounts[0][apiauthinfo]"]').val():'';
			 var source_name     = jQuery('[name="accounts[0][name]"]').val()?jQuery('[name="accounts[0][name]"]').val():'';

			 	
			 this.saving('save');

				 $.ajaxFileUpload({
					url:url,
					secureuri:false,
					fileElementId:'logo',
					dataType: 'json',
					data:{id:id,name:name,accountid:accountid,channel:channel,apiauthinfo:api_auth_info,source_name:source_name},
					//data:formData,
					success: function (data, status){
						var jsonData = jQuery.parseJSON(data);

						 if(jsonData.success == true){
							//jQuery("ul.tabs #tab2-head").click();
							
							if(jsonData.html)
							   jQuery('#client-container').html(jsonData.html);
							
							JimmyJS.alertSuccess('Client Updated.');			
							 
						 } else {
							 if(jsonData.html)
							   jQuery('#client-container').html(jsonData.html);
							
							JimmyJS.alertError('Client couldnot be updated.')	
						 }
					},
					error: function (data, status, e){
							//alert(e);
					}
				})
		},
		viewClient:function(url,select_client_id,select_tab2,select_edit,select_add){

			   JimmyJS.working('client-list');
			   	var params = null;

			    jQuery.ajax({
					   url:url,
					   params:params,
					   beforeSend: function(){},
					 })
					 .done(function ( data ) {
						var jsonData = jQuery.parseJSON(data);
							if(jsonData.success == true){
								if(jsonData.script){
								 
								 eval(jsonData.script)
								 
								}else
							  	
							  	 jQuery("#client-list").html(jsonData.html);


							  	 if(select_client_id){
							  	 	jQuery("tr[data-id=" + select_client_id + "]").click();
							  	 }

							  	 if(select_tab2){
									jQuery("ul.tabs #tab2-head").click()
							  	 }


							  	 if(select_edit){
							  	 	jQuery("#change").click()
							  	 }
							  
							     if(select_add){
									jQuery("#add_report").click();
							     }

							  if(jsonData.message)
							  	 JimmyJS.alertSuccess(jsonData.message);

							} else{
							  
							  jQuery("#client-list").html(jsonData.error);

 							  if(jsonData.message)
							  	JimmyJS.alertError(jsonData.message);							
							}
					});				 
		},
		viewClientAndReport:function(url,report_url){

			   JimmyJS.working('client-list');
			   	var params = null;

			    jQuery.ajax({
					   url:url,
					   params:params,
					   beforeSend: function(){},
					 })
					 .done(function ( data ) {
						var jsonData = jQuery.parseJSON(data);
							if(jsonData.success == true){
								if(jsonData.script){
								 
								 eval(jsonData.script)
								 
								} else
							  	
							  	jQuery("#client-list").html(jsonData.html);
							  	
								  
								JimmyJS.working('report-container');
								JimmyJS.get(report_url,'report-container');
									  	
							    if(jsonData.message)
							  	   JimmyJS.alertSuccess(jsonData.message);

							} else{
							  
							  jQuery("#client-list").html(jsonData.error);

 							  if(jsonData.message)
							  	JimmyJS.alertError(jsonData.message);							
							}
					});				 
		},
		changePwd:function(url){
			    
				var self = this;

				 if($('#pwd').val().length == 0 || $('#pwd').val().length == 0 ){
					self.alertError('Please enter the password');
					return true;	
				 } else if($('#pwd').val()!=$('#cpwd').val()){
					self.alertError('Password donot match.');
					return true;	
				 } else {
						this.saving('savePwd');
						jQuery.post('/changepwd',$('#change_pwd_form').serialize())
								 .done(function ( data ) {
									var jsonData = jQuery.parseJSON(data);
									 
									 if(jsonData.success == true){
										 
										if(jsonData.html)
										   jQuery('#account').html(jsonData.html);
										
										//jQuery("#save").html('Saving changes');	
										jQuery("#savePwd").removeAttr('disabled');
										jQuery("#savePwd").text('Save');
										
										jQuery('#account').toggle(); 
										jQuery('#changePwdForm').toggle(); 
										self.alertSuccess('Passsword Changed.');			
									 
									 } else {
										self.alertError('Password could not be changed.')	
										jQuery("#savePwd").removeAttr('disabled');
										jQuery("#savePwd").html('Save');
										
									 }
									
								});
				}
		},
		saveAgency:function(){
			 var self = this;
			 var id	 			   = jQuery('[name="id"]').val();
			 var name 			   = jQuery('#name').val();
			 var email			   = jQuery('#email').val();
			 var state			   = jQuery('[name="state"]').val();
			 
			 this.saving('save-agency')
				
				 $.ajaxFileUpload({
					url:this.adminUrl+'/agency/save',
					secureuri:false,
					fileElementId:'logo',
					dataType: 'json',
					data:{id:id,name:name,email:email,state:state},
					success: function (data, status){
						var jsonData = jQuery.parseJSON(data);

						 if(jsonData.success == true){						 
							if(jsonData.html)
							   jQuery('#account').html(jsonData.html);
							jQuery('div.wrpr div.title h5').text(jQuery('#account dl.agency-account #agency-name').text());
							jQuery("#save-agency").html('Save');	
							jQuery("#save-agency").removeAttr('disabled');
							
							jQuery('#account').toggle(); 
							jQuery('#edit').toggle(); 

							self.alertSuccess('Agency Updated.');			
							
							 
						 } else {
							self.alertError('Agency couldnot be updated.')	
						 }
					},
					error: function (data, status, e){
							//alert(e);
					}
				})
			
		},
		saveProfile:function(){
			
			 var id	 			   = jQuery('[name="id"]').val();
			 var type	 		   = jQuery('[name="type"]').val();
			 var name 			   = jQuery('#name').val();
			 var email			   = jQuery('#email').val();
			 var state			   = jQuery('[name="state"]').val();
			 this.saving('save')
				
				 $.ajaxFileUpload({
					url:'/profile',
					secureuri:false,
					fileElementId:'logo',
					dataType: 'json',
					data:{id:id,type:type,name:name,email:email,state:state},
					success: function (data, status){
						var jsonData = jQuery.parseJSON(data);
						//console.log(jsonData)
						 if(jsonData.success == true){
							 
							if(jsonData.html)
							   jQuery('#account').html(jsonData.html);
							
							jQuery("#save").html('Saving changes');	
							jQuery("#save").removeAttr('disabled');
							
							jQuery('#account').toggle(); 
							jQuery('#edit').toggle(); 
							
							this.alertSuccess('Profile Updated.');			
							 
						 } else {
							this.alertError('Profile couldnot be updated.')	
						 }
					},
					error: function (data, status, e){
							//alert(e);
					}
				})
			
		},
		changeAgencyPwd:function(){
			    this.saving('save-agency-pwd');
				 
		 		jQuery.post(this.adminUrl+'/agency/changepwd',$('#agency_change_pwd_form').serialize())
						 .done(function ( data ) {
							var jsonData = jQuery.parseJSON(data);
							 if(jsonData.success == true){
								 
								if(jsonData.html)
								   jQuery('#account').html(jsonData.html);
								
								//jQuery("#save").html('Saving changes');	
								jQuery("#save").removeAttr('disabled');
								
								jQuery('#account').toggle(); 
								jQuery('#changePwdForm').toggle(); 
								
								this.alertSuccess('Passsword Changed.');			
							 
							 } else {
								this.alertError('Password could not be changed.')	
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
									
									this.alertSuccess('Account Updated.');			
							 
								 } else {
									this.alertError('Account could not be changed.')	
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
								
								jQuery("#save").removeAttr('disabled');
								jQuery('#account').toggle(); 
								
								this.alertSuccess('Passsword Changed.');			
							 
							 } else {
								this.alertError('Password could not be changed.')	
							 }
							
						});
		},
		get:function(url,container,params,msg_container){
			 jQuery.ajax({
					   url:url,
					   params:params,
					   beforeSend: function(){},
					 })
					 .done(function ( data ) {
						var jsonData = jQuery.parseJSON(data);
							if(jsonData.success == true){
								if(jsonData.script){
								 
								 eval(jsonData.script)
								 
								}else
							  	 jQuery("#"+container).html(jsonData.html);
							  
							  if(jsonData.message)
							  	JimmyJS.alertSuccess(jsonData.message);
							} else{
							  jQuery("#"+container).html(jsonData.error);
 							  if(jsonData.message)
							  	JimmyJS.alertError(jsonData.message);							
							}
					});
		
		},
		post:function(url,form,container,msg_container,btnval){
				
				jQuery('#' + form + ' [name="submit"]').text('Saving...');
				jQuery('#' + form + ' [name="submit"]').addClass('disabled');

		 		jQuery.post(url,$('#'+form).serialize())
					 .done(function ( data ) {
						   if(btnval=="") btnval = "Save";
							jQuery('#' + form + ' [name="submit"]').removeClass('disabled');
						    jQuery('#' + form + ' [name="submit"]').text(btnval);

							var jsonData = jQuery.parseJSON(data);
							
							if(jsonData.success == true){
							   
							   if(jsonData.script){
								eval(jsonData.script);
							   } else	
							   	jQuery("#"+container).html(jsonData.html)
							   
							  if(jsonData.message)
							  	JimmyJS.alertSuccess(jsonData.message);
							} else {
								if(jsonData.message)
							  		JimmyJS.alertError(jsonData.message);	
							}
					});
		},
		wizardCreateReport:function(url,form,container,msg_container,btnval){
				
			jQuery('#' + form + ' [name="submit"]').text('Saving...');
			jQuery('#' + form + ' [name="submit"]').addClass('disabled');

	 		jQuery.post(url,$('#'+form).serialize())
				 .done(function ( data ) {
					   if(btnval=="") btnval = "Save";
						jQuery('#' + form + ' [name="submit"]').removeClass('disabled');
					    jQuery('#' + form + ' [name="submit"]').text(btnval);

						var jsonData = jQuery.parseJSON(data);
						
						if(jsonData.success == true){
						   
						   if(jsonData.script){
							eval(jsonData.script);
						   } else	
						   	jQuery("#"+container).html(jsonData.html)

						   	//if(jsonData.wizard_redirect_to_report)
						   	location.reload();
						   
						  if(jsonData.message)
						  	JimmyJS.alertSuccess(jsonData.message);
						} else {
							if(jsonData.message)
						  		JimmyJS.alertError(jsonData.message);	
						}
				});
		},
		shareReport:function(url,form,container,msg_container,btnval){
				
				jQuery('#' + form + ' [name="submit"]').text('Saving...');
				jQuery('#' + form + ' [name="submit"]').addClass('disabled');
		 		jQuery.post(url,$('#'+form).serialize())
					 .done(function ( data ) {
						   if(btnval=="") btnval = "Save";
							jQuery('#' + form + ' [name="submit"]').removeClass('disabled');
						    jQuery('#' + form + ' [name="submit"]').text(btnval);

							var jsonData = jQuery.parseJSON(data);
							
							if(jsonData.success == true){

							   if(jsonData.script){
								eval(jsonData.script);
							   } else	
							   	jQuery("#"+container).html(jsonData.html)
							   	
							  if(jsonData.message)
							  	JimmyJS.alert(msg_container,jsonData.message,'success');
							} else {
								if(jsonData.message)
							  	   JimmyJS.alert(msg_container,jsonData.message,'error');
							}
					});
		},	
		removeSharing:function(url,container,msg_container){
			 		
			 		jQuery.ajax({
					   url:url,
					   params:null,
					   beforeSend: function(){},
					 })
					 .done(function ( data ) {
						var jsonData = jQuery.parseJSON(data);
							if(jsonData.success == true){
								if(jsonData.script){
								 
								 eval(jsonData.script)
								 
								}else
							  	 jQuery("#"+container).html(jsonData.html);

							  if(jsonData.message)
							  	JimmyJS.alert(msg_container,jsonData.message,'success');
							} else{
							  jQuery("#"+container).html(jsonData.error);
 							  if(jsonData.message)
							  	   JimmyJS.alert(msg_container,jsonData.message,'error');
							}
					});
		
		},	
		cloneReport:function(url,form,container,msg_container,btnval){
				
				jQuery('#' + form + ' [name="submit"]').text('Saving...');
				jQuery('#' + form + ' [name="submit"]').addClass('disabled');
		 		jQuery.post(url,$('#'+form).serialize())
					 .done(function ( data ) {
						   if(btnval=="") btnval = "Save";
							jQuery('#' + form + ' [name="submit"]').removeClass('disabled');
						    jQuery('#' + form + ' [name="submit"]').text(btnval);

							var jsonData = jQuery.parseJSON(data);
							
							if(jsonData.success == true){
							   
							   if(jsonData.script){
								eval(jsonData.script);
							   } else	
							   	jQuery("#"+container).html(jsonData.html)
							  if(jsonData.message)
							  	JimmyJS.alert(msg_container,jsonData.message,'success');
							} else {
								if(jsonData.message)
							  	   JimmyJS.alert(msg_container,jsonData.message,'error');
							}
					});
		},	
		sortUpdate:function(url,params,container){
						 
		 		jQuery.post(url,params)
					 .done(function ( data ) {
						    var jsonData = jQuery.parseJSON(data);
							if(jsonData.success == true){
							   jQuery("#"+container).html(jsonData.message)
							} else
							   jQuery("#"+container).html(jsonData.error)
						
					});
		},
		getClients:function(url,params,container){
						 
 						jQuery.ajax({
					   		url:url,
					   		params:params,
					   		beforeSend: function(){},
					 	})	
						.done(function ( data ) {
						    var jsonData = jQuery.parseJSON(data);	
						
							if(jsonData.success == true){ 
								$("#"+container).empty();
							
								var data  = jQuery.parseJSON(jsonData.json);
								
								for (var key in data) {
									$("#"+container).append("<option value='" + parseInt(key)  +"'>" + data[key].toString() + "</option>");
								}
								
							} 
						
					});
		},
		removeClientAccount:function(url,client_account_id,confirm_delete){
						 

 						jQuery.ajax({
 							type:'POST',
					   		url:url,
					   		data:{'client_account_id':client_account_id,'confirm_delete':confirm_delete},
					 	})	
						.done(function ( data ) {
						    var jsonData = jQuery.parseJSON(data);	
						
							if(jsonData.success == true){ 
								$("#"+client_account_id).parent().parent().remove();
								JimmyJS.alert("msg-container",jsonData.message,'success');
							} else {
								JimmyJS.alert("msg-container",jsonData.message,'error');
							}
						
					});
		},		

		fetchCampaigns:function(url,client_id){
				$.post(url,{ 'show' : 'all', 'client_id' : client_id},function(response){}, 'json');
		},
		fetchProperties:function(item,url){
				$.get(url,null,function(properties){

					if(properties){
						var client_account_id = jQuery(item).val();
						var propertiesSelect  = jQuery("#properties-"+client_account_id);
						propertiesSelect.append("<option value=''> Select Profile</option>");
						$.each( properties, function( index, value ) {
							propertiesSelect.append("<option value='" + value.web_property_id + ":" + value.profile_id  +"'>" + value.name + " (" + value.web_property_id  + ") " + "</option>");
						});
						
					}
						
				}, 'json');
		}
		, 
	 	handleFileSelect:function (evt,el) {
			var files = evt.target.files; // FileList object
			// Loop through the FileList and render image files as thumbnails.
			for (var i = 0, f; f = files[i]; i++) {
			  
			  // Only process image files.
			  if (!f.type.match('image.*')) 
				continue;
			  		
			  var reader = new FileReader();
			  // Closure to capture the file information.
			  reader.onload = (function(theFile) {
				return function(e) {
				  // Render thumbnail.
				  var div = jQuery('#'+el);
				  div.html('<img  class="thumb" src="' + e.target.result + '" title="'+ escape(theFile.name)+ '"/>');
				};
			  })(f);
		
			  // Read in the image file as a data URL.
			  reader.readAsDataURL(f);
			}
	  }
		
}


 

function simple_toolTip(target_items, name){
	jQuery("."+name).remove();
	jQuery(target_items).each(function(i){
		jQuery("body").append("<div class='"+name+"' id='"+name+i+"'><p>"+jQuery(this).attr('title')+"</p></div>");
		var my_tooltip = $("#"+name+i);
		

		jQuery(this).removeAttr("title").mouseover(function(){
				my_tooltip.css({opacity:0.8, display:"none"}).fadeIn(100);
		}).mousemove(function(kmouse){
				my_tooltip.css({left:kmouse.pageX-30, top:kmouse.pageY-50});
		}).mouseout(function(){
				my_tooltip.fadeOut(100);
		}).click(function(){
				my_tooltip.fadeOut(100);
		});
	});
}



jQuery(document).ready(function(){
	JimmyJS._init();


})


// JavaScript Document

