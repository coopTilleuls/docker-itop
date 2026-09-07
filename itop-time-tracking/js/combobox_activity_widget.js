
$.widget( "custom.combobox_activity", {
	options: {
		endpoint: '',
		like_button_off: 'far fa-heart',
		like_button_on: 'fas fa-heart',
	},
	activities: [],
	curr_obj_id: 0,
	curr_obj_class: '',
	curr_obj_scope: '',
	
	_create: function(endpoint) {
		$.widget( 'itop.autocompleteActivityTimeTracking', $.ui.autocomplete,
			{
				_create: function() {
					this._super();
					this.widget().menu( "option", "items", "> :not(.ui-autocomplete-category)" );
				},
				_renderItem: function( ul, item ) {
					return $("<li>")
						.append($('<div class="tt-activity-color">').css('background-color', item.color))
						.append($("<a>").text( item.label))
						.appendTo(ul);
				},
				_renderMenu: function(ul, items) 
				{
					var that = this,
						currentCategory = "";
					var categories = {};
					ul.addClass('combobox_widget_list');
					$.each(items, function (index, item) {
						if(!categories[item.category])
						{
							categories[item.category]= {};
						}
						categories[item.category][index] = item;
					});
					$.each(categories, function (index, cat) {
						$.each(cat, function (index, item) {
							var li;
							if (item.category != currentCategory) {
								ul.append("<li class='ui-autocomplete-category'>"+item.category+"</li>");
								currentCategory = item.category;
							}
							li = that._renderItemData(ul, item);
							if (item.category) {
								li.attr("aria-label", item.category+" : "+item.label);
							}
						});
					});
				},});
		this.wrapper = $( "<span>" )
			.addClass( "custom-combobox" )
			.insertAfter( this.element );
		this.element.hide();
		this._createLikeButton();
		this._createAutocomplete();
		this._createShowAllButton();
		this._getSource();
	},

	_createAutocomplete: function() {
		var selected = this.element.children( ":selected" ),
			value = selected.val() ? selected.text() : "";
		var me = this;
		this.input = $( "<input>" )
			.appendTo( this.wrapper )
			.val( value  )
			.attr( "title", "" )
			.attr("placeholder", Dict.S("TimeTracking:SelectActivity:Placeholder"))
			.addClass(this.element.attr("class"))
			.addClass( "custom-combobox-input custom-combobox-input  ui-widget ui-widget-content ui-state-default ui-corner-left" )
			.autocompleteActivityTimeTracking({
				delay: 0,
				minLength: 0,
				autoFocus: true,
				source: $.proxy(this, "_source"),
				select: function (event, ui) {
					$(".get_tt_activity_combobox").val(ui.item.label); // display the selected text
					me.curr_obj_id = ui.item.id;
					me.curr_obj_class = ui.item.object_class;
					me.curr_obj_scope = ui.item.scope_class;
					if(ui.item.favourite === true)
					{
						$("#time-tracking-like-button").removeClass(me.options.like_button_off).addClass('time-tracking-is-favorite').addClass(me.options.like_button_on);
					}
					else
					{
						$("#time-tracking-like-button").removeClass('time-tracking-is-favorite').removeClass(me.options.like_button_on).addClass(me.options.like_button_off);
					}
				},
			})
			.tooltip({
				tooltipClass: "ui-state-highlight"
			})
			.on("click", function () {
				//select all text on a single click
				$(this).select();
			});

		if(selected.attr('data-favourite') === "true")
		{
			$("#time-tracking-like-button").removeClass(this.options.like_button_off).addClass('time-tracking-is-favorite').addClass(this.options.like_button_on);
		}
		else
		{
			$("#time-tracking-like-button").removeClass('time-tracking-is-favorite').removeClass(this.options.like_button_on).addClass(this.options.like_button_off);
		}
		this._on( this.input, {
			autocompleteselect: function( event, ui ) {
				ui.item.option.selected = true;
				this._trigger( "select", event, {
					item: ui.item.option
				});
			},
			autocompletechange: "_removeIfInvalid"
		});
	},
	_createLikeButton: function() {
		var selected = this.element.children( ":selected" );
		var bIsLiked = (selected.attr('data-favourite') == 'true' ? 'fa-heart time-tracking-is-favorite' : this.options.like_button_off);
		var me = this;
		var input = this.input,
			wasOpen = false,
			endpoint = this.endpoint;
		$("<i id=\"time-tracking-like-button\">").on('click', {me:me, input:input}, function(e)
		{
			var aActivityValues =  me.GetValues();
			if(aActivityValues.object_id)
			{
				$.extend(
					aActivityValues,
					{
						operation: 'favourite_activity',
						value: !$('#time-tracking-like-button').hasClass('time-tracking-is-favorite'),
					}
				);
				$.ajax({
					type: "POST",
					dataType: 'json',
					url: me.options.endpoint,
					data: aActivityValues,
					success: function (oResult) {
						if (oResult.status === 'failed') {
							alert(oResult.message);
						}
						else if  (oResult.status === 'ok')
						{
							if(oResult.new_value === "true")
							{
								$("#time-tracking-like-button").removeClass(me.options.like_button_off).addClass('time-tracking-is-favorite').addClass(me.options.like_button_on);

							}
							else
							{
								$("#time-tracking-like-button").removeClass('time-tracking-is-favorite').removeClass(me.options.like_button_on).addClass(me.options.like_button_off);
							}
							//me.input.data('autocompleteActivityTimeTracking').selectedItem.favourite = oResult.new_value;
							me.activities = [];
							me._getSource();
						}
					}
				});
			}
		}).appendTo(this.wrapper);
	},
	_createShowAllButton: function() {
		var input = this.input,
			wasOpen = false;

		$( "<a>" )
			.attr( "tabIndex", -1 )
			//.attr( "title", "Show All Items" )
			.tooltip()
			.appendTo( this.wrapper )
			.button({
				icons: {
					// primary: "glyphicon glyphicon-triangle-bottom"
				},
				text: false
			})
			.removeClass( "ui-corner-all" )
			.addClass(  "custom-combobox-toggle " )
			.append('<i class="fa fa-chevron-down" aria-hidden="true"></i>')
			.mousedown(function() {
				wasOpen = input.autocompleteActivityTimeTracking( "widget" ).is( ":visible" );
			})
			.click(function() {
				input.focus();

				// Close if already visible
				if ( wasOpen ) {
					return;
				}

				// Pass empty string as value to search for, displaying all results
				input.autocompleteActivityTimeTracking( "search", "" );
			});
	},

	_source: function( request, response ) {
		var me = this;
		if(this.activities.length === 0 && false) {
			$.ajax({
				type: "GET",
				dataType: 'json',
				url: this.options.endpoint,
				data: {
					operation: 'get_activities',
				},
				success: function (oResult) {
					if (oResult.status === 'failed') {
						;
					} 
					else if (oResult.status === 'ok') {
						$.each(oResult.possible_activities, function(sPossibleActivitiesClass, aPossibleActivitiesByClass) {
							aPossibleActivitiesByClass.forEach(function(aPossibleActivity){
								var aPossibleActivityItem = { 
									'object_id': aPossibleActivity.object_id, 
									'object_class': aPossibleActivity.object_class, 
									'friendlyname': aPossibleActivity.friendlyname, 
									'color': aPossibleActivity.color, 
									'scope_class': aPossibleActivity.scope_class, 
									'favorite': aPossibleActivity.favorite == "true",
									'category': sPossibleActivitiesClass,
								};
								me.activities.push(aPossibleActivityItem);
							});
						});
						me._mapsource(request, response);
					}
				}
			});
		}
		else
		{
			this._mapsource(request, response);
		}

	},
	_mapsource: function(request, response) {
		var aResponse = [];
		var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
		this.activities.forEach(function(activity) {
			var text = activity['friendlyname'];
			var scope_class =   activity['scope_class'];
			var category =   activity['category'];
			var favourite =  activity['favorite'];
			var color = activity['color'];
			var obj_class = activity['object_class'];
			var obj_id = activity['object_id'];

			if (activity && (!request.term || matcher.test(text) || matcher.test(category)))
				aResponse.push({
					label: text,
					value: text,
					id: obj_id,
					option: activity,
					category: category,
					favourite: favourite,
					color: color,
					object_class: obj_class,
					scope_class: scope_class,
				});
		});
		response( aResponse  );
	},
	_removeIfInvalid: function( event, ui ) {
		return;
		// Selected an item, nothing to do
		if ( ui.item ) {
			return;
		}

		// Search for a match (case-insensitive)
		var value = this.input.val(),
			valueLowerCase = value.toLowerCase(),
			valid = false;
		this.element.children( "option" ).each(function() {
			if ( $( this ).text().toLowerCase() === valueLowerCase ) {
				this.selected = valid = true;
				return false;
			}
		});

		// Found a match, nothing to do
		if ( valid ) {
			return;
		}

		// Remove invalid value
		this.input
			.val( "" )
			.attr( "title", value + " n'a retourné aucun élément." )
			.tooltip( "open" );
		this.element.val( "" );
		this._delay(function() {
			this.input.tooltip( "close" ).attr( "title", "" );
		}, 2500 );
		this.input.autocomplete( "instance" ).term = "";
	},
	_getSource: function(){
		var me = this;
		$.ajax({
			type: "GET",
			dataType: 'json',
			url: this.options.endpoint,
			data: {
				operation: 'get_activities',
			},
			success: function (oResult) {
				if (oResult.status === 'failed') {
					;
				}
				else if (oResult.status === 'ok') {
					$.each(oResult.possible_activities, function(sPossibleActivitiesClass, aPossibleActivitiesByClass) {
						aPossibleActivitiesByClass.forEach(function(aPossibleActivity){
							var aPossibleActivityItem = {
								'object_id': aPossibleActivity.object_id,
								'object_class': aPossibleActivity.object_class,
								'friendlyname': aPossibleActivity.friendlyname,
								'color': aPossibleActivity.color,
								'scope_class': aPossibleActivity.scope_class,
								'favorite': aPossibleActivity.favorite == "true",
								'category': sPossibleActivitiesClass,
							};
							me.activities.push(aPossibleActivityItem);
						});
					});
					//me._mapsource(request, response);
				}
			}
		});
	},
	_destroy: function() {
		this.wrapper.remove();
		this.element.show();
	},
	
	GetValues: function(){
		return {object_id: this.curr_obj_id, object_class: this.curr_obj_class, object_scope: this.curr_obj_scope,}
	},
	
	Empty: function() {
		this.activities = [];
	},
	
	Refetch: function() {
		this.Empty();
		this._getSource();
	}
});
