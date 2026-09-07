// jQuery UI style "widget" for creating stacked barcharts using RaphaelJS
$(function()
{
	// the widget definition, where "itop" is the namespace,
	// "barchart" the widget name
	$.widget( "itop.barchart",
	{
		// default options
		options:
		{
			series: [],
			x_axis: { labels: [], attr: {font:"12px Arial", "font-weight": "regular", "fill": "#000000"} },
			y_axis: { min: 0, max: 'auto', step: 'auto', attr: {font:"12px Arial", "font-weight": "regular", "fill": "#000000"} },
			style: 'stacked',
			type: 'square', // 'square' | 'round' | 'sharp' | 'soft'
			direction: 'horizontal',
			width: 'auto',
			height: 'auto',
			padding: 30,
			vertical_gutter: 20,
			horizontal_gutter: 10,
			chart_title: { text: '', attr: {font:"14px Arial", "font-weight": "bold", "fill": "#000000"} },
			legend: { visible: true, size: 10, attr: {font:"12px Arial", "font-weight": "regular", "fill": "#000000"} } 
		},
	
		// the constructor
		_create: function()
		{
			this.element
			.addClass('itop-barchart');
			this._refresh();
		},
	
		// called when created, and later when changing options
		_refresh: function()
		{
			if (this.options.width == 'auto')
			{
				this.options.width = this.element.width();
			}
			if (this.options.height == 'auto')
			{
				this.options.height = this.element.height();
			}
			if (this.options.height == 0)
			{
				this.options.height = 0.66 * this.options.width;
				this.element.height(this.options.height);
			}
			if (this.paper != null)
			{
				// Rebuild the whole paper to support dynamic resizing
				this.paper.clear();
				this.paper.remove();
			}
			this.paper = Raphael(this.element[0], this.options.width, this.options.height);				

			this.legend_width = 0;
			this._computeColors();
			if (this.options.legend.visible)
			{
				this._drawLegend();
			}

			this._computeYAxis();
			this._drawYAxis();
			this._drawYGrid();
			this._drawBars();
			this._drawXAxis(); // Draw the XAxis on top of the chart for a cleaner display
			this._drawTitle();
		},
		// events bound via _bind are removed automatically
		// revert other modifications here
		_destroy: function()
		{
			this.element
			.removeClass('itop-barchart');			
		},
		// _setOptions is called with a hash of all options that are changing
		_setOptions: function()
		{
			this._superApply(arguments);
			this._refresh();
		},
		// _setOption is called for each individual option that is changing
		_setOption: function( key, value )
		{
			this._superApply(arguments);
		},
		_onMouseOver: function (item)
		{
			// No popup for empty items, for clarity
			if (item.bar.display_label)
			{
				item.flag = this.paper.popup(item.bar.x, item.bar.y, item.bar.display_label).insertBefore(item).toFront();				
			}
		},
		_onMouseOut: function (item)
		{
			if (typeof item.flag == 'object')
			{
				item.flag.animate({opacity: 0}, 300, function () { this.remove();});				
			}
		},
		_computeYAxis: function()
		{
			if ((this.options.y_axis.min == 'auto') || (this.options.y_axis.max == 'auto'))
			{
				var iMin = 999999;
				var iMax = -999999;
				if (this.options.style == 'stacked')
				{
					for(var i in this.options.series[0].data)
					{
						var value = 0;
						for(var j in this.options.series)
						{
							var val = this.options.series[j].data[i].value;
							value += val;
						}
						if (value > iMax) iMax = value;
						if (value < iMin) iMin = value;
					}
				}
				else
				{
					for(var i in this.options.series)
					{
						var value = 0;
						for(var j in this.options.series[i].data)
						{
							value = this.options.series[i].data[j].value;
							if (value > iMax) iMax = value;
							if (value < iMin) iMin = value;
						}
					}					
				}
				
				if (this.options.y_axis.min == 'auto')
				{
					this.options.y_axis.min = iMin;
				}
				if (this.options.y_axis.max == 'auto')
				{
					this.options.y_axis.max = iMax;
				}
			}
			
			if (this.options.y_axis.step == 'auto')
			{
				var aVal = [0, 1, 2, 5, 10, 20, 50, 100, 500, 1000, 5000, 10000];
				var idx = 0;
				var yDist = 0;
				do
				{
					idx++;
					yDist = (this.options.height - 2*this.options.vertical_gutter - 2*this.options.padding) / ((this.options.y_axis.max - this.options.y_axis.min) / aVal[idx]);	
				}
				while((yDist < 15) && (idx < aVal.length)); // minimum distance 15px between two labels on the axis...
				this.options.y_axis.step = aVal[idx];
			}
		},
		_drawYAxis: function()
		{
			var iYAxisHeight = this.options.height - 2*this.options.vertical_gutter - 2*this.options.padding;
			var aYAxisValues = [];
			for(var i=this.options.y_axis.min; i <= this.options.y_axis.max; i += this.options.y_axis.step)
			{
				aYAxisValues.push(i);
			}			
			
			var yAxis = this.paper.raphael.g.axis(this.options.padding, // x
												  this.options.height - this.options.padding - this.options.vertical_gutter, // y
												  iYAxisHeight, // length
												  0, // from
												  aYAxisValues.length - 1, // to
												  aYAxisValues.length - 1, // steps
												  1, // orientation
												  aYAxisValues, // labels
												  "T", // type
												  null, // dashsize
												  this.paper // paper
												  );
			yAxis.text.attr(this.options.y_axis.attr);			
		},
		_drawYGrid: function()
		{
			// Horizontal grid
			var iYAxisHeight = this.options.height - 2*this.options.vertical_gutter - 2*this.options.padding;
			var iXPos = this.options.padding;
			var iLength = this.options.width - 2*this.options.padding - this.legend_width;
			for(var i=this.options.y_axis.min; i <= this.options.y_axis.max; i += this.options.y_axis.step)
			{
			    var iYPos = this.options.height - this.options.vertical_gutter - this.options.padding -(i*iYAxisHeight)/this.options.y_axis.max;
			    this.paper.path('M'+iXPos+','+iYPos+'h'+iLength).attr({stroke: '#CCCCCC', 'stroke-width': 1});
			}			
		},
		_drawXAxis: function()
		{
			var iColWidth = (this.options.width - 2*this.options.padding - this.legend_width) / this.options.series[0].data.length;
			var g = this.options.horizontal_gutter * iColWidth / 100;
			var iX = this.options.padding + g/2 + iColWidth/2;
			var iAxisWidth = this.options.width - g - 2*this.options.padding - iColWidth - this.legend_width;
			
			var xAxis = this.paper.raphael.g.axis(iX, // x
												  this.options.height - this.options.padding - this.options.vertical_gutter, // y
												  iAxisWidth, // length
												  0, // from
												  this.options.x_axis.labels.length - 1, // to
												  this.options.x_axis.labels.length - 1, // steps
												  0, // orientation
												  this.options.x_axis.labels, // labels
												  "|", // type
												  null, // dashsize
												  this.paper // paper
												  );
			this.paper.path('M'+this.options.padding+','+(this.options.height - this.options.padding - this.options.vertical_gutter)+' h'+(iAxisWidth+iColWidth)).attr({stroke: '#000000', 'stroke-width': 1});
			xAxis.text.attr(this.options.x_axis.attr);	
		},
		_drawBars: function()
		{
			var aData = [];
			var aColors = [];
			
			for(var i in this.options.series)
			{
				aData.push([]);
				aColors.push(this.options.series[i].color);
				
				for(var j in this.options.series[i].data)
				{
					aData[i].push(this.options.series[i].data[j].value);
				}
			}
			
			var me = this;
			var oChart = this.paper.barchart(
					this.options.padding, // x
					this.options.padding, // y
					this.options.width - 2*this.options.padding - this.legend_width,
					this.options.height - 2*this.options.padding,
					aData,
					{
						stacked: (this.options.style == 'stacked'),
						stretch: true,
						type: this.options.type, 
						axis: "0 0 1 1", 
						gutter: this.options.horizontal_gutter, 
						vgutter: this.options.vertical_gutter,
						colors: aColors
					}).hover(function() { me._onMouseOver(this); }, function() { me._onMouseOut(this); });
			
			for(var i in this.options.series)
			{
				for(var j in this.options.series[i].data)
				{
					oChart.bars[i][j].display_label = this.options.series[i].data[j].label;
				}
			}
		},
		_drawTitle: function()
		{
			var oText = this.paper.text(0, 0, this.options.chart_title.text);
			oText.attr(this.options.chart_title.attr);
			var oBB = oText.getBBox();
			oText.translate(this.options.width / 2, oBB.height);
		},
		_getColorArray: function()
		{
			return ['#EF6352', '#FFBFF0', '#FFCA28', '#558B2F', '#3949AB', '#6329BC', '#9C2973', '#EC407A', '#EF948B', '#FF6FF0', '#FFEE58', '#CDDC39', '#33691E', '#26C6DA', '#1A237E', '#946CB5', '#C62994', '#EF5350', '#FFCC80', '#FFE082', '#9CCC65', '#9E9D24', '#0277BD', '#5C6BC0', '#B584DD', '#F6C8E9','#535353', '#757575', '#a6a6a6', '#c2c2c2', '#546E7A', '#78909C', '#455A64', '#37474F', '#263238'];
		},
		_computeColors: function()
		{
			var colorArray = this._getColorArray();
			for(var i in this.options.series)
			{
				if (!this.options.series[i].color)
				{
					this.options.series[i].color = colorArray[i % colorArray.length];
				}
			}
		},
		_drawLegend: function()
		{
			var oSet = this.paper.set();
			var iMaxWidth = 0;
			
			for(var i in this.options.series)
			{
				oSet.push(this.paper.rect(0, 2*i*this.options.legend.size, this.options.legend.size, this.options.legend.size).attr({fill: this.options.series[i].color, 'stroke-width': 0}));
				if (this.options.series[i].label && (this.options.series[i].label != ''))
				{
					var oText = this.paper.text(this.options.legend.size*2, (2*i*this.options.legend.size)+this.options.legend.size/2, this.options.series[i].label);
					if (this.options.legend.attr)
					{
						oText.attr(this.options.legend.attr);
					}
					var oBBox = oText.getBBox();
					oText.translate(oBBox.width/2);
					if (iMaxWidth < oBBox.width)
					{
						iMaxWidth = oBBox.width;
					}
					oSet.push(oText);					
				}
			}
			oSet.translate(this.options.width - this.options.padding - iMaxWidth, this.options.height / 2 - (2*this.options.series.length*this.options.legend.size)/2);
			this.legend_width = iMaxWidth + 3*this.options.legend.size;
		}
	});	
});