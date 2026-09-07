$(function() {
	// the widget definition, where "itop" is the namespace,
	// "calendar_handler" the widget name
	$.widget("itop.stopwatch_handler",
		{
			options:
				{
					endpoints: {},
					activity_id: 0,
					timespent_id: 0,
					start_date: 0,
					local_start_date : 0,
					configuration: 'none',
					current_object_friendlyname : '',
					object_friendlyname : '',
					object_class: '',
					object_id: 0,
				},

			_create: function () {
			var me = this;
			this.element.find('.stopwatch-bookmark').on('click', {me:me}, function(){
				if($(this).hasClass('stopwatch-bookmark-show'))
				{
					$(this).removeClass('stopwatch-bookmark-show').addClass('stopwatch-bookmark-hide');
					me.element.addClass('stopwatch-view-show').removeClass('stopwatch-view-hidden');
				}
				else if($(this).hasClass('stopwatch-bookmark-hide'))
				{
					$(this).removeClass('stopwatch-bookmark-hide').addClass('stopwatch-bookmark-show');
					me.element.addClass('stopwatch-view-hidden').removeClass('stopwatch-view-show');
				}
			});
			$(document).mouseup(function (e)
			{
				var container = $(me.element); // YOUR CONTAINER SELECTOR
				var button = me.element.find('.stopwatch-bookmark');
				if (!container.is(e.target) && container.has(e.target).length === 0) {
					if (button.hasClass('stopwatch-bookmark-hide')) {
						button.removeClass('stopwatch-bookmark-hide').addClass('stopwatch-bookmark-show');
						me.element.addClass('stopwatch-view-hidden').removeClass('stopwatch-view-show');
					}
				}
			});
			switch (this.options.configuration) {
				case 'onactiveobject':
					this._OnActiveObject();
					break;
				case 'onobject_stopwatchoff':
					this._OnObjectStopwatchOff();
					break;
				case 'onpage_stopwatchon':
					this._OnActiveObject();
					break;
				case 'onobject_stopwatchon':
					this._OnObjectStopwatchOn();
					break;
			}
				this.element.show(700);
			},
			_updateTime: function () {
				var time = (moment(this.options.start_date, "YYYY/MM/DD HH:mm:ss").isBefore(moment()) ? moment(this.options.start_date, "YYYY/MM/DD HH:mm:ss") : moment(this.options.local_start_date, "YYYY/MM/DD HH:mm:ss"));
				var ms = moment().diff(time);
				var d = moment.duration(ms);
				var s = Math.floor(d.asHours()) + moment.utc(d.asMilliseconds()).format(":mm:ss");
				this.element.find('.stopwatch-time').text(s);
			},
			_getHistory: function(){
				var me = this;
				var oParams = {
					'operation' : 'get_history_stopwatch',
					'object_class' : me.options.object_class,
					'object_id' : me.options.object_id,
				};
				$.post(me.options.endpoints.timespentbackground, oParams, function(oResult) {
					if (oResult.status == 'failed') {
					}
					else if (oResult.status == 'ok'){
						var oHistoryDiv = me.element.find('.stopwatch-history');
						oHistoryDiv.empty();
						var even = false;
						var precdate = 0;
						oResult.history.forEach(function (e) {
							if(!moment.utc(e.start_date).isSame(moment.utc(precdate), 'day'))
							{
								var oNewDate;
								if(moment.utc(e.start_date).isSame(moment(), 'week'))
								{
									oNewDate = $('<div>').addClass('stopwatch-date-line').html(moment.utc(e.start_date).fromNow());
								}
								else
								{
									oNewDate = $('<div>').addClass('stopwatch-date-line').html(moment.utc(e.start_date).format("MMMM Do"));
								}
								oHistoryDiv.append(oNewDate);
							}
							var oLineDiv = $('<div>');
							if(even)
							{
								oLineDiv.addClass('stopwatch-history-even');
							}
							var oContactDiv= $('<div class="tt_stopwatch_history_contact" title="'+e.contact_name+'">');
							if (e.contact_picture !== undefined)
							{
								oContactDiv.css('background-image', 'url('+e.contact_picture+')')
							}
							else{
								oContactDiv.text(e.contact_name.substr(0,1));
							}
							var sEllapsedTime = moment.duration(e.end_date - e.start_date);
							sEllapsedTime = Math.floor(sEllapsedTime.asHours()) + moment.utc(sEllapsedTime.asMilliseconds()).format(":mm:ss");
							var oTimeDiv= $('<div class="tt_stopwatch_history_time">').text(sEllapsedTime).attr('title', moment(e.start_date).format('LLL'));
							var oOriginDiv= $('<div class="tt_stopwatch_history_origin tt_stopwatch_history_origin_'+e.origin + '">').attr('title', Dict.S('TimeTracking::TrackedFrom:'+e.origin));
							oLineDiv.append(oContactDiv,oTimeDiv,oOriginDiv);
							oHistoryDiv.append(oLineDiv);
							even = !even;
							precdate = e.start_date;
						});
					}
				});
			},
			_OnActiveObject: function ()
			{
				var me = this;
				this.element.find('.stopwatch-bookmark').addClass('stopwatch-bookmark-on');
				me._getHistory();
				me.options.local_start_date = moment().format("YYYY/MM/DD HH:mm:ss");
				var interval = setInterval(function(){me._updateTime()}, 1000);
				me.element.find('.stopwatch-msg').empty().html(Dict.S('TimeTracking:TrackingTimeOn') +' <br/> <span class="stopwatch-friendlyname">' + me.options.object_friendlyname + '</span>');
				this.element.find('.stopwatch-button-bar .play').hide();
				this.element.find('.stopwatch-button-bar .stop').off('click').on('click', {me:me}, function(e){
					var oParams = {
						'operation': 'stop_stopwatch',
						'timespent_id': me.options.timespent_id,
					};
					$.post(me.options.endpoints.timespentbackground, oParams, function(oResult) {
						me.element.find('.stopwatch-bookmark').removeClass('stopwatch-bookmark-on');
						clearInterval(interval);
						if (oResult.status == 'failed') {
							alert('Failed to stop active stopwatch');
						}
						else if (oResult.status == 'ok'){
							me.options.timespent_id = 0;
							me.options.start_date = 0;
							me.options.local_start_date = 0;
							me.element.find('.stopwatch-time').hide();
							me.element.find('.stopwatch-button-bar .play').show();
							me._OnObjectStopwatchOff();
						}
					});
				});
				this.element.find('.stopwatch-button-bar .reset').off('click').on('click', {me:me}, function(e){
					var oParams = {
						'operation' : 'reset_stopwatch',
						'timespent_id' : me.options.timespent_id,
					};
					$.post(me.options.endpoints.timespentbackground, oParams, function(oResult) {
						if (oResult.status == 'failed') {
							alert('Failed to reset active stopwatch');
						}
						else if (oResult.status == 'ok'){
							me.options.timespent_id = 0;
							me.options.start_date = 0;
							me.options.local_start_date = 0;
							me.element.find('.stopwatch-time').hide();
							me.element.find('.stopwatch-button-bar .play').show();
							me._OnObjectStopwatchOff();						}
					});
				});
			},
			_OnObjectStopwatchOff: function ()
			{
				var me = this;
				me.element.find('.stopwatch-msg').empty().html(Dict.S('TimeTracking:PressButtonToStartTrackingOn')+ '<br/><span class="stopwatch-friendlyname">' + me.options.object_friendlyname + '</span>');
				me.element.find('.stopwatch-time').hide();
				me.element.find('.stopwatch-button-bar .stop').hide();
				me.element.find('.stopwatch-button-bar .reset').hide();
				me._getHistory();
				this.element.find('.stopwatch-button-bar .play').off('click').on('click', {me:me}, function(e){
					var oParams = {
						'operation': 'start_stopwatch',
						'object_class': me.options.object_class,
						'object_id': me.options.object_id,
					};
					$.post(me.options.endpoints.timespentbackground, oParams, function(oResult) {
						if (oResult.status == 'failed') {
							alert('Failed to start stopwatch');
						}
						else if (oResult.status == 'ok'){
							me.options.timespent_id = oResult.new_timspent_background.timespent_id;
							me.options.start_date =  oResult.new_timspent_background.start;
							me.options.local_start_date = moment().format("YYYY/MM/DD HH:mm:ss");
							me.element.find('.stopwatch-time').show();
							me.element.find('.stopwatch-button-bar .stop').show();
							me.element.find('.stopwatch-button-bar .reset').show();
							me._OnActiveObject();
						}
					});
				});
			},
			_OnObjectStopwatchOn: function ()
			{
				var me = this;
				me.element.find('.stopwatch-history').hide();
				me._OnActiveObject();
				me.element.find('.stopwatch-msg').empty().html(Dict.S('TimeTracking:TrackingTimeOnDiffObj') + '<br/> <span class="stopwatch-friendlyname">' + me.options.current_object_friendlyname + '</span>').addClass('stopwatch-msg-alert');

				this.element.find('.stopwatch-button-bar .stop').on('click', {me:me}, function(e){
					me.element.find('.stopwatch-msg').removeClass('stopwatch-msg-alert');
					me.element.find('.stopwatch-history').show();
				});
				this.element.find('.stopwatch-button-bar .reset').on('click', {me:me}, function(e){
					me.element.find('.stopwatch-msg').removeClass('stopwatch-msg-alert');
					me.element.find('.stopwatch-history').show();
				});
				},
		}
	);
});
