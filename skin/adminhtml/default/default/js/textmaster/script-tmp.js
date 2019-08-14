/**
 * Copyright (c) 2014 Textmaster
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* It is available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @category    Addonline
* @package     Addonline_Textmaster
* @copyright   Copyright (c) 2014 Textmaster
* @author 	    Addonline (http://www.addonline.fr)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
var stop_ajax = false;
var stoped_ajax = false;
var ajax_request = false;

window.onload = function() {
	if ( (typeof jQuery === 'undefined') && !window.jQuery ) {
		//alert('TOTO');
		var head = document.getElementsByTagName('head')[0];
		url = 'http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js';
		var headTag = document.getElementsByTagName("head")[0];
	    
		var jqTag = document.createElement('script');
	    jqTag.type = 'text/javascript';
	    jqTag.src = url;
	    jqTag.onload = url;
	    jqTag.onload = function(){	    	
		    jQuery.noConflict();
		    jQueryOnLoad(jQuery);
		    if(typeof jQueryCall != 'undefined'){
		    	
		    	jQueryCall(jQuery);
		    }
	    };
	    headTag.appendChild(jqTag);
	    
	} else {
		jQuery.noConflict();
		jQueryOnLoad(jQuery);
		if(typeof jQueryCall != 'undefined'){	    	
	    	jQueryCall(jQuery);
	    }
	}
}
jQueryOnLoad = function($){
	if($('#config_edit_form').length) {
		$('#login,#password' ).keypress(function( event ) {
			if ( event.which == 13 ) {
				loginAjaxAction();
			}									
		});
		
		var current_label_translation = $('label[for=textmaster_defaultvalue_briefing_message_translation]').html();
		var changeStoreDefault_translation = function(){
          current_locale = $('#textmaster_defaultvalue_default_language').val();
          all_textarea = $('textarea[id^=briefing_message_translation]').hide();
          all_textarea.hide(0);
          $('#briefing_message_translation_'+current_locale).show();
          label_html = current_label_translation+' ('+$('#briefing_message_translation_'+current_locale).attr('data-label')+')';
          $('label[for=textmaster_defaultvalue_briefing_message_translation]').html(label_html);
        };
        
        var current_label_proofreading = $('label[for=textmaster_defaultvalue_briefing_message_proofreading]').html();
		var changeStoreDefault_proofreading = function(){
			current_locale = $('#textmaster_defaultvalue_default_language').val();
          all_textarea = $('textarea[id^=briefing_message_proofreading]');
          all_textarea.hide(0);
          $('#briefing_message_proofreading_'+current_locale).show();
          label_html = current_label_proofreading+' ('+$('#briefing_message_proofreading_'+current_locale).attr('data-label')+')';
          $('label[for=textmaster_defaultvalue_briefing_message_proofreading]').html(label_html);
        };
        
        
        changeStoreDefault = function(){
        	changeStoreDefault_proofreading();
        	changeStoreDefault_translation();
        }
        $('#textmaster_defaultvalue_default_language').change(changeStoreDefault);        
        changeStoreDefault();
        var changeType = function (){
        	type = $('#textmaster_defaultvalue_default_type').val();
        	if(type=='translate') type = 'translation';
        	$('tr[id^=row_textmaster_defaultvalue_briefing_message]').hide();
        	$('#row_textmaster_defaultvalue_briefing_message_'+type).show();
        	
        }
        $('#textmaster_defaultvalue_default_type').change(changeType);
        changeType();
        
        if(!textmaster_is_log) {
        	$('#textmaster_defaultvalue').parent().hide();
			$('#row_textmaster_textmaster_api_key').hide();										
			$('#row_textmaster_textmaster_api_secret').hide();		
        } else {
        	$('#textmaster_defaultvalue').parent().show();
        	$('#row_textmaster_textmaster_creation').hide();
			$('#row_textmaster_textmaster_api_key').hide();
			$('#row_textmaster_textmaster_api_secret').hide();
        }
	}
	if($('#edit_form').length){
		
		
	    editForm = new varienForm('edit_form', '');
	    qualities = $('#quality option');
	    service_levels = $('#language_level option');
	    expertises = $('#expertise option');
	    priorities = $('#priority option');
        translation_memories = $('#translation_memory option');

	    label_expertises = new Array();
	    label_priorities = new Array();
		label_qualities = new Array();
		label_service_levels = new Array();
        label_translation_memories = new Array();

		$.each(service_levels,function(index,value){
			label_service_levels[index] = $(value).text();
		});
		$.each(qualities,function(index,value){
			label_qualities[index] = $(value).text();
		});
		$.each(expertises,function(index,value){
			label_expertises[index] = $(value).text();
		});
		$.each(priorities,function(index,value){
			label_priorities[index] = $(value).text();
		});
        $.each(translation_memories,function(index,value){
            label_translation_memories[index] = $(value).text();
        });

		updateDisplay = function(){
			ctype = $('#ctype').val();
			//specific_attachment = $('#specific_attachment').val();
			priority = $('#priority').val();
			quality = $('#quality').val();
			expertise = $('#expertise').val();
			service_level = $('#language_level').val();
            translation_memory = $('#translation_memory').val();
			
			$('#textmaster_projet').html($('#ctype option[value='+ctype+']').html());
			
			
			if(typeof service_level =='undefined'){
				service_level = 'regular';				
			}
			if(ctype=='translation' && (service_level=='premium' || service_level=='enterprise')){
				$('#quality').removeAttr('disabled');
            } else {
                $('#quality').val(0);
                $('#quality').attr('disabled','disabled');
                quality = $('#quality').val();
            }
            if(ctype=='translation' && service_level=='enterprise'){
                $('#translation_memory').removeAttr('disabled');
            }else {
                $('#translation_memory').val(0);
                $('#translation_memory').attr('disabled','disabled');
            }
            $.each(service_levels,function(i,v){
				$(v).text(label_service_levels[i]);
			});
			
			
			html_option = $('#language_level option[value='+service_level+']').html()+'<br/>';			
			
			base_price = parseFloat(textmaster_pricing['types'][ctype][service_level]);
			service_level_price = base_price;
			
			quality_price = parseFloat(textmaster_pricing['types'][ctype]['quality']);
			if(quality=='1'){
				html_option += Translator.translate('Control Quality')+'<br/>';			
				base_price += quality_price;
			}
			priority_price = parseFloat(textmaster_pricing['types'][ctype]['priority']);
			if(priority=='1'){
				html_option += Translator.translate('Priority')+'<br/>';
				base_price += priority_price;
			}		
			expertise_price = parseFloat(textmaster_pricing['types'][ctype]['expertise']);
			if(expertise=='1'){
				html_option += Translator.translate('Expertise')+'<br/>';
				base_price += expertise_price;
			}
            translation_memory_price = parseFloat(textmaster_pricing['types'][ctype]['translation_memory']);
            if(translation_memory=='1'){
                html_option += Translator.translate('Translation memory')+'<br/>';
                base_price += translation_memory_price;
            }
			/*if(specific_attachment=='1'){
				html_option += 'Specific attachment<br/>';
				base_price += parseFloat(textmaster_pricing['types'][ctype]['specific_attachment']);
			}*/
			$('#textmaster_options').html(html_option);
			word_count = 0;
			lang = $('#store_id_origin').val();
			$('input[id^=attribute_]').each(function(index,value){				
				if($(this).is(':checked') && lang!=0){
					code = $(this).val();				
					word_count += parseInt(attribute_word_count[lang][code]);
				}			
			});
			price = Math.round(word_count*base_price*100)/100;
			priceFormat = {};
			priceFormat.requiredPrecision = 2;
			priceFormat.pattern = '%s';
			$('#textmaster_nbmot').html(word_count);
			$('#textmaster_total_price').html(formatCurrency(price,priceFormat)+' '+currency_symbol+'<br/>'+Translator.translate('worth')+' '+formatCurrency(base_price,priceFormat)+' '+currency_symbol+' / '+Translator.translate('mot'));
			
			language_from = $('#store_id_origin').val();
			language_to = $('#store_id_translation').val();
			
			$.each(service_levels,function(i,v){
				if($(v).attr('value')=='premium'){
					price = service_level_price*word_count;
					$(v).text(label_service_levels[i]);
				}
			});
			$.each(qualities,function(i,v){
				if($(v).attr('value')!='0'){
					price = quality_price*word_count;
					$(v).text(label_qualities[i]+' +'+formatCurrency(price,priceFormat)+''+currency_symbol+'');
				}
			});
			$.each(priorities,function(i,v){
				if($(v).attr('value')!='0'){
					price = priority_price*word_count;
					$(v).text(label_priorities[i]+' +'+formatCurrency(price,priceFormat)+''+currency_symbol+'');
				}
			});
			$.each(expertises,function(i,v){
				if($(v).attr('value')!='0'){
					price = expertise_price*word_count;
					$(v).text(label_expertises[i]+' +'+formatCurrency(price,priceFormat)+''+currency_symbol+'');
				}
			});
            $.each(translation_memories,function(i,v){
                if($(v).attr('value')!='0'){
                    price = translation_memory_price*word_count;
                    $(v).text(label_translation_memories[i]+' +'+formatCurrency(price,priceFormat)+''+currency_symbol+'');
                }
            });
			/*dataAuthor = {
				ctype 				: ctype,
				word_count 			: word_count,
				language_from		: language_from,
				language_to			: language_to,
				language_level		: service_level,
				quality				: quality,
				priority			: priority,
				expertise			: expertise,
				specific_attachment	: specific_attachment,
				form_key : $('input[name=form_key]').val()
			};
			$.ajax({
				url : authorurl,
				method:'post',
				type:'post',
				dataType : 'json',
				data:dataAuthor,
				success: function(data){
					if(typeof data.authors != 'undefined'){
						$("#mytextmaster option").remove();
						for(i=0;i<data.authors.length;i++){
							option = '<option value="'+data.authors[i].id+'">'+data.authors[i].ident+'</option>';
							$("#mytextmaster").append(option);
						}
					}
				}
				
			});*/
			
		}
		
		//updateDisplay();
		
		$('input[id^=attribute_],#ctype,#specific_attachment,#priority,#quality,#expertise,#language_level,#store_id_translation').change(function(){
			updateDisplay();
		});
		store_id = $('#store_id_origin').val();
		var old_locale = store_langue_correspondance[store_id];		
		var old_type = $('#ctype').val();		
		$('#ctype,#store_id_origin').change(function(){
			store_id = $('#store_id_origin').val();
			locale = store_langue_correspondance[store_id];
			if(old_type=='translation'){
				briefing_translation[old_locale] = $('#project_briefing').val();				
			} else {
				briefing_proofreading[old_locale] = $('#project_briefing').val();
			}
			if($('#ctype').val()=='translation') {				
				$('#project_briefing').val(briefing_translation[locale]);
				
			} else {
				$('#project_briefing').val(briefing_proofreading[locale]);				
			}
			old_locale = locale;
			old_type = $('#ctype').val();
		});
		
		$('#store_id_origin').change(function(){
			lang = $(this).val();
			$('#store_id_translation option').removeAttr('disabled');
			if(lang!=0){
				$('#store_id_translation option[value='+lang+']').attr('disabled','disabled')
				$.each(attribute_word_count[$(this).val()],function(index,value){
					$('label[for=attribute_'+index+'] span.tprice').text(value);
				});
				val = $('#store_id_translation').val();
				if(lang==val){
					$('#store_id_translation').val(0);
					$('#store_id_translation option').removeAttr('selected');
				}				
			}
			updateDisplay();
		});
		
		
		$('#all-attributes').click(function(e){
			e.preventDefault();
			$('input[id^=attribute_]').attr('checked','checked');
			updateDisplay();
		});
		$('#notall-attributes').click(function(e){
			e.preventDefault();
			$('input[id^=attribute_]').removeAttr('checked');
			updateDisplay();
		});
		$('#store_id_origin').change();
		
	}
	if($('#summary_form').length){
		editForm = new varienForm('summary_form', '');
	}
	if(typeof must_display_loader !='undefined' && must_display_loader){
		$('#loading-mask').show();
		jQuery('body > .wrapper > .header').css({position:'relative',zIndex:600});
		html = pourcent_avance+'%<span class="progress-cadre"><span class="progress" style="width:'+pourcent_avance+'%"></span></span>';
		html += '<span class="message">'+nouveau_message_loader+'</span>';
		jQuery('#loading_mask_loader_message').html(html);
		jQuery('body > .wrapper > .header a').click(function(e){
			if(jQuery(this).attr('href')!='') {
				e.preventDefault();
				if(typeof ajax_request.transport!='undefined')
					ajax_request.transport.abort();
				window.location = jQuery(this).attr('href');				
			}
			
		});
		documentSend(textmasterurl_count);
	}
	$('button[onclick*="massactionJsObject"]').click(function(e){
		//e.preventDefault();
		checked = false;
		$('input[name=document_id]:checked').each(function() {
			checked = true;
		});
		if(checked) {
			html = '0%'+'<span class="progress-cadre"><span class="progress" style="width:0%"></span></span>';
			html += '<span class="message">'+message_completed_document+'</span>';
			$('#loading_mask_loader_message').html(html);
			$('#loading-mask').show();
		}
	});
	
};

