$(function()
{
	// the widget definition, where "itop" is the namespace,
	// "calendar_handler" the widget name
	$.widget( "itop.timetracking_handler",
		{
			options:
				{
					is_legacy: false,
					endpoints: {},
					id: '',
					params: {},
					mode: '',
					calendar_options: {
					},
					activity_mode: 'modal',
					like_button_off: 'far fa-heart',
					like_button_on: 'fas fa-heart',
				},
			activity_picker: null,
			_create: function()
			{
				var me = this;
				this.element.addClass('timetracking_handler');
				$.extend(true, this.options.calendar_options, {
					events: me._getEventsFunction(),
					eventResize: function(event, delta, revertFunc) {
						var oParams = {};
						oParams.event = event;
						me._updateActivity(event, oParams, revertFunc);
					},
					eventDrop: function(event, delta, revertFunc) {
						var oParams = {};
						oParams.event = event;
						me._updateActivity(event, oParams, revertFunc);
					},
					eventAfterRender: function ( event, element, view )
					{
						if (view.name === 'listDay') {
							if(me.options.clone_events) {
								element.find('.fc-list-item-time').append('<i class="tt-clone-button fa fa-clone" id="tt_'+event._id+'_clone_btn" aria-hidden="true"></i>');
							}
							element.find('.fc-list-item-time').append('<i class="tt-remove-button fa fa-times" id="tt_'+event._id+'_delete_btn"  aria-hidden="true"></i>');
						}
						else {
							element.find('.fc-content').prepend('<i class="tt-remove-button fa fa-times" id="tt_'+event._id+'_delete_btn" aria-hidden="true"></i>');
							if(me.options.clone_events) {
								element.find('.fc-content').prepend('<i class="tt-clone-button fa fa-clone" id="tt_'+event._id+'_clone_btn" aria-hidden="true"></i>');
							}
							if(event.description)
							{
								element.find('.fc-content').append(event.description.replace(/(?:\r\n|\r|\n)/g, '<br>'));
							}
						}
						element.find('#tt_'+event._id+'_delete_btn').on('click', function(e) {
							e.stopPropagation();
							var oParams = {};
							oParams.operation = 'delete';
							oParams.id = event.id;
							$.post(me.options.endpoints.events, oParams, function(oResult) {
								if (oResult.status == 'failed') {
									alert(oResult.message);
								}
								else if (oResult.status == 'ok'){
									me.element.find('.timetracking-content').fullCalendar('removeEvents', event._id);
								}
							});
						});

						if(me.options.clone_events) {
							element.find('#tt_'+event._id+'_clone_btn').on('click', function (e) {
								e.stopPropagation();
								me._cloneEvent(me, event);
							});
						}

						var sContactIconClass = (me.options.is_legacy === true) ? "fa fa-user" : "fas fa-user"
						var sDurationIconClass = (me.options.is_legacy === true) ? "fa fa-clock-o" : "fas fa-clock"
						var sQTipContent = '<div class="tt-qtip"><table>'
							+ '<tr>'
							+ '<td><i class="'+sContactIconClass+'" aria-hidden="true"></i></td>'
							+ '<td>' + event.contact + '</td>'
							+ '</tr>'
							+ '<tr>'
							+ '<td><i class="'+sDurationIconClass+'" aria-hidden="true"></i></td>'
							+ '<td>' + event.duration + '</td>'
							+ '</tr>'
							+ '</table></div>';
						
						if(me.options.is_legacy){
							element.qtip({
								content: sQTipContent,
								position: { corner: { target: 'topMiddle', tooltip: 'bottomMiddle'}},
								style: { tip: 'bottomMiddle', name: 'light' },
								show: { solo: true },
								hide: { when: 'mouseout', fixed: true }
							});
						}
						else{
							sQTipContent = $(sQTipContent).html();
							element.attr('data-tooltip-html-enabled', true);
							element.attr('data-tooltip-content', sQTipContent);
							CombodoTooltip.InitTooltipFromMarkup(element);
						}
					},
					select: function(start, end)
					{
						var oParams = {};
						oParams.operation = 'create';
						if (me.options.mode === 'User')
						{
							var oNewActivity = function(){ return me.activity_picker.combobox_activity('GetValues');};
							
							// When trying to add a timespent with no activity selected in classic mode, prompt user with modal mode as backup
							var bForcedModal = false;

							if(me.options.activity_mode === 'classic' && oNewActivity().object_id === 0 && oNewActivity().object_class === ''){
								me.options.activity_mode = 'modal';
								me._updateActivityMode();
								bForcedModal = true;
							}
							
							// When modal mode has been forced, reset to classic mode after modal has been closed
							var resetActivityMode = function(){
								if(bForcedModal){
									me.options.activity_mode = 'classic';
									me._updateActivityMode();
								}
							};
							
							if (me.options.activity_mode === 'modal')
							{
								$('#timetracking_view').prop('disabled', true);
								$(".tt_activity_modal").show();
								$('.tt_activity_modal_close').off('click').on('click',function(e){
									$(".tt_activity_modal").hide();
									$('#timetracking_view').prop('disabled', false);
									resetActivityMode();
								});
								$('.tt_activity_modal button').off('click').on('click', {me: me}, function(e){
									$.extend(
										oParams,
										oNewActivity()
									);
									$('#timetracking_view').prop('disabled', false);
									e.data.me._newEvent(me, oParams, start, end);
									$(".tt_activity_modal").hide();
									resetActivityMode();
								});
							}
							else if (me.options.activity_mode === 'classic')
							{
								$.extend(
									oParams,
									oNewActivity()
								);
								me._newEvent(me, oParams, start, end);
							}
						}
						else if(me.options.mode === 'Object')
						{
							var oNewActivity = function() { return me.activity_picker.val() };
							var oObjectDetails = oNewActivity().split(',');
							oParams.object_class = oObjectDetails[0];
							oParams.object_id = oObjectDetails[1];
							me._newEvent(me, oParams, start, end);
						}

					},
					eventClick: function(event, jsEvent, view)
					{
						me._displayUpdateDialog(me, event);
					},
					loading: function(bIsLoading, oView){
						if(bIsLoading)
						{
							me._showLoader();
						}
						else
						{
							me._hideLoader();
						}
					},
				});
				this.element.find('.timetracking-content').fullCalendar(this.options.calendar_options);
				this._addLoader();
				// activity picker
				if(this.options.mode === 'User')
				{
					me._initializeActivityMode();
					this.activity_picker = $(".get_tt_activity_combobox").combobox_activity({ endpoint: me.options.endpoints.activities, like_button_off: me.options.like_button_off,
						like_button_on: me.options.like_button_on });
					$(".tt_user_color_activity").on('click', function () {
								var oSelectedActivity = me.activity_picker.combobox_activity('GetValues');
								var oInputId = oSelectedActivity.object_id;
								var oInputClass = oSelectedActivity.object_class;


								var oIconBG = $('<i class="fa fa-paint-brush" aria-hidden="true"></i>');
								var oColorBG =  $('<input id="tt-background-color-picker" type="color">');
								var oIconText = $('<i class="fa fa-font" aria-hidden="true"></i>');
								var oColorText =  $('<input id="tt-text-color-picker" type="color">');
								var oValidButton =  $('<button id="tt-submit-add-activity">' + Dict.S('TimeTracking:SetColor') + '</button>');

								this.dlg = $('<div class="dialog">').append(oIconBG, oColorBG,oIconText, oColorText, oValidButton);
								var dlg = this.dlg;
								var modifyDialog = this.dlg;
								this.dlg.dialog({
									width: 'auto',
									height: 'auto',
									title: Dict.S('TimeTracking:SelectColorForActivity'),
									modal: true,
									close: function () {
										dlg.find("form").remove();
										dlg.dialog('destroy');
									},
								});
								
								//polyfills input[type=color] into spectrum widget
								$.fn.spectrum.load = true;
								$.fn.spectrum.processNativeColorInputs();
								oValidButton.on('click',function(e) {
									$.ajax({
										type: "POST",
										dataType: 'json',
										url: me.options.endpoints.activities,
										data: {
											operation: 'user_color_activity',
											object_class: oInputClass,
											object_id: oInputId,
											background_color: oColorBG.val(),
											text_color: oColorText.val(),
										},
										success: function (oResult) {
											if (oResult.status === 'failed') {
												alert(oResult.message);
											} 
											else if (oResult.status === 'ok') {
												me.activity_picker.combobox_activity('Refetch');
												me.element.find('.timetracking-content').fullCalendar('refetchEvents');
											}
											modifyDialog.dialog('close');
										}
									});
								});
					});
				}
				else if (this.options.mode === 'Object')
				{
					this.activity_picker = $(".get_tt_activity");
					this.element.parents('.ui-tabs').on('tabsactivate', {me:me}, function(event, ui) {
							me.element.find('.timetracking-content').fullCalendar('rerenderEvents');
							me.element.find('.timetracking-content').fullCalendar('render');
					});
				}

			},

			_displayUpdateDialog: function(me, event)
			{
				$('#timetracking_view').prop('disabeld', true);
				var oParams = {};
				oParams.operation = 'modify_event_form';
				oParams.event_id = event.id;
				$.post(me.options.endpoints.events, oParams, function(oResult) {
					this.dlg = $('<div class="dialog">'+oResult+'</div>');
					var modifyDialog = this.dlg;
					
					// new setIntervals to catch dialogs setIntervals id range
					var iEndIntervalId = 0;
					var iStartIntervalId = window.setInterval(function(){}, 60000);
					
					this.dlg.dialog({
						width: 'auto',
						height: 'auto',
						title: event.title,
						modal: true,
						close: function () {
							// Re-enable calendar widget
							$('#timetracking_view').prop('disabled', false);
							
							// Destroy setInterval added by the modify form
							for(var i = iStartIntervalId; i <= iEndIntervalId; ++i)
							{
								window.clearInterval(i);
							}

							// Removing object form listener on window.unload
							$(window).off('unload');

							// Removing object form action on page unloading (mainly "data may be lost" alert)
							window.onbeforeunload = function(){};
							
							// Releasing object lock
							$.post(me.options.endpoints.ui, {operation: 'kill_lock', class: 'TimeSpent', id: event.id});

							modifyDialog.find("form").remove();
							modifyDialog.dialog('destroy');
						},
					});
					
					// Set an setInterval to know last dialog setInterval id
					iEndIntervalId = window.setInterval(function(){}, 60000);
					
					// Add a button to delete events in a modify form 
					modifyDialog.find('.cancel[type=button]').after('<button type="button" class="action delete-timespent"><span>' + Dict.S('UI:Button:Delete')+'</span></button>');

					modifyDialog.find('.delete-timespent[type=button]').addClass('ibo-button ibo-is-alternative ibo-is-danger');
					modifyDialog.find('.delete-timespent[type=button]').on('click', function(e) {
						e.stopPropagation();
						if(confirm(Dict.Format('UI:Delect:Confirm_Object', Dict.S('Class:TimeSpent') + ': ' + event.title)))
						{
							var oParams = {};
							oParams.operation = 'delete';
							oParams.id = event.id;
							$.post(me.options.endpoints.events, oParams, function(oResult) {
								if (oResult.status == 'failed') {
									alert(oResult.message);
								}
								else if (oResult.status == 'ok'){
									me.element.find('.timetracking-content').fullCalendar('refetchEvents');
									modifyDialog.dialog('close');
								}
							});
						}
					});
					
					// On cancel button, just close the modal
					modifyDialog.find('.cancel[type=button]').off('click').on('click',function(e)
					{
						modifyDialog.dialog('close');
						e.stopImmediatePropagation();
					});
					
					// Remove current submit form button to add our own (and skip this hardcoded whitespace in form markup 🙈
					modifyDialog.find('.action[type=submit]').remove();
					modifyDialog.find('.delete-timespent[type=button]').after('<button type="submit" class="action ibo-button ibo-is-regular ibo-is-primary"><span>' + Dict.S('UI:Button:Apply')+'</span></button>');
					modifyDialog.find('form').on('submit',function(event){
							/* stop form from submitting normally */
							event.preventDefault();
							/* get the action attribute from the <form action=""> element */
							var $form = $(this);

							if(CheckFields($form.attr('id'), false))
							{
								var url = $form.attr('action');
								var posting = $.post( url, $form.serialize());

								/* Alerts the results */
								posting.done(function( data ) {
									me.element.find('.timetracking-content').fullCalendar('refetchEvents');
									modifyDialog.dialog('close');
								});
							}
					});
				});
			},

			_updateActivity: function(event, oParams, revertFunc)
			{
				var me = this;
				oParams.operation = 'update_event';
				//oParams.event.start = oParams.event.start.valueOf();
				//oParams.event.end = oParams.event.end.valueOf();
				$.ajax({
					type: "GET",
					url: me.options.endpoints.events,
					data: {
						start: oParams.event.start.unix(),
						end: oParams.event.end.unix(),
						id: oParams.event.id,
						description: oParams.event.description,
						title: oParams.event.title,
						operation: oParams.operation,
					},
					success: function(oResult){
						if(oResult.status === 'failed')
						{
							alert(oResult.message);
							revertFunc();
						}
						else if (oResult.status === 'ok')
						{
							event.title = oResult.event.title;
							event.start= moment(oResult.event.start);
							event.color= oResult.event.color;
							event.textColor= oResult.event.textColor;
							event.end = moment(oResult.event.end);
							event.description = oResult.event.description;
							event.editable = oResult.event.editable;
							event.allDay = oResult.event.allDay;
							event.id = oResult.event.timespent_id;
							event.contact = oResult.event.contact;
							event.duration = oResult.event.duration;
							
							me.element.find('.timetracking-content').fullCalendar('updateEvent', event);
						}
					},
					dataType: "json"
				});
			},
			_getEventsFunction: function()
			{
				var me = this;
				var data = {};
				if(me.options.mode === 'Object')
				{
					var $sClassAndId = $('.get_tt_activity').val();
					var $aClassAndId = $sClassAndId.split(',');
					data =
						{
							object_class: $aClassAndId[0],
							object_id: $aClassAndId[1],

						};
				}
				return function (start, end, timezone, callback) {
					// polyfill methos as IE doesn't support Object.assign (SCRIPT438)
					if (typeof Object.assign != 'function') {
						Object.assign = function(target) {
							'use strict';
							if (target == null) {
								throw new TypeError('Cannot convert undefined or null to object');
							}

							target = Object(target);
							for (var index = 1; index < arguments.length; index++) {
								var source = arguments[index];
								if (source != null) {
									for (var key in source) {
										if (Object.prototype.hasOwnProperty.call(source, key)) {
											target[key] = source[key];
										}
									}
								}
							}
							return target;
						};
					}

					$.ajax({
						url: me.options.endpoints.events ,
						dataType: 'json',
						type: 'GET',
						data: Object.assign({},{
							start: start.unix(),
							end: end.unix(),
						}, me.options.params.events),
						success: function(doc) {
							var events = [];
							if(doc.event) {
								doc.event.forEach(function (e) {
									events.push({
										title: e.title,
										start: moment(e.start),
										color: e.color,
										textColor: e.textColor,
										end: moment(e.end),
										description: e.description,
										editable: e.editable,
										allDay: e.allDay,
										id: e.timespent_id,
										contact: e.contact,
										duration: e.duration,
									});
								});
							}
							callback(events);
						},
						error: function() {

						},
					});
				};
			},
			_cloneEvent: function(me, event)
			{
				$('#timetracking_view').prop('disabeld', true);
				var oParams = {
					operation: 'clone',
					id: event.id,
					origin: 'calendar'
				};
				$.post(me.options.endpoints.events, oParams, function(oResult) {
					if(oResult.status === 'failed')
					{
						alert(oResult.message);
					}
					else if (oResult.status === 'ok')
					{
						var oCopiedEvent = oResult.new_event;
						oCopiedEvent.start = moment(oCopiedEvent.start);
						oCopiedEvent.end = moment(oCopiedEvent.end);
						oCopiedEvent.id = oCopiedEvent.timespent_id;
						me.element.find('.timetracking-content').fullCalendar('renderEvent', oCopiedEvent);
					}
				});
			},
			_newEvent: function(me, oParams, start, end)
			{
				oParams.start = start.unix();
				oParams.end = end.unix();
				oParams.origin = 'calendar';
				$.post(me.options.endpoints.events, oParams, function(oResult) {
					if(oResult.status === 'failed')
					{
						// Check if the creation has failed only because of empty but mandatory fields
						if(
							Array.isArray(oResult.message)
							&& oResult.message.length === 1
							&& oResult.message.pop().indexOf("Null not allowed") !== -1
						)
						{
							// Cannot create this object as-is, prompt the user to complete the edition
							me._displayCreationDialog(me, oParams);
						}
						else
						{
							alert(oResult.message);
						}
					}
					else if (oResult.status === 'ok')
					{
						var oNewEvent = oResult.new_event;
						oNewEvent.start = moment(oNewEvent.start);
						oNewEvent.end = moment(oNewEvent.end);
						oNewEvent.id = oNewEvent.timespent_id;
						me.element.find('.timetracking-content').fullCalendar('renderEvent', oNewEvent);
					}
				});
			},
			_initializeActivityMode: function () {
				var me = this;
				$('#timetracking-classic-mode').on('click',{me:me},function(){
					SetUserPreference('timetracking_activity_picker', 'classic', true);
					me.options.activity_mode = 'classic';
					me._updateActivityMode();
				});
				$('#timetracking-modal-mode').on('click',{me:me},function(){
					SetUserPreference('timetracking_activity_picker', 'modal', true);
					me.options.activity_mode = 'modal';
					me._updateActivityMode();
				});
			},
			_updateActivityMode: function () {
				var me = this;
				if(me.options.activity_mode === 'modal')
				{
					$('.tt_activity_modal_close_hidden').removeClass('tt_activity_modal_close_hidden').addClass('tt_activity_modal_close');
					$('.tt_activity_classic').removeClass('tt_activity_classic').addClass('tt_activity_modal');
					$('.tt_activity_modal').find('button[name=Add]').show();
					$('.tt_activity_modal').hide();

				}
				else if(me.options.activity_mode === 'classic')
				{
					$('.tt_activity_modal_close').removeClass('tt_activity_modal_close').addClass('tt_activity_modal_close_hidden');
					$('.tt_activity_modal').removeClass('tt_activity_modal').addClass('tt_activity_classic');
					$('.tt_activity_classic').find('button[name=Add]').hide();
					$('.tt_activity_classic').show();
				}
			},
			_addLoader: function()
			{
				this.element.find('.timetracking-content .fc-view-container').append('<div class="tt-loader"><span class="fa fa-fw fa-spin fa-refresh"></span></div>');
			},
			_showLoader: function()
			{
				this.element.find('.tt-loader').show();
			},
			_hideLoader: function()
			{
				this.element.find('.tt-loader').hide();
			},
			_displayCreationDialog: function(me, oParams)
			{
				oParams.operation = 'create_event_form';
				$('#timetracking_view').prop('disabeld', true);
				$.post(me.options.endpoints.events, oParams, function(oResult) {
					this.dlg = $('<div class="dialog">'+oResult+'</div>');
					var createDialog = this.dlg;
					
					// new setIntervals to catch dialogs setIntervals id range
					var iEndIntervalId = 0;
					var iStartIntervalId = window.setInterval(function(){}, 60000);
					
					this.dlg.dialog({
						width: 'auto',
						height: 'auto',
						title: Dict.S('UI:FillAllMandatoryFields'),
						modal: true,
						close: function () {
							// Re-enable calendar widget
							$('#timetracking_view').prop('disabled', false);
							
							// Destroy setInterval added by the modify form
							for(var i = iStartIntervalId; i <= iEndIntervalId; ++i)
							{
								window.clearInterval(i);
							}

							// Removing object form listener on window.unload
							$(window).off('unload');

							// Removing object form action on page unloading (mainly "data may be lost" alert)
							window.onbeforeunload = function(){};

							createDialog.find("form").remove();
							createDialog.dialog('destroy');
						},
					});
					
					// Set an setInterval to know last dialog setInterval id
					iEndIntervalId = window.setInterval(function(){}, 60000);
					
					// On cancel button, just close the modal


					// On cancel button, just close the modal
					createDialog.find('.cancel[type=button]').off('click').on('click',function(e)
					{
						createDialog.dialog('close');
						e.stopImmediatePropagation();
					});
					
					// Remove current submit form button to add our own (and skip this hardcoded whitespace in form markup 🙈
					createDialog.find('.action[type=submit]').remove();
					createDialog.find('.cancel[type=button]').after('<button type="submit" class="action ibo-button ibo-is-regular ibo-is-primary"><span>' + Dict.S('UI:Button:Apply')+'</span></button>');
					createDialog.find('form').on('submit',function(event){
							/* stop form from submitting normally */
							event.preventDefault();
							/* get the action attribute from the <form action=""> element */
							var $form = $(this);

							if(CheckFields($form.attr('id'), false))
							{
								var url = $form.attr('action');
								var posting = $.post( url, $form.serialize());

								/* Alerts the results */
								posting.done(function( data ) {
									me.element.find('.timetracking-content').fullCalendar('refetchEvents');
									createDialog.dialog('close');
								});
							}
					});
				});
			},
			});
});
