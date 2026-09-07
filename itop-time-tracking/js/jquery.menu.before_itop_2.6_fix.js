$.widget( 'timetracking.menu', $.ui.menu,
{
	_create: function() {
		this._super();
		return this.element;
	},
});