function valideStep2() {
	
	if(editForm.validator && editForm.validator.validate()){
	
	html = '0%'+'<span class="progress-cadre"><span class="progress" style="width:0%"></span></span>';
	html += '<span class="message">'+nouveau_message_loader+'</span>';
	$('loading_mask_loader_message').update(html);
	$('loading-mask').show();
	
	jQuery('body > .wrapper > .header').css({position:'relative',zIndex:600});
	jQuery('body > .wrapper > .header a').click(function(e){
		if(jQuery(this).attr('href')!='') {
			e.preventDefault();
			if(typeof ajax_request.transport!='undefined')
				ajax_request.transport.abort();
			window.location = jQuery(this).attr('href');
			
		}
	});
	html = '0%'+'<span class="progress-cadre"><span class="progress" style="width:0%"></span></span>';
	html += '<span class="message">'+nouveau_message_loader+'</span>';
	
	
	new Ajax.Request(
			$('edit_form').action,
			{
				method: 'post',
				parameters: Form.serialize("edit_form"),  
				onSuccess: function(oXHR) {
					json = oXHR.responseText.evalJSON();
					if(typeof json.counturl != 'undefined'){						
						counturl = json.counturl;
						documentSend(counturl);
						
					} else if(typeof json.url != 'undefined'){ 
						window.location = json.url;
						$('loading-mask').hide();
					} else {
						alert('erreur de creation du projet');
						$('loading-mask').hide();
					}
				}
			}
		);
	
	}
}
function documentSend (url){
	ajax_request = new Ajax.Request(
			url,
			{
				method: 'get',
				onSuccess: function(oXHR) {
					if(oXHR.responseText.substr(0,4) != 'http'){
                        if(oXHR.responseText == 'in_progress'){
    						html = '100% <span class="progress-cadre"><span class="progress" style="width:100%"></span></span>';
    						html += '<span class="message">'+message_loader_tm+'</span>';
                        }else{
                            html = oXHR.responseText+'%'+'<span class="progress-cadre"><span class="progress" style="width:'+oXHR.responseText+'%"></span></span>';
                            html += '<span class="message">'+nouveau_message_loader+'</span>';
                        }
						$('loading_mask_loader_message').update(html);
						if(!stop_ajax)
							documentSend(url);
						else
							stoped_ajax = true;
					} else {
						$('loading-mask').hide();
						window.location = oXHR.responseText;
					}
				}
			}
	);
}

function showAuthors(url,title) {
    winCompare = new Window({url:url,title:title,width:680,minimizable:false,maximizable:false,showEffectOptions:{duration:0.4},hideEffectOptions:{duration:0.4}});
    winCompare.setZIndex(100);
    winCompare.showCenter(true);
 
}
function completeDocuments(grid,massactiongrid,transport) {
	html = '0%'+'<span class="progress-cadre"><span class="progress" style="width:0%"></span></span>';
	html += '<span class="message">'+message_completed_document+'</span>';
	$('loading_mask_loader_message').update(html);
	$('loading-mask').show();
	url = transport.responseJSON.url;
	jQuery('body > .wrapper > .header').css({position:'relative',zIndex:600});
	jQuery('body > .wrapper > .header a').click(function(e){
		if(jQuery(this).attr('href')!='') {
			e.preventDefault();
			if(typeof ajax_request.transport!='undefined')
				ajax_request.transport.abort();
			window.location = jQuery(this).attr('href');
			
		}
	});
	iscomplete(url);
	
	return;	
}
function completeDocument(url,id) {
	html = '0%'+'<span class="progress-cadre"><span class="progress" style="width:0%"></span></span>';
	html += '<span class="message">'+message_completed_document+'</span>';
	$('loading_mask_loader_message').update(html);
	$('loading-mask').show();
	jQuery('body > .wrapper > .header').css({position:'relative',zIndex:600});
	jQuery('body > .wrapper > .header a').click(function(e){
		if(jQuery(this).attr('href')!='') {
			e.preventDefault();
			if(typeof ajax_request.transport!='undefined')
				ajax_request.transport.abort();
			window.location = jQuery(this).attr('href');
			
		}
	});
	new Ajax.Request(
			url,
			{
				method: 'get',
				onSuccess:function(oXHR) {
					json = oXHR.responseText.evalJSON();
					if(typeof json.counturl != 'undefined'){						
						counturl = json.counturl;
						iscomplete(counturl);
						
					} else if(typeof json.url != 'undefined'){ 
						window.location = json.url;
						
						$('loading-mask').hide();
					} else {
						alert('erreur lors de la validation du document');
						$('loading-mask').hide();
					}
				}
			}
		);
}
function iscomplete (url){
	new Ajax.Request(
			url,
			{
				method: 'get',
				onSuccess:function(oXHR) {
					if(oXHR.responseText.substr(0,4) != 'http'){
						html = oXHR.responseText+'%'+'<span class="progress-cadre"><span class="progress" style="width:'+oXHR.responseText+'%"></span></span>';
						html += '<span class="message">'+message_completed_document+'</span>';
						$('loading_mask_loader_message').update(html);
						if(!stop_ajax)
							iscomplete(url);
						else
							stoped_ajax = true;
					} else {
						html = '100%'+'<span class="progress-cadre"><span class="progress" style="width:100%"></span></span>';
						html += '<span class="message">'+message_completed_document+'</span>';
						$('loading_mask_loader_message').update(html);
						$('loading-mask').hide();
						window.location = window.location;
					}
				}
			}
		);
}